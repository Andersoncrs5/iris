<?php

namespace App\Services\PermissionUser;

use App\Models\PermissionUserModel;

class ShowPermissionUserUseCase
{
    public function execute(int $id): ?PermissionUserModel
    {
        $permissionUser = PermissionUserModel::query()
            ->with(['user', 'permission'])
            ->find($id);

        return $permissionUser;
    }
}