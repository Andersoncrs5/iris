<?php

namespace App\Bootstrap;

use App\Models\PermissionModel;
use App\Models\PermissionUserModel;
use App\Models\User;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SuperAdminBootstrapper
{
    public function run(): void
    {
        /** @var IdGeneratorInterface $idGenerator */
        $idGenerator = app(IdGeneratorInterface::class);

        $email    = config('iris.super_admin.email');
        $password = config('iris.super_admin.password');

        if (blank($password)) {
            if (app()->isProduction()) {
                throw new RuntimeException(
                    'SUPER_ADMIN_PASSWORD must be set when APP_ENV=production.'
                );
            }

            // Conveniência apenas fora de produção.
            $password = 'password123';
        }

        // O closure só é avaliado quando o usuário ainda não existe
        // (evita gerar hash bcrypt e ID snowflake à toa a cada execução).
        $admin = User::firstOrCreate(
            ['email' => $email],
            fn () => [
                'id'        => $idGenerator->generate(),
                'name'      => config('iris.super_admin.name'),
                'full_name' => config('iris.super_admin.full_name'),
                'password'  => Hash::make($password),
            ]
        );

        if ($admin->wasRecentlyCreated) {
            Log::info(
                '[SuperAdminBootstrapper] Super Admin user created.',
                ['id' => $admin->id]
            );
        }

        $allPermissions = PermissionModel::all();

        /*
         * Include soft-deleted assignments so that their IDs are not
         * regenerated and their unique user-permission pairs are not
         * attempted again.
         */
        $existingPermissionIds = PermissionUserModel::withTrashed()
            ->where('user_id', $admin->getKey())
            ->pluck('permission_id')
            ->map(static fn ($id): string => (string) $id)
            ->all();

        $existingPermissionIds = array_fill_keys(
            $existingPermissionIds,
            true
        );

        $syncData = [];

        foreach ($allPermissions as $permission) {
            $permissionId = (string) $permission->getKey();

            // Never rewrite an existing assignment's primary key.
            if (isset($existingPermissionIds[$permissionId])) {
                continue;
            }

            // Generate an ID only for a new assignment.
            $syncData[$permission->getKey()] = [
                'id'      => $idGenerator->generate(),
                'version' => 1,
            ];
        }

        $admin->permissions = $allPermissions
            ->pluck('slug')
            ->values()
            ->all();

        // Attach only permissions that do not already have a pivot row.
        if ($syncData !== []) {
            $admin->permissionRelations()
                ->syncWithoutDetaching($syncData);
        }

        $admin->save();

        Log::info(
            '[SuperAdminBootstrapper] Super Admin permissions synchronized successfully.',
            [
                'total_permissions' => $allPermissions->count(),
                'new_assignments'   => count($syncData),
            ]
        );
    }
}
