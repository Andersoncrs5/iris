<?php 

namespace App\Http\Controllers;

use App\DTOs\Permission\CreatePermissionDTO;
use App\DTOs\Permission\FilterPermissionDTO;
use App\DTOs\Permission\UpdatePermissionDTO;
use App\Http\Requests\Permission\CreatePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;
use App\Models\PermissionModel;
use App\Services\Pemission\CreatePermissionUseCase;
use App\Services\Pemission\DeletePermissionUseCase;
use App\Services\Pemission\ListPermissionUseCase;
use App\Services\Pemission\UpdatePermissionUseCase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct(
        private readonly CreatePermissionUseCase $createPermissionUseCase,
        private readonly ListPermissionUseCase $listPermissionUseCase,
        private readonly UpdatePermissionUseCase $updatePermissionUseCase,
        private readonly DeletePermissionUseCase $deletePermissionUseCase
    ) {}

    public function list(Request $request)
    {
        Gate::authorize('permission.read');

        $filterDto = FilterPermissionDTO::fromRequest($request);

        $permissions = $this->listPermissionUseCase->execute($filterDto);

        return view('permission.list', [
            'permissions' => $permissions,
        ]);
    }

    public function create() 
    {
        Gate::authorize('permission.create');
        
        return view('permission.create');
    }

    public function createForm(CreatePermissionRequest $request) 
    {
        Gate::authorize('permission.create');

        $dto = CreatePermissionDTO::fromRequest($request);
        
        $result = $this->createPermissionUseCase->execute($dto);

        if ($result->isFailure()) {
            return redirect()->back()->with('error', $result->getError())->withInput();
        }

        return redirect()
            ->route('permission.list')
            ->with('success', 'Permission created successfully!');
    }

    public function showDetails(int $id) 
    {
        $permission = PermissionModel::find($id);

        if ($permission == null) {
            return redirect()
                ->route('permission.list')
                ->with('error', "Permission not found");
        }

        return view("permission.view-details", compact("permission"));
    }

    public function delete(int $id) 
    {
        Gate::authorize('permission.delete');

        $result = $this->deletePermissionUseCase->execute($id);

        if ($result->isFailure()) {
            return redirect()
                ->route('permission.list')
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('permission.list')
            ->with('success', 'Permission deleted with success!');
    }

    public function edit(int $id) 
    {
        $permission = PermissionModel::find($id);

        if ($permission == null) {
            return redirect()
                ->route('permission.list')
                ->with('error', "Permission not found");
        }

        return view("permission.update", compact("permission"));
    }

    public function editForm(UpdatePermissionRequest $request, int $id) 
    {
        
        $dto = UpdatePermissionDTO::fromRequest($request);

        $result = $this->updatePermissionUseCase->execute($dto, $id);

        if ($result->isFailure()) {
            return redirect()
                ->back()
                ->with('error', $result->getError())
                ->withInput();
        }

        return redirect()
            ->route('permission.list')
            ->with('success', 'Permission updated successfully!');
    }

    
}