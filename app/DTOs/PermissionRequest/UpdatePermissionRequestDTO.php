<?php

namespace App\DTOs\PermissionRequest;

final class UpdatePermissionRequestDTO
{
    public function __construct(
        public readonly int $permissionId,
        public readonly string $reason,
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            permissionId: (int) $data['permission_id'],
            reason: $data['reason'],
        );
    }

    public function toArray(): array
    {
        return [
            'permission_id' => $this->permissionId,
            'reason' => $this->reason,
        ];
    }
}