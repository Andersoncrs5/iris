<?php

namespace App\Http\Controllers;

use App\DTOs\User\FilterUserDTO;
use App\DTOs\User\UpdatePasswordDTO;
use App\DTOs\User\UpdateUserDTO;
use App\Http\Requests\User\UserUpdatePasswordRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Models\User;
use App\Services\User\ListUserUseCase;
use App\Services\User\UpdatePasswordUseCase;
use App\Services\User\UpdateUserUseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Utils\Enums\User\UserStatusEnum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct(
        private readonly UpdateUserUseCase $updateUserUseCase,
        private readonly UpdatePasswordUseCase $updatePasswordUseCase,
        private readonly ListUserUseCase $listUserUseCase,
    ) {}

    public function list(Request $request) 
    {
        Gate::authorize('user.read');

        $filterDto = FilterUserDTO::fromRequest($request);

        $users = $this->listUserUseCase->execute($filterDto);

        return view('user.list', [
            'users' => $users,
        ]);
    }

    public function editPassword()
    {
        $user = auth()->user();
        return view("user.edit-password", compact("user"));
    }

    public function editPasswordForm(UserUpdatePasswordRequest $request)
    {
        Gate::authorize('user.update');

        $dto = UpdatePasswordDTO::fromRequest($request);
        $result = $this->updatePasswordUseCase->execute($dto, auth()->id());

        if ($result->isFailure()) {
            return redirect()->back()->with('error', $result->getError());
        }

        return redirect()
            ->route("user.profile")
            ->with("success", "Password updated successfully!");
    }

    public function profile()
    {
        $user = auth()->user();

        return view("user.profile", compact('user'));
    }

    public function edit()
    {
        Gate::authorize('user.update');
        $user = auth()->user();

        return view("user.edit", compact('user'));
    }

    public function editForm(UserUpdateRequest $request)
    {
        Gate::authorize('user.update');

        $userId = auth()->id();
        $dto = UpdateUserDTO::fromRequest($request);

        $result = $this->updateUserUseCase->execute($dto, $userId);

        if ($result->isFailure()) {
            return redirect()->back()->with('error', $result->getError());
        }

        return redirect()
            ->route("user.profile")
            ->with("success", "User updated with success!");
    }

    public function delete()
    {
        Gate::authorize('user.delete');

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $user->delete();

        return redirect()->route('login')->with('success', 'Account deleted successfully.');
    }

    public function show(int $id)
    {
        /** @var \App\Models\User $user */
        $user = User::find($id);

        if (!$user) {
            return redirect()   
                ->back()
                ->with("error", "User not found");
        }
        
        return view("user.show-details", compact('user'));
    }

    public function toggleActive(int $id,string $status,): RedirectResponse {
        $actor = auth()->user();

        abort_unless(
            $actor !== null
            && (
                $actor->can('super.adm')
                || $actor->can('simple.adm')
            ),
            403,
        );

        $targetStatus = UserStatusEnum::tryFrom(
            strtoupper($status),
        );

        if ($targetStatus === null) {
            abort(404);
        }

        if ((int) $actor->getAuthIdentifier() === $id) {
            return back()->with(
                'error',
                'You cannot change your own account status.',
            );
        }

        $updated = DB::transaction(
            function () use ($id, $targetStatus): ?bool {
                $user = User::query()
                    ->lockForUpdate()
                    ->find($id);

                if ($user === null) {
                    return null;
                }

                $currentStatus = $user->status instanceof UserStatusEnum
                    ? $user->status
                    : UserStatusEnum::tryFrom(
                        strtoupper((string) $user->status),
                    );

                if ($currentStatus === $targetStatus) {
                    return false;
                }

                $user->status = $targetStatus->value;
                $user->version = (int) ($user->version ?? 0) + 1;
                $user->save();

                return true;
            },
        );

        if ($updated === null) {
            return back()->with('error', 'User not found.');
        }

        if ($updated === false) {
            return back()->with(
                'info',
                'The user already has this status.',
            );
        }

        return back()->with(
            'success',
            'User status changed to ' . $targetStatus->label() . '.',
        );
    }

}