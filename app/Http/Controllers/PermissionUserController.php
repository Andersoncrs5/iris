<?php

namespace App\Http\Controllers;

use App\DTOs\PermissionUser\CreatePermissionUserDTO;
use App\DTOs\PermissionUser\FilterPermissionUserDTO;
use App\Http\Requests\PermissionUser\CreatePermissionUserRequest;
use App\Models\PermissionModel;
use App\Models\PermissionUserModel;
use App\Models\User;
use App\Services\PermissionUser\CreatePermissionUserUseCase;
use App\Services\PermissionUser\DeletePermissionUserUseCase;
use App\Services\PermissionUser\ListPermissionUserUseCase;
use App\Services\PermissionUser\RestorePermissionUserUseCase;
use App\Services\PermissionUser\ShowPermissionUserUseCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PermissionUserController extends Controller
{
    public function __construct(
        private readonly ListPermissionUserUseCase $listPermissionUserUseCase,
        private readonly ShowPermissionUserUseCase $showPermissionUserUseCase,
        private readonly DeletePermissionUserUseCase $deletePermissionUserUseCase,
        private readonly RestorePermissionUserUseCase $restorePermissionUserUseCase,
        private readonly CreatePermissionUserUseCase $createPermissionUserUseCase,
    ) {}

    public function list(Request $request): View
    {
        Gate::authorize('permission.user.list');

        $filters = FilterPermissionUserDTO::fromRequest($request);

        $permissionUsers = $this->listPermissionUserUseCase->execute(
            $filters
        );

        return view('permission-user.list', [
            'permissionUsers' => $permissionUsers,
            'filters' => $filters,
        ]);
    }

    public function viewDetails(int $id) : View | RedirectResponse
    {
        Gate::authorize('permission.user.view');

        $permissionUser = $this->showPermissionUserUseCase->execute($id);

        if ($permissionUser == null) {
            return redirect()
                ->back()
                ->with("error", "Permission user not found");
        }

        return view('permission-user.show', [
            'permissionUser' => $permissionUser,
        ]);
    }

    public function delete(int $id): RedirectResponse
    {
        Gate::authorize('permission.user.delete');

        $result = $this->deletePermissionUserUseCase->execute($id);

        if ($result->isFailure()) {
            return redirect()
                ->route('permission.user.list')
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('permission.user.list')
            ->with('success', 'Permission assignment deleted successfully.');
    }

    public function restore(int $id): RedirectResponse
    {
        Gate::authorize('permission.user.restore');

        $result = $this->restorePermissionUserUseCase->execute($id);

        if ($result->isFailure()) {
            return redirect()
                ->route('permission.user.list', ['trashed' => 'with'])
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('permission.user.list')
            ->with('success', 'Permission assignment restored successfully.');
    }

    public function create()
    {
        $users = User::query()
            ->select(['id', 'name', 'full_name', 'email'])
            ->orderBy('name')
            ->get();

        $permissions = PermissionModel::query()
            ->select(['id', 'name', 'slug', 'module'])
            ->orderBy('module')
            ->orderBy('name')
            ->get();

        return view('permission-user.create', [
            'users' => $users,
            'permissions' => $permissions,
        ]);
    }

    public function createForm(
        CreatePermissionUserRequest $request
    ): RedirectResponse {
        $dto = CreatePermissionUserDTO::fromValidated(
            $request->validated()
        );

        $result = $this->createPermissionUserUseCase->execute($dto);

        if ($result->isFailure()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('permission.user.list')
            ->with('success', 'Permission assigned successfully.');
    }

}