<?php

namespace App\DTOs\PermissionRequest;

use App\Utils\Enums\PermissionRequestStatus\PermissionRequestStatus;

final class ChangePermissionRequestStatusDTO
{
    public function __construct(
        public readonly PermissionRequestStatus $status,
        public readonly ?string $reviewComment,
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            status: PermissionRequestStatus::from($data['status']),
            reviewComment: $data['review_comment'] ?? null,
        );
    }
}