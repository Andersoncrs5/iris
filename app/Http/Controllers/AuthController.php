<?php

namespace App\Http\Controllers;

use App\DTOs\User\UserRegisterDTO;
use App\Http\Requests\User\UserLoginRequest;
use App\Http\Requests\User\UserRegisterRequest;
use App\Services\User\CreateUserUseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        private readonly CreateUserUseCase $createUserUseCase
    ) {}

    public function Login() {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view("auth.login");
    }

    public function LoginHandle(UserLoginRequest $request) {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function Register() {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view("auth.register");
    }

    public function RegisterHandle(UserRegisterRequest $request) {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        $dto = UserRegisterDTO::fromRequest($request);

        $user = $this->createUserUseCase->Execute($dto);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function Logout(Request $request) {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}