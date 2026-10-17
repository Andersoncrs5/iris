
<?php

namespace App\Services\Pemission;

use App\DTOs\Permission\CreatePermissionDTO;
use App\Models\PermissionModel;
use App\Models\PermissionUserModel;
use App\Models\User;
use App\Utils\Result\Result;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Support\Facades\DB;

class CreatePermissionUseCase
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator
    ) {}

    public function execute(CreatePermissionDTO $dto): Result
    {
        try {
            return DB::transaction(function () use ($dto): Result {
                /*
                 * Find all Super Admins using the same criteria
                 * as User::isSuperAdmin().
                 */
                $superAdmins = User::query()
                    ->where(function ($query) {
                        $query
                            ->whereIn('status', ['super_admin', 'super_adm'])
                            ->orWhereJsonContains('permissions', 'super.adm')
                            ->orWhereJsonContains('permissions', '*')
                            ->orWhereHas(
                                'permissionRelations',
                                fn ($permissionQuery) => $permissionQuery
                                    ->whereIn('permissions.slug', [
                                        'super.adm',
                                        '*',
                                    ])
                            );
                    })
                    ->lockForUpdate()
                    ->get();

                if ($superAdmins->isEmpty()) {
                    return Result::fail(
                        'Failed to create permission: no Super Admin account was found.'
                    );
                }

                /*
                 * Create the permission.
                 */
                $permission = PermissionModel::query()->create([
                    'id' => $this->idGenerator->generate(),
                    'name' => $dto->name,
                    'slug' => $dto->slug,
                    'module' => $dto->module,
                    'description' => $dto->description,
                ]);

                /*
                 * Assign the new permission to every Super Admin.
                 */
                foreach ($superAdmins as $superAdmin) {
                    $this->assignPermissionToUser(
                        $permission,
                        $superAdmin
                    );
                }

                return Result::ok($permission);
            });
        } catch (\Throwable $th) {
            report($th);

            return Result::fail(
                'Failed to create permission: ' . $th->getMessage()
            );
        }
    }

    private function assignPermissionToUser(
        PermissionModel $permission,
        User $user
    ): void {
        /*
         * Check for an existing assignment, including soft-deleted rows.
         */
        $assignment = PermissionUserModel::withTrashed()
            ->where('user_id', $user->getKey())
            ->where('permission_id', $permission->getKey())
            ->first();

        if ($assignment === null) {
            PermissionUserModel::query()->create([
                'id' => $this->idGenerator->generate(),
                'user_id' => $user->getKey(),
                'permission_id' => $permission->getKey(),
                'version' => 1,
            ]);
        } elseif ($assignment->trashed()) {
            $assignment->restore();
        }

        /*
         * Synchronize the permissions JSON array on the user.
         * User::addPermission() already saves the model.
         */
        $user->addPermission($permission->slug);
    }
}
