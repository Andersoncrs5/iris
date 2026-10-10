<?php

namespace App\Http\Controllers;

use App\DTOs\User\UpdatePasswordDTO;
use App\DTOs\User\UpdateUserDTO;
use App\Http\Requests\User\UserUpdatePasswordRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Services\User\UpdatePasswordUseCase;
use App\Services\User\UpdateUserUseCase;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function __construct(
        private readonly UpdateUserUseCase $updateUserUseCase,
        private readonly UpdatePasswordUseCase $updatePasswordUseCase
    ) {}

    public function list() 
    {
        
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
}