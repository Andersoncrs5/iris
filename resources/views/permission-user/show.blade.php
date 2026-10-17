@extends('layouts.base')

@section('title', 'Permission Assignment Details - Iris App')

@section('content')

<div
    class="min-h-full"
    x-data="{
        showDeleteModal: false,
        showRestoreModal: false
    }"
    @keydown.escape.window="showDeleteModal = false; showRestoreModal = false"
>

    @php
        $isDeleted = $permissionUser->trashed();

        $user = $permissionUser->user;
        $permission = $permissionUser->permission;

        $deleteRouteExists = Route::has('permission.user.delete');
        $restoreRouteExists = Route::has('permission.user.restore');
        $listRouteExists = Route::has('permission.user.list');
    @endphp

    {{-- =========================================================
         PAGE HEADER
    ========================================================== --}}
    <div class="mb-8">

        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

            <div>
                <div class="mb-3 flex items-center gap-2">

                    <span
                        class="inline-flex h-9 w-9 items-center justify-center
                               rounded-xl bg-indigo-500/10 text-indigo-400
                               ring-1 ring-indigo-500/20"
                    >
                        <i class="fa-solid fa-user-shield"></i>
                    </span>

                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-400">
                        Access Control
                    </span>

                </div>

                <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                    Permission Assignment Details
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                    Inspect the user, permission and metadata associated with
                    this assignment.
                </p>
            </div>

            {{-- HEADER ACTIONS --}}
            <div class="flex flex-wrap items-center gap-2">

                @if($listRouteExists)
                    <a
                        href="{{ route('permission.user.list') }}"
                        class="inline-flex items-center justify-center gap-2
                               rounded-xl border border-white/10 bg-white/5
                               px-4 py-2.5 text-sm font-semibold text-gray-300
                               transition hover:bg-white/10 hover:text-white"
                    >
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to list
                    </a>
                @endif

                @if($isDeleted)

                    {{-- RESTORE BUTTON --}}
                    @if($restoreRouteExists)
                        <button
                            type="button"
                            @click="showRestoreModal = true"
                            class="inline-flex items-center justify-center gap-2
                                   rounded-xl bg-emerald-600 px-4 py-2.5
                                   text-sm font-bold text-white transition
                                   hover:bg-emerald-500"
                        >
                            <i class="fa-solid fa-rotate-left"></i>
                            Restore
                        </button>
                    @endif

                @else

                    {{-- DELETE BUTTON --}}
                    @if($deleteRouteExists)
                        <button
                            type="button"
                            @click="showDeleteModal = true"
                            class="inline-flex items-center justify-center gap-2
                                   rounded-xl border border-red-500/20
                                   bg-red-500/10 px-4 py-2.5
                                   text-sm font-bold text-red-300 transition
                                   hover:bg-red-500/20 hover:text-red-200"
                        >
                            <i class="fa-solid fa-trash-can"></i>
                            Delete
                        </button>
                    @endif

                @endif

            </div>
        </div>

        {{-- FEEDBACK --}}
        @if(session('success'))
            <div
                class="mt-6 flex items-start gap-3 rounded-xl
                       border border-emerald-500/20 bg-emerald-500/10 p-4"
            >
                <i class="fa-solid fa-circle-check mt-0.5 text-emerald-400"></i>

                <p class="text-sm text-emerald-300">
                    {{ session('success') }}
                </p>
            </div>
        @endif

        @if(session('error'))
            <div
                class="mt-6 flex items-start gap-3 rounded-xl
                       border border-red-500/20 bg-red-500/10 p-4"
            >
                <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-400"></i>

                <p class="text-sm text-red-300">
                    {{ session('error') }}
                </p>
            </div>
        @endif

        {{-- STATUS BANNER --}}
        @if($isDeleted)
            <div
                class="mt-6 flex items-start gap-3 rounded-xl
                       border border-red-500/20 bg-red-500/5 p-4"
            >
                <i class="fa-solid fa-box-archive mt-0.5 text-red-400"></i>

                <div>
                    <p class="text-sm font-semibold text-red-300">
                        This assignment has been deleted.
                    </p>

                    <p class="mt-1 text-xs leading-5 text-gray-400">
                        The record is soft-deleted. You can restore it if you
                        have the required permission.
                    </p>
                </div>
            </div>
        @else
            <div
                class="mt-6 flex items-start gap-3 rounded-xl
                       border border-emerald-500/20 bg-emerald-500/5 p-4"
            >
                <i class="fa-solid fa-circle-check mt-0.5 text-emerald-400"></i>

                <div>
                    <p class="text-sm font-semibold text-emerald-300">
                        Assignment is active.
                    </p>

                    <p class="mt-1 text-xs leading-5 text-gray-400">
                        This permission assignment is not soft-deleted.
                    </p>
                </div>
            </div>
        @endif

    </div>

    {{-- =========================================================
         SUMMARY CARDS
    ========================================================== --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

        {{-- ASSIGNMENT --}}
        <div class="rounded-2xl border border-white/5 bg-gray-900/70 p-5 shadow-xl shadow-black/5">
            <div class="flex items-center gap-3">

                <div
                    class="flex h-11 w-11 items-center justify-center
                           rounded-xl bg-indigo-500/10 text-indigo-400"
                >
                    <i class="fa-solid fa-link"></i>
                </div>

                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                        Assignment ID
                    </p>

                    <p class="mt-1 text-xl font-black text-white">
                        #{{ $permissionUser->id }}
                    </p>
                </div>

            </div>
        </div>

        {{-- VERSION --}}
        <div class="rounded-2xl border border-white/5 bg-gray-900/70 p-5 shadow-xl shadow-black/5">
            <div class="flex items-center gap-3">

                <div
                    class="flex h-11 w-11 items-center justify-center
                           rounded-xl bg-violet-500/10 text-violet-400"
                >
                    <i class="fa-solid fa-code-branch"></i>
                </div>

                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                        Version
                    </p>

                    <p class="mt-1 text-xl font-black text-white">
                        {{ $permissionUser->version }}
                    </p>
                </div>

            </div>
        </div>

        {{-- STATUS --}}
        <div class="rounded-2xl border border-white/5 bg-gray-900/70 p-5 shadow-xl shadow-black/5">
            <div class="flex items-center gap-3">

                <div
                    class="flex h-11 w-11 items-center justify-center
                           rounded-xl {{ $isDeleted
                               ? 'bg-red-500/10 text-red-400'
                               : 'bg-emerald-500/10 text-emerald-400' }}"
                >
                    <i class="fa-solid {{ $isDeleted ? 'fa-box-archive' : 'fa-check-circle' }}"></i>
                </div>

                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                        Status
                    </p>

                    <p class="mt-1 text-xl font-black {{ $isDeleted ? 'text-red-300' : 'text-emerald-300' }}">
                        {{ $isDeleted ? 'Deleted' : 'Active' }}
                    </p>
                </div>

            </div>
        </div>

    </div>

    {{-- =========================================================
         DETAILS CONTENT
    ========================================================== --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

        {{-- =====================================================
             USER DETAILS
        ====================================================== --}}
        <section class="overflow-hidden rounded-2xl border border-white/5 bg-gray-900/80 shadow-xl shadow-black/5">

            <div class="border-b border-white/5 px-6 py-5">

                <div class="flex items-center gap-3">

                    <div
                        class="flex h-10 w-10 items-center justify-center
                               rounded-xl bg-indigo-500/10 text-indigo-400"
                    >
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <div>
                        <h2 class="text-sm font-bold text-white">
                            User information
                        </h2>

                        <p class="mt-0.5 text-xs text-gray-500">
                            Account associated with this assignment
                        </p>
                    </div>

                </div>
            </div>

            <div class="space-y-5 p-6">

                {{-- NAME --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Name
                    </p>

                    <p class="mt-1.5 break-words text-sm font-semibold text-white">
                        {{ $user?->name ?? 'User not available' }}
                    </p>
                </div>

                {{-- FULL NAME --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Full name
                    </p>

                    <p class="mt-1.5 break-words text-sm text-gray-300">
                        {{ $user?->full_name ?: 'Not provided' }}
                    </p>
                </div>

                {{-- EMAIL --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Email
                    </p>

                    <p class="mt-1.5 break-all text-sm text-gray-300">
                        {{ $user?->email ?? 'Not available' }}
                    </p>
                </div>

                {{-- USER ID --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                        User ID
                    </p>

                    <code class="mt-1.5 inline-block break-all rounded-lg border border-white/5 bg-gray-950/70 px-2.5 py-1.5 text-xs text-gray-300">
                        {{ $permissionUser->user_id }}
                    </code>
                </div>

                {{-- USER STATUS --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Account status
                    </p>

                    @if($user)
                        <span
                            class="mt-1.5 inline-flex items-center gap-1.5 rounded-full
                                   border border-white/10 bg-white/5 px-2.5 py-1
                                   text-xs font-semibold text-gray-300"
                        >
                            <span class="h-1.5 w-1.5 rounded-full bg-indigo-400"></span>
                            {{ $user->status ?: 'Not specified' }}
                        </span>
                    @else
                        <p class="mt-1.5 text-sm text-gray-500">
                            User record is unavailable.
                        </p>
                    @endif
                </div>

            </div>
        </section>

        {{-- =====================================================
             PERMISSION DETAILS
        ====================================================== --}}
        <section class="overflow-hidden rounded-2xl border border-white/5 bg-gray-900/80 shadow-xl shadow-black/5">

            <div class="border-b border-white/5 px-6 py-5">

                <div class="flex items-center gap-3">

                    <div
                        class="flex h-10 w-10 items-center justify-center
                               rounded-xl bg-violet-500/10 text-violet-400"
                    >
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                    <div>
                        <h2 class="text-sm font-bold text-white">
                            Permission information
                        </h2>

                        <p class="mt-0.5 text-xs text-gray-500">
                            Permission associated with this assignment
                        </p>
                    </div>

                </div>
            </div>

            <div class="space-y-5 p-6">

                {{-- PERMISSION NAME --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Permission name
                    </p>

                    <p class="mt-1.5 break-words text-sm font-semibold text-white">
                        {{ $permission?->name ?? 'Permission not available' }}
                    </p>
                </div>

                {{-- SLUG --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Slug
                    </p>

                    @if($permission?->slug)
                        <code class="mt-1.5 inline-block max-w-full break-all rounded-lg border border-white/5 bg-gray-950/70 px-2.5 py-1.5 text-xs text-indigo-300">
                            {{ $permission->slug }}
                        </code>
                    @else
                        <p class="mt-1.5 text-sm text-gray-500">
                            Not available
                        </p>
                    @endif
                </div>

                {{-- MODULE --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Module
                    </p>

                    @if($permission?->module)
                        <span
                            class="mt-1.5 inline-flex items-center gap-2 rounded-full
                                   border border-indigo-500/20 bg-indigo-500/10
                                   px-2.5 py-1 text-xs font-semibold text-indigo-300"
                        >
                            <i class="fa-solid fa-cubes-stacked"></i>
                            {{ $permission->module }}
                        </span>
                    @else
                        <p class="mt-1.5 text-sm text-gray-500">
                            Not available
                        </p>
                    @endif
                </div>

                {{-- PERMISSION ID --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Permission ID
                    </p>

                    <code class="mt-1.5 inline-block break-all rounded-lg border border-white/5 bg-gray-950/70 px-2.5 py-1.5 text-xs text-gray-300">
                        {{ $permissionUser->permission_id }}
                    </code>
                </div>

                {{-- DESCRIPTION --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Description
                    </p>

                    <p class="mt-1.5 whitespace-pre-line break-words text-sm leading-6 text-gray-400">
                        {{ $permission?->description ?: 'No description provided.' }}
                    </p>
                </div>

            </div>
        </section>

        {{-- =====================================================
             ASSIGNMENT METADATA
        ====================================================== --}}
        <section class="overflow-hidden rounded-2xl border border-white/5 bg-gray-900/80 shadow-xl shadow-black/5 xl:col-span-2">

            <div class="border-b border-white/5 px-6 py-5">

                <div class="flex items-center gap-3">

                    <div
                        class="flex h-10 w-10 items-center justify-center
                               rounded-xl bg-amber-500/10 text-amber-400"
                    >
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>

                    <div>
                        <h2 class="text-sm font-bold text-white">
                            Assignment metadata
                        </h2>

                        <p class="mt-0.5 text-xs text-gray-500">
                            Creation, update and deletion timestamps
                        </p>
                    </div>

                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2 xl:grid-cols-4">

                {{-- CREATED AT --}}
                <div class="rounded-xl border border-white/5 bg-gray-950/40 p-4">
                    <div class="flex items-center gap-2 text-gray-500">
                        <i class="fa-regular fa-calendar text-xs"></i>

                        <p class="text-xs font-semibold uppercase tracking-wider">
                            Created at
                        </p>
                    </div>

                    <p class="mt-3 text-sm font-semibold text-gray-200">
                        {{ $permissionUser->created_at?->format('d/m/Y') ?? 'Not available' }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        {{ $permissionUser->created_at?->format('H:i:s') ?? '' }}
                    </p>
                </div>

                {{-- UPDATED AT --}}
                <div class="rounded-xl border border-white/5 bg-gray-950/40 p-4">
                    <div class="flex items-center gap-2 text-gray-500">
                        <i class="fa-solid fa-clock-rotate-left text-xs"></i>

                        <p class="text-xs font-semibold uppercase tracking-wider">
                            Updated at
                        </p>
                    </div>

                    <p class="mt-3 text-sm font-semibold text-gray-200">
                        {{ $permissionUser->updated_at?->format('d/m/Y') ?? 'Not available' }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        {{ $permissionUser->updated_at?->format('H:i:s') ?? '' }}
                    </p>
                </div>

                {{-- DELETED AT --}}
                <div class="rounded-xl border border-white/5 bg-gray-950/40 p-4">
                    <div class="flex items-center gap-2 text-gray-500">
                        <i class="fa-solid fa-box-archive text-xs"></i>

                        <p class="text-xs font-semibold uppercase tracking-wider">
                            Deleted at
                        </p>
                    </div>

                    @if($permissionUser->deleted_at)
                        <p class="mt-3 text-sm font-semibold text-red-300">
                            {{ $permissionUser->deleted_at->format('d/m/Y') }}
                        </p>

                        <p class="mt-1 text-xs text-gray-500">
                            {{ $permissionUser->deleted_at->format('H:i:s') }}
                        </p>
                    @else
                        <p class="mt-3 text-sm font-semibold text-gray-400">
                            Not deleted
                        </p>
                    @endif
                </div>

                {{-- VERSION --}}
                <div class="rounded-xl border border-white/5 bg-gray-950/40 p-4">
                    <div class="flex items-center gap-2 text-gray-500">
                        <i class="fa-solid fa-code-branch text-xs"></i>

                        <p class="text-xs font-semibold uppercase tracking-wider">
                            Version
                        </p>
                    </div>

                    <p class="mt-3 text-2xl font-black text-white">
                        {{ $permissionUser->version }}
                    </p>

                    <p class="mt-1 text-xs text-gray-500">
                        Assignment version
                    </p>
                </div>

            </div>
        </section>

    </div>

    {{-- =========================================================
         DELETE CONFIRMATION MODAL
    ========================================================== --}}
    @if(!$isDeleted && $deleteRouteExists)

        <template x-teleport="body">

            <div
                x-show="showDeleteModal"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click.self="showDeleteModal = false"
                class="fixed inset-0 z-[100] flex items-center justify-center
                       overflow-y-auto bg-black/70 p-4 backdrop-blur-sm"
            >

                <div
                    x-show="showDeleteModal"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="w-full max-w-md overflow-hidden rounded-2xl
                           border border-white/10 bg-gray-900
                           shadow-2xl shadow-black/40"
                >

                    {{-- HEADER --}}
                    <div class="border-b border-white/5 px-6 py-5">

                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-11 w-11 flex-shrink-0 items-center
                                       justify-center rounded-xl bg-red-500/10
                                       text-red-400 ring-1 ring-red-500/20"
                            >
                                <i class="fa-solid fa-trash-can text-lg"></i>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-white">
                                    Delete permission assignment
                                </h3>

                                <p class="mt-1 text-xs text-gray-500">
                                    Confirm this operation
                                </p>
                            </div>

                        </div>
                    </div>

                    {{-- BODY --}}
                    <div class="space-y-4 px-6 py-5">

                        <p class="text-sm leading-6 text-gray-400">
                            This action will soft-delete the assignment between
                            <strong class="text-gray-200">{{ $user?->name ?? 'Unknown user' }}</strong>
                            and the permission
                            <strong class="text-gray-200">{{ $permission?->name ?? 'Unknown permission' }}</strong>.
                        </p>

                        <div class="rounded-xl border border-white/5 bg-gray-950/60 p-4">

                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                                Assignment
                            </p>

                            <p class="mt-2 text-sm font-bold text-white">
                                #{{ $permissionUser->id }}
                            </p>

                            @if($permission?->slug)
                                <code class="mt-2 inline-block max-w-full break-all rounded-lg bg-gray-900 px-2.5 py-1.5 text-xs text-gray-300">
                                    {{ $permission->slug }}
                                </code>
                            @endif

                        </div>

                        <div class="flex items-start gap-2.5 rounded-xl border border-amber-500/20 bg-amber-500/5 p-3">

                            <i class="fa-solid fa-triangle-exclamation mt-0.5 text-amber-400"></i>

                            <p class="text-xs leading-5 text-amber-200/80">
                                The record will not be permanently removed, but this
                                assignment will no longer appear in the default active
                                listing. Your authorization rules will be checked by
                                the server before the operation is performed.
                            </p>

                        </div>

                    </div>

                    {{-- ACTIONS --}}
                    <div
                        class="flex flex-col-reverse gap-2 border-t border-white/5
                               bg-gray-950/30 px-6 py-4 sm:flex-row sm:justify-end"
                    >

                        <button
                            type="button"
                            @click="showDeleteModal = false"
                            class="inline-flex items-center justify-center gap-2
                                   rounded-xl border border-white/10 bg-white/5
                                   px-4 py-2.5 text-sm font-semibold text-gray-300
                                   transition hover:bg-white/10 hover:text-white"
                        >
                            <i class="fa-solid fa-xmark"></i>
                            Cancel
                        </button>

                        <form
                            action="{{ route('permission.user.delete', $permissionUser->id) }}"
                            method="POST"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center
                                       gap-2 rounded-xl bg-red-600 px-4 py-2.5
                                       text-sm font-bold text-white transition
                                       hover:bg-red-500 sm:w-auto"
                            >
                                <i class="fa-solid fa-trash-can"></i>
                                Confirm delete
                            </button>
                        </form>

                    </div>

                </div>
            </div>

        </template>

    @endif

    {{-- =========================================================
         RESTORE CONFIRMATION MODAL
    ========================================================== --}}
    @if($isDeleted && $restoreRouteExists)

        <template x-teleport="body">

            <div
                x-show="showRestoreModal"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click.self="showRestoreModal = false"
                class="fixed inset-0 z-[100] flex items-center justify-center
                       overflow-y-auto bg-black/70 p-4 backdrop-blur-sm"
            >

                <div
                    x-show="showRestoreModal"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="w-full max-w-md overflow-hidden rounded-2xl
                           border border-white/10 bg-gray-900
                           shadow-2xl shadow-black/40"
                >

                    {{-- HEADER --}}
                    <div class="border-b border-white/5 px-6 py-5">

                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-11 w-11 flex-shrink-0 items-center
                                       justify-center rounded-xl bg-emerald-500/10
                                       text-emerald-400 ring-1 ring-emerald-500/20"
                            >
                                <i class="fa-solid fa-rotate-left text-lg"></i>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-white">
                                    Restore permission assignment
                                </h3>

                                <p class="mt-1 text-xs text-gray-500">
                                    Confirm restoration
                                </p>
                            </div>

                        </div>
                    </div>

                    {{-- BODY --}}
                    <div class="space-y-4 px-6 py-5">

                        <p class="text-sm leading-6 text-gray-400">
                            You are about to restore the assignment between
                            <strong class="text-gray-200">{{ $user?->name ?? 'Unknown user' }}</strong>
                            and
                            <strong class="text-gray-200">{{ $permission?->name ?? 'Unknown permission' }}</strong>.
                        </p>

                        <div class="flex items-start gap-2.5 rounded-xl border border-amber-500/20 bg-amber-500/5 p-3">

                            <i class="fa-solid fa-triangle-exclamation mt-0.5 text-amber-400"></i>

                            <p class="text-xs leading-5 text-amber-200/80">
                                Restoring this assignment makes it active again.
                                The application's authorization rules will determine
                                whether the permission is effective for the user.
                            </p>

                        </div>

                    </div>

                    {{-- ACTIONS --}}
                    <div
                        class="flex flex-col-reverse gap-2 border-t border-white/5
                               bg-gray-950/30 px-6 py-4 sm:flex-row sm:justify-end"
                    >

                        <button
                            type="button"
                            @click="showRestoreModal = false"
                            class="inline-flex items-center justify-center gap-2
                                   rounded-xl border border-white/10 bg-white/5
                                   px-4 py-2.5 text-sm font-semibold text-gray-300
                                   transition hover:bg-white/10 hover:text-white"
                        >
                            <i class="fa-solid fa-xmark"></i>
                            Cancel
                        </button>

                        <form
                            action="{{ route('permission.user.restore', $permissionUser->id) }}"
                            method="POST"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center
                                       gap-2 rounded-xl bg-emerald-600 px-4 py-2.5
                                       text-sm font-bold text-white transition
                                       hover:bg-emerald-500 sm:w-auto"
                            >
                                <i class="fa-solid fa-rotate-left"></i>
                                Confirm restore
                            </button>
                        </form>

                    </div>

                </div>
            </div>

        </template>

    @endif

    {{-- =========================================================
         FOOTER ACTIONS
    ========================================================== --}}
    <div class="mt-6 flex flex-wrap justify-between gap-3">

        @if($listRouteExists)
            <a
                href="{{ route('permission.user.list') }}"
                class="inline-flex items-center justify-center gap-2
                       rounded-xl border border-white/10 bg-white/5
                       px-4 py-2.5 text-sm font-semibold text-gray-300
                       transition hover:bg-white/10 hover:text-white"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Back to permission assignments
            </a>
        @endif

        <span class="text-xs text-gray-600">
            Assignment #{{ $permissionUser->id }}
        </span>

    </div>

</div>

@endsection