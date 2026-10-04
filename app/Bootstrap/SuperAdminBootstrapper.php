<?php

namespace App\Bootstrap;

use App\Models\PermissionModel;
use App\Models\User;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SuperAdminBootstrapper
{
    public function run(): void
    {
        /** @var IdGeneratorInterface $idGenerator */
        $idGenerator = app(IdGeneratorInterface::class);

        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'id'        => $idGenerator->generate(),
                'name'      => 'Super Admin',
                'full_name' => 'Administrator System',
                'password'  => Hash::make('password123'),
            ]
        );

        if ($admin->wasRecentlyCreated) {
            Log::info(
                '[SuperAdminBootstrapper] Super Admin user created.',
                ['id' => $admin->id]
            );
        }

        $allPermissions = PermissionModel::all();

        $syncData = [];

        foreach ($allPermissions as $permission) {
            $syncData[$permission->id] = [
                'id'      => $idGenerator->generate(),
                'version' => 1,
            ];
        }

        $admin->permissions = $allPermissions
            ->pluck('slug')
            ->values()
            ->all();

        $admin->permissionRelations()->syncWithoutDetaching($syncData);

        $admin->save();

        Log::info(
            '[SuperAdminBootstrapper] Super Admin permissions synchronized successfully.',
            [
                'total_permissions' => $allPermissions->count(),
            ]
        );
    }
}