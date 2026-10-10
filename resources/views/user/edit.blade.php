@extends('layouts.base')

@section('title', 'Edit Profile - Iris App')

@section('content')

@php
    $displayName = old('name', $user->name);
    $fullName = old('full_name', $user->full_name ?? $user->name);
    $email = $user->email;

    $initials = collect(
        preg_split('/\s+/', trim($fullName))
    )
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('');

    if ($initials === '') {
        $initials = strtoupper(substr($displayName, 0, 2));
    }

    $isActive = $user->status === 'ACTIVE';
@endphp

<div
    x-data="{
        name: @js($displayName),
        fullName: @js($fullName),
    }"
    class="min-h-full"
>

    {{-- =========================================================
         PAGE HEADER
    ========================================================== --}}
    <div class="mb-8">

        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

            <div>

                <div class="mb-3 flex items-center gap-2">

                    <span
                        class="inline-flex h-9 w-9 items-center justify-center
                               rounded-xl bg-indigo-500/10
                               text-indigo-400
                               ring-1 ring-indigo-500/20"
                    >
                        <i class="fa-solid fa-user-pen"></i>
                    </span>

                    <span
                        class="text-xs font-semibold uppercase
                               tracking-[0.18em] text-indigo-400"
                    >
                        Account
                    </span>

                </div>


                <div class="flex flex-wrap items-center gap-3">

                    <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                        Edit Profile
                    </h1>

                    @if($isActive)

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full
                                   border border-emerald-500/20
                                   bg-emerald-500/10
                                   px-2.5 py-1
                                   text-[10px] font-bold
                                   uppercase tracking-wider
                                   text-emerald-300"
                        >
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            Active
                        </span>

                    @else

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full
                                   border border-red-500/20
                                   bg-red-500/10
                                   px-2.5 py-1
                                   text-[10px] font-bold
                                   uppercase tracking-wider
                                   text-red-300"
                        >
                            <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
                            {{ ucfirst(strtolower($user->status)) }}
                        </span>

                    @endif

                </div>


                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                    Manage your personal profile information and account details.
                </p>

            </div>


            {{-- BACK --}}
            @if(Route::has('user.profile'))

                <a
                    href="{{ route('user.profile') }}"
                    class="inline-flex items-center justify-center gap-2
                           rounded-xl border border-white/10
                           bg-white/5 px-4 py-2.5
                           text-sm font-semibold text-gray-300
                           transition
                           hover:bg-white/10 hover:text-white
                           active:scale-[0.98]"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Profile
                </a>

            @endif

        </div>

    </div>


    {{-- =========================================================
         MAIN LAYOUT
    ========================================================== --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">


        {{-- =====================================================
             MAIN FORM
        ====================================================== --}}
        <div>

            <form
                action="{{ route('user.update') }}"
                method="POST"
                class="overflow-hidden rounded-2xl
                       border border-white/5
                       bg-gray-900/80
                       shadow-2xl shadow-black/10"
            >

                @csrf
                @method('PUT')


                {{-- =================================================
                     PROFILE INFORMATION
                ================================================== --}}
                <section>

                    <div
                        class="border-b border-white/5
                               px-6 py-5 sm:px-7"
                    >

                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-9 w-9 items-center justify-center
                                       rounded-xl bg-indigo-500/10
                                       text-indigo-400"
                            >
                                <i class="fa-solid fa-id-card"></i>
                            </div>

                            <div>

                                <h2 class="text-sm font-bold text-white">
                                    Profile information
                                </h2>

                                <p class="mt-0.5 text-xs text-gray-500">
                                    Update the information associated with your account.
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="space-y-5 p-6 sm:p-7">

                        {{-- USERNAME --}}
                        <div>

                            <label
                                for="name"
                                class="mb-2 block text-xs
                                       font-semibold uppercase
                                       tracking-wider text-gray-400"
                            >
                                Username
                                <span class="text-red-400">*</span>
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-solid fa-at
                                           pointer-events-none
                                           absolute left-3.5 top-1/2
                                           -translate-y-1/2
                                           text-sm text-gray-500"
                                ></i>

                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    x-model="name"
                                    required
                                    autocomplete="username"
                                    placeholder="e.g. john_doe"
                                    class="w-full rounded-xl
                                           border border-white/10
                                           bg-gray-950/60
                                           py-3 pl-10 pr-4
                                           text-sm text-white
                                           placeholder:text-gray-600
                                           outline-none transition
                                           focus:border-indigo-500/50
                                           focus:ring-4 focus:ring-indigo-500/10"
                                />

                            </div>

                            @error('name')

                                <p
                                    class="mt-2 flex items-center gap-1.5
                                           text-xs text-red-400"
                                >
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- FULL NAME --}}
                        <div>

                            <label
                                for="full_name"
                                class="mb-2 block text-xs
                                       font-semibold uppercase
                                       tracking-wider text-gray-400"
                            >
                                Full name
                                <span class="text-red-400">*</span>
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-solid fa-user
                                           pointer-events-none
                                           absolute left-3.5 top-1/2
                                           -translate-y-1/2
                                           text-sm text-gray-500"
                                ></i>

                                <input
                                    type="text"
                                    name="full_name"
                                    id="full_name"
                                    x-model="fullName"
                                    required
                                    autocomplete="name"
                                    placeholder="e.g. John Doe"
                                    class="w-full rounded-xl
                                           border border-white/10
                                           bg-gray-950/60
                                           py-3 pl-10 pr-4
                                           text-sm text-white
                                           placeholder:text-gray-600
                                           outline-none transition
                                           focus:border-indigo-500/50
                                           focus:ring-4 focus:ring-indigo-500/10"
                                />

                            </div>

                            @error('full_name')

                                <p
                                    class="mt-2 flex items-center gap-1.5
                                           text-xs text-red-400"
                                >
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- EMAIL READONLY --}}
                        <div>

                            <label
                                for="email"
                                class="mb-2 block text-xs
                                       font-semibold uppercase
                                       tracking-wider text-gray-400"
                            >
                                Email address
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-solid fa-envelope
                                           pointer-events-none
                                           absolute left-3.5 top-1/2
                                           -translate-y-1/2
                                           text-sm text-gray-600"
                                ></i>

                                <input
                                    type="email"
                                    id="email"
                                    value="{{ $email }}"
                                    disabled
                                    class="w-full cursor-not-allowed
                                           rounded-xl
                                           border border-white/5
                                           bg-gray-950/30
                                           py-3 pl-10 pr-4
                                           text-sm text-gray-500"
                                />

                            </div>

                            <p class="mt-2 flex items-center gap-1.5 text-xs text-gray-600">
                                <i class="fa-solid fa-lock"></i>
                                Email changes are not available from this page.
                            </p>

                        </div>

                    </div>

                </section>


                {{-- =================================================
                     ACCOUNT STATUS
                ================================================== --}}
                <section class="border-t border-white/5">

                    <div
                        class="border-b border-white/5
                               px-6 py-5 sm:px-7"
                    >

                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-9 w-9 items-center justify-center
                                       rounded-xl bg-emerald-500/10
                                       text-emerald-400"
                            >
                                <i class="fa-solid fa-circle-check"></i>
                            </div>

                            <div>

                                <h2 class="text-sm font-bold text-white">
                                    Account status
                                </h2>

                                <p class="mt-0.5 text-xs text-gray-500">
                                    Current state of your account.
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="p-6 sm:p-7">

                        <div
                            class="flex items-center justify-between
                                   rounded-xl
                                   border border-white/5
                                   bg-gray-950/40
                                   px-4 py-4"
                        >

                            <div class="flex items-center gap-3">

                                <div
                                    class="flex h-9 w-9 items-center justify-center
                                           rounded-lg
                                           {{ $isActive
                                                ? 'bg-emerald-500/10 text-emerald-400'
                                                : 'bg-red-500/10 text-red-400' }}"
                                >
                                    <i
                                        class="fa-solid
                                            {{ $isActive
                                                ? 'fa-circle-check'
                                                : 'fa-circle-xmark' }}"
                                    ></i>
                                </div>

                                <div>

                                    <p class="text-sm font-semibold text-white">
                                        {{ ucfirst(strtolower($user->status)) }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-gray-500">
                                        Your current account status.
                                    </p>

                                </div>

                            </div>


                            <span
                                class="inline-flex items-center gap-1.5
                                       rounded-full
                                       border px-2.5 py-1
                                       text-[10px] font-bold
                                       uppercase tracking-wider
                                       {{ $isActive
                                            ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300'
                                            : 'border-red-500/20 bg-red-500/10 text-red-300' }}"
                            >

                                <span
                                    class="h-1.5 w-1.5 rounded-full
                                        {{ $isActive
                                            ? 'bg-emerald-400'
                                            : 'bg-red-400' }}"
                                ></span>

                                {{ $user->status }}

                            </span>

                        </div>

                    </div>

                </section>


                {{-- =================================================
                     FOOTER ACTIONS
                ================================================== --}}
                <div
                    class="flex flex-col-reverse gap-3
                           border-t border-white/5
                           bg-gray-950/30
                           px-6 py-5
                           sm:flex-row sm:items-center
                           sm:justify-end sm:px-7"
                >

                    @if(Route::has('user.profile'))

                        <a
                            href="{{ route('user.profile') }}"
                            class="inline-flex items-center justify-center
                                   gap-2 rounded-xl
                                   border border-white/10
                                   bg-white/5 px-5 py-2.5
                                   text-sm font-semibold text-gray-300
                                   transition
                                   hover:bg-white/10
                                   hover:text-white
                                   active:scale-[0.98]"
                        >
                            Cancel
                        </a>

                    @endif


                    <button
                        type="submit"
                        class="inline-flex items-center justify-center
                               gap-2 rounded-xl
                               bg-indigo-600 px-6 py-2.5
                               text-sm font-bold text-white
                               shadow-lg shadow-indigo-600/20
                               transition
                               hover:-translate-y-0.5
                               hover:bg-indigo-500
                               active:translate-y-0"
                    >
                        <i class="fa-solid fa-floppy-disk"></i>
                        Save Changes
                    </button>

                </div>

            </form>

        </div>


        {{-- =====================================================
             SIDEBAR
        ====================================================== --}}
        <aside class="space-y-4">


            {{-- =================================================
                 USER PREVIEW
            ================================================== --}}
            <div
                class="overflow-hidden rounded-2xl
                       border border-white/5
                       bg-gray-900/80
                       shadow-2xl shadow-black/10"
            >

                <div
                    class="border-b border-white/5
                           px-5 py-4"
                >

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-8 w-8 items-center justify-center
                                   rounded-lg bg-indigo-500/10
                                   text-indigo-400"
                        >
                            <i class="fa-solid fa-eye text-xs"></i>
                        </div>

                        <div>

                            <h2 class="text-sm font-bold text-white">
                                Profile preview
                            </h2>

                            <p class="text-xs text-gray-500">
                                Current account identity
                            </p>

                        </div>

                    </div>

                </div>


                <div class="p-5">

                    <div
                        class="rounded-xl border border-white/5
                               bg-gray-950/50 p-4"
                    >

                        <div class="flex items-center gap-3">

                            {{-- AVATAR --}}
                            <div
                                class="flex h-12 w-12 flex-shrink-0
                                       items-center justify-center
                                       rounded-xl
                                       bg-gradient-to-br
                                       from-indigo-500/30 to-violet-500/20
                                       text-sm font-black
                                       text-indigo-300
                                       ring-1 ring-indigo-500/20"
                                x-text="(
                                    fullName || name || 'User'
                                )
                                .split(' ')
                                .filter(Boolean)
                                .slice(0, 2)
                                .map(word => word.charAt(0).toUpperCase())
                                .join('')"
                            ></div>


                            <div class="min-w-0">

                                <p
                                    class="truncate text-sm font-bold text-white"
                                    x-text="name || 'Username'"
                                ></p>

                                <p
                                    class="mt-1 truncate text-xs text-gray-500"
                                    x-text="fullName || 'Full name'"
                                ></p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 ACCOUNT
            ================================================== --}}
            <div
                class="rounded-2xl
                       border border-white/5
                       bg-gray-900/80
                       p-5
                       shadow-2xl shadow-black/10"
            >

                <div class="flex items-start gap-3">

                    <div
                        class="flex h-9 w-9 flex-shrink-0
                               items-center justify-center
                               rounded-xl bg-violet-500/10
                               text-violet-400"
                    >
                        <i class="fa-solid fa-id-badge"></i>
                    </div>

                    <div class="min-w-0">

                        <h3 class="text-sm font-bold text-white">
                            Account
                        </h3>

                        <div class="mt-3 space-y-2.5">

                            <div>

                                <p class="text-[10px] uppercase tracking-wider text-gray-600">
                                    Username
                                </p>

                                <p class="mt-0.5 truncate text-xs font-semibold text-gray-300">
                                    {{ $user->name }}
                                </p>

                            </div>


                            <div>

                                <p class="text-[10px] uppercase tracking-wider text-gray-600">
                                    Email
                                </p>

                                <p class="mt-0.5 truncate text-xs font-semibold text-gray-300">
                                    {{ $user->email }}
                                </p>

                            </div>


                            <div>

                                <p class="text-[10px] uppercase tracking-wider text-gray-600">
                                    Status
                                </p>

                                <p
                                    class="mt-0.5 text-xs font-semibold
                                        {{ $isActive
                                            ? 'text-emerald-400'
                                            : 'text-red-400' }}"
                                >
                                    {{ $user->status }}
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 PROFILE INFO
            ================================================== --}}
            <div
                class="rounded-2xl
                       border border-white/5
                       bg-gray-900/80
                       p-5
                       shadow-2xl shadow-black/10"
            >

                <div class="flex items-start gap-3">

                    <div
                        class="flex h-9 w-9 flex-shrink-0
                               items-center justify-center
                               rounded-xl bg-emerald-500/10
                               text-emerald-400"
                    >
                        <i class="fa-solid fa-circle-info"></i>
                    </div>

                    <div>

                        <h3 class="text-sm font-bold text-white">
                            Profile information
                        </h3>

                        <p class="mt-2 text-xs leading-5 text-gray-500">
                            Update your username and full name.
                            Your email address is currently managed separately.
                        </p>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 ACCOUNT ID
            ================================================== --}}
            <div
                class="rounded-2xl
                       border border-white/5
                       bg-gray-900/60
                       px-5 py-4"
            >

                <div class="flex items-center justify-between gap-4">

                    <span
                        class="text-[10px] font-semibold
                               uppercase tracking-wider
                               text-gray-600"
                    >
                        Account ID
                    </span>

                    <span
                        class="truncate font-mono text-[11px]
                               text-gray-500"
                    >
                        {{ $user->id }}
                    </span>

                </div>

            </div>

        </aside>

    </div>

</div>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>

@endsection