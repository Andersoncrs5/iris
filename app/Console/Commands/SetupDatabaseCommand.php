<?php

namespace App\Console\Commands;

use App\Bootstrap\PermissionBootstrapper;
use App\Bootstrap\SuperAdminBootstrapper;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

class SetupDatabaseCommand extends Command
{
    protected $signature = 'iris:setup
        {--skip-bootstrap : Apenas cria o banco e roda as migrations}';

    protected $description = 'Cria o banco (se não existir), roda as migrations e sincroniza permissões/super admin';

    public function handle(): int
    {
        $maxAttempts = max(1, (int) config('iris.setup.max_attempts', 10));
        $delay       = max(0, (int) config('iris.setup.retry_delay', 4));

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $this->info("[iris:setup] Tentativa {$attempt}/{$maxAttempts}...");

                // Descarta qualquer conexão em estado ruim de uma tentativa anterior.
                DB::purge();

                $this->ensureDatabaseExists();

                // A conexão antiga apontava para um banco que não existia.
                DB::purge();

                $exitCode = $this->call('migrate', ['--force' => true]);

                if ($exitCode !== self::SUCCESS) {
                    throw new RuntimeException("migrate terminou com código {$exitCode}.");
                }

                if (! $this->option('skip-bootstrap')) {
                    (new PermissionBootstrapper())->run();
                    (new SuperAdminBootstrapper())->run();
                }

                $this->info('[iris:setup] Concluído com sucesso.');

                return self::SUCCESS;
            } catch (Throwable $e) {
                $this->warn("[iris:setup] Falhou: {$e->getMessage()}");

                if ($attempt === $maxAttempts) {
                    $this->error("[iris:setup] Desistindo após {$maxAttempts} tentativas.");

                    return self::FAILURE;
                }

                $this->line("[iris:setup] Nova tentativa em {$delay}s.");
                sleep($delay);
            }
        }

        return self::FAILURE;
    }

    private function ensureDatabaseExists(): void
    {
        $name   = config('database.default');
        $config = config("database.connections.{$name}");
        $driver = $config['driver'] ?? null;

        match ($driver) {
            'pgsql'  => $this->ensurePostgresDatabase($config),
            'sqlite' => $this->ensureSqliteDatabase($config),
            default  => $this->warn(
                "[iris:setup] Driver [{$driver}] sem criação automática de banco; pulando."
            ),
        };
    }

    private function ensurePostgresDatabase(array $config): void
    {
        $database = $config['database'];

        // Conecta no banco de manutenção "postgres", que sempre existe.
        $pdo = new PDO(
            sprintf(
                'pgsql:host=%s;port=%s;dbname=postgres',
                Arr::first((array) $config['host']),
                $config['port']
            ),
            $config['username'],
            $config['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $stmt = $pdo->prepare('SELECT 1 FROM pg_database WHERE datname = :name');
        $stmt->execute(['name' => $database]);

        if ($stmt->fetchColumn()) {
            $this->line("[iris:setup] Banco [{$database}] já existe.");

            return;
        }

        try {
            $pdo->exec('CREATE DATABASE "' . str_replace('"', '""', $database) . '"');
            $this->info("[iris:setup] Banco [{$database}] criado.");
        } catch (PDOException $e) {
            // 42P04 = duplicate_database: outro processo criou entre o SELECT e o CREATE.
            if ($e->getCode() !== '42P04') {
                throw $e;
            }
        }
    }

    private function ensureSqliteDatabase(array $config): void
    {
        $path = $config['database'];

        if ($path === ':memory:' || is_file($path)) {
            return;
        }

        if (! touch($path)) {
            throw new RuntimeException("Não foi possível criar o arquivo SQLite em [{$path}].");
        }

        $this->info("[iris:setup] Arquivo SQLite [{$path}] criado.");
    }
}