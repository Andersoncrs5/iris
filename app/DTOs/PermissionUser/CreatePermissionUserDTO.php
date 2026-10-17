<?php

namespace App\DTOs\PermissionUser;

readonly class CreatePermissionUserDTO
{
    public function __construct(
        public int $userId,
        public int $permissionId,
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            permissionId: (int) $data['permission_id'],
        );
    }
}