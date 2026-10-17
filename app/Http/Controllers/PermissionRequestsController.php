<?php

namespace App\Http\Controllers;

use App\DTOs\PermissionRequest\ChangePermissionRequestStatusDTO;
use App\DTOs\PermissionRequest\CreatePermissionRequestDTO;
use App\DTOs\PermissionRequest\FilterPermissionRequestDTO;
use App\DTOs\PermissionRequest\UpdatePermissionRequestDTO;
use App\Http\Requests\PermissionRequest\CreatePermissionRequestRequest;
use App\Models\PermissionModel;
use App\Models\PermissionRequestModel;
use App\Services\PermissionRequest\ChangePermissionRequestStatusUseCase;
use App\Services\PermissionRequest\CreatePermissionRequestUseCase;
use App\Services\PermissionRequest\UpdatePermissionRequestUseCase;
use App\Services\PermissionRequest\DeletePermissionRequestUseCase;
use App\Services\PermissionRequest\ListPermissionRequestUseCase;
use App\Services\PermissionRequest\MarkPermissionRequestViewedUseCase;
use App\Utils\Enums\PermissionRequestStatus\PermissionRequestStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PermissionRequestsController extends Controller
{
    public function __construct(
        private readonly CreatePermissionRequestUseCase $createPermissionRequestUseCase,
        private readonly UpdatePermissionRequestUseCase $updatePermissionRequestUseCase,
        private readonly DeletePermissionRequestUseCase $deletePermissionRequestUseCase,
        private readonly ChangePermissionRequestStatusUseCase $changePermissionRequestStatusUseCase,
        private readonly MarkPermissionRequestViewedUseCase $markPermissionRequestViewedUseCase,
        private readonly ListPermissionRequestUseCase $listPermissionRequestUseCase,
    ) {}

    public function list(Request $request)
    {
        Gate::authorize('permission.request.list');

        $filterDto = FilterPermissionRequestDTO::fromRequest($request);

        $permissionRequests = $this->listPermissionRequestUseCase
            ->execute($filterDto);

        $availablePermissions = PermissionModel::query()
            ->orderBy('name')
            ->get();

        return view('permission-request.list', [
            'permissionRequests' => $permissionRequests,
            'availablePermissions' => $availablePermissions,
            'availableStatuses' => PermissionRequestStatus::values(),
        ]);
    }

    public function create(): View
    {
        $permissions = PermissionModel::paginate();

        return view('permission-request.create', compact('permissions'));
    }

    public function createForm(CreatePermissionRequestRequest $request ): RedirectResponse {
        $dto = CreatePermissionRequestDTO::fromValidated(
            $request->validated()
        );

        $result = $this->createPermissionRequestUseCase->execute($dto);

        if ($result->isFailure()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('permission.request.my')
            ->with('success', 'Permission request submitted successfully.');
    }

    public function myRequest(): View
    {
        $permissionRequests = PermissionRequestModel::query()
            ->with('permission')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view(
            'permission-request.my-request',
            compact('permissionRequests')
        );
    }

    /**
     * Show permission request details.
     */
    public function show(int $id): View
    {
        $permissionRequest = PermissionRequestModel::query()
            ->with('permission')
            ->findOrFail($id);

        $isOwner = (int) $permissionRequest->user_id
            === (int) auth()->id();

        abort_unless(
            $isOwner || auth()->user()->can('permission.request.list'),
            403
        );

        return view(
            'permission-request.show',
            compact('permissionRequest')
        );
    }

    /**
     * Display the edit form for an owned pending request.
     */
    public function edit(int $id): View|RedirectResponse
    {
        $permissionRequest = PermissionRequestModel::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        if (
            $permissionRequest->getRawOriginal('status')
            !== PermissionRequestStatus::PENDING->value
        ) {
            return redirect()
                ->route('permission.request.my')
                ->with('error', 'Only pending requests can be updated.');
        }

        $permissions = PermissionModel::query()
            ->orderBy('name')
            ->get();

        return view(
            'permission-request.edit',
            compact('permissionRequest', 'permissions')
        );
    }

    /**
     * Update an owned pending request.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'permission_id' => [
                'required',
                'integer',
                'min:1',
                Rule::exists('permissions', 'id'),
            ],
            'reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $dto = UpdatePermissionRequestDTO::fromValidated($data);

        $result = $this->updatePermissionRequestUseCase->execute(
            dto: $dto,
            id: $id,
            userId: (int) auth()->id(),
        );

        if ($result->isFailure()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('permission.request.my')
            ->with('success', 'Permission request updated successfully.');
    }

    /**
     * Delete an owned pending request.
     */
    public function delete(int $id): RedirectResponse
    {
        $result = $this->deletePermissionRequestUseCase->execute(
            id: $id,
            userId: (int) auth()->id(),
        );

        if ($result->isFailure()) {
            return redirect()
                ->route('permission.request.my')
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('permission.request.my')
            ->with('success', 'Permission request deleted successfully.');

    }

    /**
     * Mark an owned request as viewed.
     */
    public function markViewed(int $id): RedirectResponse
    {
        Gate::authorize('permission.request.mark-viewed');

        $result = $this->markPermissionRequestViewedUseCase->execute(
            id: $id,
            userId: (int) auth()->id(),
        );

        if ($result->isFailure()) {
            return redirect()
                ->route('permission.request.my')
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('permission.request.my')
            ->with(
                'success',
                'Permission request marked as viewed or was already viewed.'
            );
    }

    /**
     * Display the status change form.
     */
    public function changeStatus(int $id): View
    {
        Gate::authorize('permission.request.status.update');

        $permissionRequest = PermissionRequestModel::query()
            ->with('permission')
            ->findOrFail($id);

        return view(
            'permission-request.change-status',
            compact('permissionRequest')
        );
    }

    /**
     * Save the status change.
     */
    public function changeStatusForm(Request $request,int $id,): RedirectResponse {
        Gate::authorize('permission.request.status.update');

        $data = $request->validate([
            'status' => [
                'required',
                'string',
                Rule::in(PermissionRequestStatus::values()),
            ],
            'review_comment' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $dto = ChangePermissionRequestStatusDTO::fromValidated($data);

        $result = $this->changePermissionRequestStatusUseCase->execute(
            dto: $dto,
            id: $id,
            reviewerId: (int) auth()->id(),
        );

        if ($result->isFailure()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('permission.request.my')
            ->with(
                'success',
                'Permission request status updated successfully.'
            );
    }
}