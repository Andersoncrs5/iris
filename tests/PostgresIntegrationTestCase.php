<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;
use Testcontainers\Container\StartedGenericContainer;
use Testcontainers\Modules\PostgresContainer;
use Testcontainers\Modules\RedisContainer;

abstract class PostgresIntegrationTestCase extends TestCase
{
    protected static ?StartedGenericContainer $postgres = null;
    protected static ?StartedGenericContainer $redis = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if (self::$postgres === null) {
            self::$postgres = (new PostgresContainer('16-alpine'))
                ->withPostgresDatabase('iris_test')
                ->withPostgresUser('postgres')
                ->withPostgresPassword('secret')
                ->start();
        }

        if (self::$redis === null) {
            self::$redis = (new RedisContainer('8-alpine'))
                ->start();
        }

        config([
            'database.default' => 'pgsql',

            'database.connections.pgsql.host' =>
                self::$postgres->getHost(),

            'database.connections.pgsql.port' =>
                self::$postgres->getFirstMappedPort(),

            'database.connections.pgsql.database' =>
                'iris_test',

            'database.connections.pgsql.username' =>
                'postgres',

            'database.connections.pgsql.password' =>
                'secret',

            'cache.default' => 'redis',

            'redis.default.host' =>
                self::$redis->getHost(),

            'redis.default.port' =>
                self::$redis->getFirstMappedPort(),
        ]);

        Artisan::call('migrate:fresh');
    }

    public static function tearDownAfterClass(): void
    {
        self::$redis?->stop();
        self::$postgres?->stop();

        self::$redis = null;
        self::$postgres = null;

        parent::tearDownAfterClass();
    }
}