@extends('layouts.base')

@section('title', 'User Profile - Iris App')

@section('content')

@php
    $currentUser = $user ?? auth()->user();

    /*
    |--------------------------------------------------------------------------
    | User data
    |--------------------------------------------------------------------------
    */

    $displayName = $currentUser->name;
    $fullName = $currentUser->full_name;
    $email = $currentUser->email;
    $status = strtoupper($currentUser->status ?? 'UNKNOWN');

    $initials = collect(
        preg_split('/\s+/', trim($displayName))
    )
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('');

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */

    $permissionRelations = $currentUser
        ->permissionRelations()
        ->orderBy('module')
        ->orderBy('name')
        ->get();

    $permissionGroups = $permissionRelations
        ->groupBy('module');

    $permissionCount = $permissionRelations->count();

    $isSuperAdmin = $currentUser->isSuperAdmin();

    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    $isActive = $status === 'ACTIVE';

    /*
    |--------------------------------------------------------------------------
    | Permission icons
    |--------------------------------------------------------------------------
    */

    $moduleIcons = [
        'auth'              => 'fa-shield-halved',
        'users'             => 'fa-users',
        'permissions'       => 'fa-key',
        'categories'        => 'fa-folder',
        'tags'               => 'fa-tags',
        'ticket_statuses'   => 'fa-list-check',
        'ticket_priorities' => 'fa-arrow-up-wide-short',
        'tickets'           => 'fa-ticket',
        'ticket_messages'   => 'fa-comments',
        'ticket_attachments'=> 'fa-paperclip',
        'ticket_watchers'   => 'fa-eye',
    ];

    $moduleLabels = [
        'auth'               => 'Authentication',
        'users'              => 'Users',
        'permissions'        => 'Permissions',
        'categories'         => 'Categories',
        'tags'               => 'Tags',
        'ticket_statuses'    => 'Ticket Statuses',
        'ticket_priorities'  => 'Ticket Priorities',
        'tickets'            => 'Tickets',
        'ticket_messages'    => 'Ticket Messages',
        'ticket_attachments' => 'Ticket Attachments',
        'ticket_watchers'    => 'Ticket Watchers',
    ];
@endphp

<div class="min-h-screen w-full px-4 py-8 md:px-8 max-w-7xl mx-auto space-y-8">

    {{-- ============================================================
    HEADER
    ============================================================= --}}

    <div
        x-data="{ showDeleteModal: false }"
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4"
    >

        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-white flex items-center gap-3">
                <i class="fa-solid fa-circle-user text-indigo-500"></i>
                User Profile
            </h1>

            <p class="mt-1 text-sm text-gray-400">
                Manage account information, security settings, and permissions.
            </p>
        </div>


        {{-- ACTIONS --}}
        <div class="flex items-center gap-3">

            {{-- EDIT --}}
            @if(Route::has('user.edit'))
                <a
                    href="{{ route('user.edit') }}"
                    class="inline-flex items-center gap-2 rounded-xl
                        bg-indigo-600 px-4 py-2.5
                        text-sm font-semibold text-white
                        shadow-lg shadow-indigo-600/30
                        transition
                        hover:bg-indigo-500
                        active:scale-[0.98]"
                >
                    <i class="fa-solid fa-pen-to-square"></i>
                    Edit Profile
                </a>
            @endif


            {{-- UPDATE PASSWORD --}}
            @if(Route::has('user.update.password'))
                <a
                    href="{{ route('user.update.password') }}"
                    class="inline-flex items-center gap-2 rounded-xl
                        bg-amber-600 px-4 py-2.5
                        text-sm font-semibold text-white
                        shadow-lg shadow-amber-600/30
                        transition
                        hover:bg-amber-500
                        active:scale-[0.98]"
                >
                    <i class="fa-solid fa-key"></i>
                    Update Password
                </a>
            @endif


            {{-- DELETE --}}
            @if(Route::has('user.delete'))
                <button
                    type="button"
                    @click="showDeleteModal = true"
                    class="inline-flex items-center gap-2 rounded-xl
                        bg-red-600 px-4 py-2.5
                        text-sm font-semibold text-white
                        shadow-lg shadow-red-600/30
                        transition
                        hover:bg-red-500
                        active:scale-[0.98]"
                >
                    <i class="fa-solid fa-trash-can"></i>
                    Delete Profile
                </button>
            @endif

        </div>

        {{-- ============================================================
            DELETE CONFIRMATION MODAL
        ============================================================= --}}

        <div
            x-show="showDeleteModal"
            x-cloak

            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"

            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"

            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
        >

            <div
                @click.away="showDeleteModal = false"
                class="bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-5"
            >

                {{-- WARNING ICON --}}
                <div class="flex justify-center">
                    <div class="flex items-center justify-center h-14 w-14 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                        <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                    </div>
                </div>


                {{-- TITLE --}}
                <div class="text-center space-y-2">

                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                        Delete Profile
                    </h3>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Are you sure you want to delete this profile?
                    </p>

                </div>


                {{-- CONSEQUENCES --}}
                <div class="rounded-xl border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-950/20 p-4">

                    <div class="flex items-start gap-3">

                        <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5"></i>

                        <div class="text-sm text-red-800 dark:text-red-300 space-y-2">

                            <p class="font-semibold">
                                This action will affect the following:
                            </p>

                            <ul class="list-disc list-inside space-y-1 text-red-700 dark:text-red-400">
                                <li>Your account will be deactivated.</li>
                                <li>You will no longer be able to access the system.</li>
                                <li>Your permissions will no longer be available.</li>
                                <li>Your profile will be removed from active users.</li>
                            </ul>

                        </div>

                    </div>

                </div>


                {{-- USER IDENTIFICATION --}}
                <div class="rounded-xl bg-gray-100 dark:bg-gray-700/50 px-4 py-3">

                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Account
                    </p>

                    <p class="mt-1 font-semibold text-gray-900 dark:text-white">
                        {{ $currentUser->name }}
                    </p>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $currentUser->email }}
                    </p>

                </div>


                {{-- ACTIONS --}}
                <div class="flex gap-3 pt-2">

                    {{-- CANCEL --}}
                    <button
                        @click="showDeleteModal = false"
                        type="button"
                        class="w-full px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition"
                    >
                        Cancel
                    </button>


                    {{-- CONFIRM DELETE --}}
                    <form
                        action="{{ route('user.delete', $currentUser->id) }}"
                        method="POST"
                        class="w-full"
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition shadow-md shadow-red-600/20"
                        >
                            <i class="fa-solid fa-trash-can mr-1"></i>
                            Delete Profile
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

    {{-- ============================================================
         PROFILE HERO
    ============================================================= --}}

    <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-zinc-900/90 via-black/80 to-zinc-950/90 p-6 md:p-8 shadow-2xl backdrop-blur-xl">

        <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full bg-indigo-600/20 blur-3xl pointer-events-none"></div>

        <div class="absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-purple-600/15 blur-3xl pointer-events-none"></div>


        <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start gap-6 md:gap-8">

            {{-- Avatar --}}
            <div class="relative">

                <div class="h-28 w-28 md:h-32 md:w-32 rounded-2xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-pink-500 p-1 shadow-xl">

                    <div class="h-full w-full rounded-[14px] bg-zinc-950 flex items-center justify-center text-4xl font-black text-white uppercase tracking-wider">
                        {{ $initials }}
                    </div>

                </div>

                <span class="absolute -bottom-1 -right-1 flex h-5 w-5">

                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>

                    <span class="relative inline-flex rounded-full h-5 w-5 bg-emerald-500 border-2 border-zinc-950"></span>

                </span>

            </div>


            {{-- User metadata --}}
            <div class="flex-1 text-center md:text-left space-y-3">

                <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">

                    <h2 class="text-3xl font-bold text-white tracking-tight">
                        {{ $displayName }}
                    </h2>


                    {{-- Super Admin --}}
                    @if($isSuperAdmin)

                        <span class="inline-flex items-center gap-1.5 rounded-full bg-purple-500/15 px-3 py-1 text-xs font-semibold text-purple-400 border border-purple-500/30">

                            <i class="fa-solid fa-crown text-[11px]"></i>

                            Super Admin

                        </span>

                    @endif


                    {{-- Status --}}
                    <span
                        class="
                            inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold border
                            {{ $isActive
                                ? 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30'
                                : 'bg-red-500/15 text-red-400 border-red-500/30'
                            }}
                        "
                    >

                        <i class="fa-solid fa-circle-check text-[10px]"></i>

                        {{ $status }}

                    </span>

                </div>


                {{-- Full name --}}
                <p class="text-sm text-gray-300 flex items-center justify-center md:justify-start gap-2">

                    <i class="fa-regular fa-id-card text-indigo-400"></i>

                    {{ $fullName }}

                </p>


                {{-- Email --}}
                <p class="text-sm text-gray-300 flex items-center justify-center md:justify-start gap-2">

                    <i class="fa-regular fa-envelope text-indigo-400"></i>

                    {{ $email }}

                    @if($currentUser->email_verified_at)

                        <i
                            class="fa-solid fa-circle-check text-emerald-400 text-xs"
                            title="Email verified"
                        ></i>

                    @endif

                </p>


                {{-- Metadata --}}
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-6 pt-2 text-xs text-gray-400 border-t border-white/10">

                    <span class="flex items-center gap-2">

                        <i class="fa-regular fa-id-card text-gray-500"></i>

                        ID:

                        <strong class="text-gray-200">
                            #{{ $currentUser->id }}
                        </strong>

                    </span>


                    <span class="flex items-center gap-2">

                        <i class="fa-regular fa-calendar-check text-gray-500"></i>

                        Member Since:

                        <strong class="text-gray-200">
                            {{ $currentUser->created_at?->format('M d, Y') ?? '—' }}
                        </strong>

                    </span>


                    <span class="flex items-center gap-2">

                        <i class="fa-regular fa-clock text-gray-500"></i>

                        Last Login:

                        <strong class="text-gray-200">

                            @if($currentUser->last_login_at)

                                {{ $currentUser->last_login_at->diffForHumans() }}

                            @else

                                Never

                            @endif

                        </strong>

                    </span>

                </div>

            </div>

        </div>

    </div>


    {{-- 
    ============================================================
    MAIN GRID
    ============================================================= 
    --}}

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">


        {{-- 
        ========================================================
        LEFT COLUMN
        ========================================================= 
        --}}

        <div class="lg:col-span-1 space-y-6">


            {{-- Account details --}}
            <div class="rounded-2xl border border-white/10 bg-black/40 p-6 shadow-2xl backdrop-blur-md space-y-5">

                <h3 class="text-lg font-bold text-white flex items-center gap-2.5 pb-3 border-b border-white/10">

                    <i class="fa-solid fa-user-gear text-indigo-400"></i>

                    Account Details

                </h3>


                <div class="space-y-4 text-sm">

                    {{-- Full name --}}
                    <div>

                        <span class="block text-xs font-medium text-gray-400 uppercase tracking-wider">
                            Full Name
                        </span>

                        <p class="mt-0.5 font-semibold text-gray-200">
                            {{ $fullName }}
                        </p>

                    </div>


                    {{-- Username --}}
                    <div>

                        <span class="block text-xs font-medium text-gray-400 uppercase tracking-wider">
                            Username
                        </span>

                        <p class="mt-0.5 font-semibold text-gray-200">
                            {{ $displayName }}
                        </p>

                    </div>


                    {{-- Email --}}
                    <div>

                        <span class="block text-xs font-medium text-gray-400 uppercase tracking-wider">
                            Email Address
                        </span>

                        <p class="mt-0.5 font-semibold text-gray-200 flex items-center gap-2">

                            {{ $email }}

                            @if($currentUser->email_verified_at)

                                <i
                                    class="fa-solid fa-circle-check text-emerald-400 text-xs"
                                    title="Verified"
                                ></i>

                            @endif

                        </p>

                    </div>


                    {{-- Status --}}
                    <div>

                        <span class="block text-xs font-medium text-gray-400 uppercase tracking-wider">
                            Status
                        </span>

                        <div class="mt-1">

                            <span
                                class="
                                    inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium border
                                    {{ $isActive
                                        ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                                        : 'bg-red-500/10 text-red-400 border-red-500/20'
                                    }}
                                "
                            >

                                <span
                                    class="
                                        h-1.5 w-1.5 rounded-full
                                        {{ $isActive ? 'bg-emerald-400' : 'bg-red-400' }}
                                    "
                                ></span>

                                {{ $status }}

                            </span>

                        </div>

                    </div>


                    {{-- Version --}}
                    <div>

                        <span class="block text-xs font-medium text-gray-400 uppercase tracking-wider">
                            Account Version
                        </span>

                        <p class="mt-0.5 font-semibold text-gray-200">
                            v{{ $currentUser->version }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- System metrics --}}
            <div class="rounded-2xl border border-white/10 bg-black/40 p-6 shadow-2xl backdrop-blur-md space-y-4">

                <h3 class="text-lg font-bold text-white flex items-center gap-2.5 pb-3 border-b border-white/10">

                    <i class="fa-solid fa-chart-line text-indigo-400"></i>

                    System Metrics

                </h3>


                <div class="grid grid-cols-2 gap-4">

                    {{-- Access --}}
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4 text-center">

                        <span class="block text-2xl font-black text-white">

                            @if($isSuperAdmin)
                                Full
                            @else
                                {{ $permissionCount }}
                            @endif

                        </span>

                        <span class="text-xs text-gray-400">
                            Access Level
                        </span>

                    </div>


                    {{-- Permissions --}}
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4 text-center">

                        <span class="block text-2xl font-black text-indigo-400">
                            {{ $permissionCount }}
                        </span>

                        <span class="text-xs text-gray-400">
                            Permissions
                        </span>

                    </div>

                </div>

            </div>

        </div>


        {{-- ========================================================
             RIGHT COLUMN
        ========================================================= --}}

        <div class="lg:col-span-2 space-y-6">

            <div class="rounded-2xl border border-white/10 bg-black/40 p-6 md:p-8 shadow-2xl backdrop-blur-md space-y-6">


                {{-- Header --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-white/10">

                    <div>

                        <h3 class="text-xl font-bold text-white flex items-center gap-2.5">

                            <i class="fa-solid fa-key text-indigo-400"></i>

                            Granted Permissions

                        </h3>

                        <p class="text-xs text-gray-400 mt-0.5">

                            Permissions currently assigned to this account.

                        </p>

                    </div>


                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">

                        <i class="fa-solid fa-lock-open text-[10px]"></i>

                        {{ $permissionCount }} Granted

                    </span>

                </div>


                {{-- ==================================================
                     SUPER ADMIN
                =================================================== --}}

                @if($isSuperAdmin)

                    <div class="rounded-xl border border-purple-500/20 bg-purple-500/5 p-5">

                        <div class="flex items-start gap-3">

                            <div class="mt-0.5 text-purple-400">

                                <i class="fa-solid fa-crown"></i>

                            </div>

                            <div>

                                <h4 class="text-sm font-bold text-purple-300">
                                    Super Administrator
                                </h4>

                                <p class="mt-1 text-xs text-gray-400">

                                    This account has unrestricted administrative access to the system.

                                </p>

                            </div>

                        </div>

                    </div>

                @endif


                {{-- ==================================================
                     PERMISSION GROUPS
                =================================================== --}}

                @if($permissionGroups->isNotEmpty())

                    <div class="space-y-6">

                        @foreach($permissionGroups as $module => $groupPermissions)

                            <div class="rounded-xl border border-white/10 bg-white/5 p-4 md:p-5 space-y-3">

                                {{-- Group header --}}
                                <div class="flex items-center justify-between">

                                    <h4 class="text-sm font-bold text-indigo-300 uppercase tracking-wider flex items-center gap-2">

                                        <i class="fa-solid {{ $moduleIcons[$module] ?? 'fa-layer-group' }} text-xs"></i>

                                        {{ $moduleLabels[$module] ?? str_replace('_', ' ', $module) }}

                                    </h4>


                                    <span class="text-xs text-gray-400 bg-white/10 px-2.5 py-0.5 rounded-full font-mono">

                                        {{ $groupPermissions->count() }}

                                    </span>

                                </div>


                                {{-- Permissions --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">

                                    @foreach($groupPermissions as $permission)

                                        <div class="flex items-center justify-between p-3 rounded-lg border border-emerald-500/20 bg-emerald-500/5 transition">

                                            <div class="flex items-center gap-2.5">

                                                <span class="text-emerald-400">

                                                    <i class="fa-solid fa-check text-sm"></i>

                                                </span>


                                                <div>

                                                    <p class="text-xs font-semibold text-gray-200">

                                                        {{ $permission->name }}

                                                    </p>

                                                    <code class="text-[10px] text-gray-400 font-mono">

                                                        {{ $permission->slug }}

                                                    </code>

                                                </div>

                                            </div>


                                            <span class="text-emerald-400 text-xs">

                                                <i class="fa-solid fa-check"></i>

                                            </span>

                                        </div>

                                    @endforeach

                                </div>

                            </div>

                        @endforeach

                    </div>

                @else

                    {{-- No permissions --}}
                    <div class="rounded-xl border border-white/10 bg-white/5 p-8 text-center">

                        <div class="text-gray-500 mb-3">

                            <i class="fa-solid fa-lock text-3xl"></i>

                        </div>

                        <h4 class="text-sm font-semibold text-gray-300">

                            No permissions assigned

                        </h4>

                        <p class="mt-1 text-xs text-gray-500">

                            This account currently has no explicit permissions.

                        </p>

                    </div>

                @endif

            </div>

        </div>

    </div>

</div>

@endsection