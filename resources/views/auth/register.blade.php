@extends('layouts.base') 

@section('title', 'Sign Up - Iris App')

@section('content')
<div class="flex min-h-screen items-center justify-center px-4 py-12">
    <div class="w-full max-w-md rounded-2xl border border-white/20 bg-black/40 p-8 shadow-2xl backdrop-blur-md">
        
        <!-- Header -->
        <div class="mb-6 text-center">
            <h1 class="text-3xl font-bold tracking-tight text-white">Create Account</h1>
            <p class="mt-2 text-sm text-gray-300">Please fill in the details below</p>
        </div>

        <!-- Registration Form -->
        <form action="{{ route('registerHandle') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Username -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-200 mb-1">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        value="{{ old('name') }}" 
                        required 
                        placeholder="e.g., johndoe" 
                        class="w-full rounded-lg border border-white/20 bg-white/10 py-2.5 pl-10 pr-4 text-white placeholder-gray-400 backdrop-blur-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50" 
                    />
                </div>
                @error('name') 
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p> 
                @enderror
            </div>

            <!-- Full Name -->
            <div>
                <label for="full_name" class="block text-sm font-medium text-gray-200 mb-1">Full Name</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                        <i class="fa-solid fa-id-card"></i>
                    </span>
                    <input 
                        type="text" 
                        name="full_name" 
                        id="full_name" 
                        value="{{ old('full_name') }}" 
                        required 
                        placeholder="John Doe" 
                        class="w-full rounded-lg border border-white/20 bg-white/10 py-2.5 pl-10 pr-4 text-white placeholder-gray-400 backdrop-blur-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50" 
                    />
                </div>
                @error('full_name') 
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p> 
                @enderror
            </div>

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
                        placeholder="john@example.com" 
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

            <!-- Submit Button -->
            <button type="submit" class="w-full rounded-lg bg-indigo-600 py-3 font-semibold text-white shadow-lg shadow-indigo-600/40 transition hover:bg-indigo-500 active:scale-[0.98]">
                Sign Up
            </button>
        </form>

        <!-- Link to Login -->
        <div class="mt-6 text-center text-sm text-gray-300">
            Already have an account? 
            <a href="{{ route('login') }}" class="font-medium text-indigo-400 hover:text-indigo-300 underline">Sign In</a>
        </div>
    </div>
</div>
@endsection