@extends('layouts.base')

@section('title', 'Sign In - Iris App')

@section('content')
<div class="flex min-h-screen items-center justify-center px-4 py-12">
    <div class="w-full max-w-md rounded-2xl border border-white/20 bg-black/40 p-8 shadow-2xl backdrop-blur-md">
        
        <!-- Header -->
        <div class="mb-6 text-center">
            <h1 class="text-3xl font-bold tracking-tight text-white">Sign In</h1>
            <p class="mt-2 text-sm text-gray-300">Enter your credentials to continue</p>
        </div>

        <!-- Login Form -->
        <form action="{{ route('loginHandle') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-200 mb-1">Email address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                        <i class="fa-solid fa-envelope"></i>
                    </span>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        value="{{ old('email') }}"
                        required 
                        autofocus
                        placeholder="name@example.com"
                        class="w-full rounded-lg border border-white/20 bg-white/10 py-2.5 pl-10 pr-4 text-white placeholder-gray-400 backdrop-blur-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                    />
                </div>
                @error('email')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-200 mb-1">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        required 
                        placeholder="••••••••"
                        class="w-full rounded-lg border border-white/20 bg-white/10 py-2.5 pl-10 pr-4 text-white placeholder-gray-400 backdrop-blur-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                    />
                </div>
                @error('password')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <button 
                type="submit" 
                class="w-full rounded-lg bg-indigo-600 py-3 font-semibold text-white shadow-lg shadow-indigo-600/40 transition hover:bg-indigo-500 active:scale-[0.98]"
            >
                Sign In
            </button>
        </form>
    </div>
</div>
@endsection