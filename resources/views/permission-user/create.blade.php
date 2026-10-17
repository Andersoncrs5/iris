@extends('layouts.base')

@section('title', 'Assign Permission - Iris App')

@section('content')

@php
    $selectedUser = $users->first(
        fn ($user) => (string) $user->id === (string) old('user_id', '')
    );

    $selectedPermission = $permissions->first(
        fn ($permission) => (string) $permission->id === (string) old('permission_id', '')
    );
@endphp

<div
    class="min-h-full"
    x-data="{
        selectedUserName: @js($selectedUser?->name ?? ''),
        selectedUserEmail: @js($selectedUser?->email ?? ''),

        selectedPermissionName: @js($selectedPermission?->name ?? ''),
        selectedPermissionSlug: @js($selectedPermission?->slug ?? ''),
        selectedPermissionModule: @js($selectedPermission?->module ?? '')
    }"
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
                    Assign Permission
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                    Grant a specific permission to a user and manage access
                    to the resources available in Iris.
                </p>

            </div>

            @if(Route::has('permission.user.list'))
                <a
                    href="{{ route('permission.user.list') }}"
                    class="inline-flex items-center justify-center gap-2
                           rounded-xl border border-white/10 bg-white/5
                           px-4 py-2.5 text-sm font-semibold text-gray-300
                           transition hover:bg-white/10 hover:text-white"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to assignments
                </a>
            @endif

        </div>

    </div>

    {{-- =========================================================
         VALIDATION ERRORS
    ========================================================== --}}
    @if($errors->any())

        <div
            class="mb-6 flex items-start gap-3 rounded-xl
                   border border-red-500/20 bg-red-500/10 p-4"
        >
            <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-400"></i>

            <div>
                <p class="text-sm font-semibold text-red-300">
                    Unable to continue
                </p>

                <ul class="mt-2 space-y-1">
                    @foreach($errors->all() as $error)
                        <li class="text-sm text-red-300/80">
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>

        </div>

    @endif

    {{-- =========================================================
         CONTENT
    ========================================================== --}}
    <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">

        {{-- =====================================================
             MAIN FORM
        ====================================================== --}}
        <main class="min-w-0">

            <form
                action="{{ route('permission.user.create.form') }}"
                method="POST"
                class="overflow-hidden rounded-2xl border border-white/5
                       bg-gray-900/80 shadow-2xl shadow-black/10"
            >

                @csrf

                {{-- FORM HEADER --}}
                <div class="border-b border-white/5 px-6 py-5 sm:px-7">

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-10 w-10 items-center justify-center
                                   rounded-xl bg-indigo-500/10 text-indigo-400"
                        >
                            <i class="fa-solid fa-link"></i>
                        </div>

                        <div>
                            <h2 class="text-sm font-bold text-white">
                                New permission assignment
                            </h2>

                            <p class="mt-0.5 text-xs text-gray-500">
                                Select the account and the permission to grant.
                            </p>
                        </div>

                    </div>

                </div>

                {{-- FORM FIELDS --}}
                <div class="space-y-7 p-6 sm:p-7">

                    {{-- =================================================
                         USER SELECTION
                    ================================================== --}}
                    <section>

                        <div class="mb-4 flex items-start gap-3">

                            <div
                                class="flex h-10 w-10 flex-shrink-0 items-center
                                       justify-center rounded-xl bg-blue-500/10
                                       text-blue-400"
                            >
                                <i class="fa-solid fa-user"></i>
                            </div>

                            <div>
                                <h3 class="text-sm font-bold text-white">
                                    Target user
                                </h3>

                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Choose the account that will receive the permission.
                                </p>
                            </div>

                        </div>

                        <label
                            for="user_id"
                            class="mb-2 block text-xs font-semibold uppercase
                                   tracking-wider text-gray-400"
                        >
                            User <span class="text-red-400">*</span>
                        </label>

                        <div class="relative">

                            <i
                                class="fa-solid fa-magnifying-glass pointer-events-none
                                       absolute left-4 top-1/2 -translate-y-1/2
                                       text-sm text-gray-500"
                            ></i>

                            <select
                                id="user_id"
                                name="user_id"
                                required
                                @change="
                                    const option = $event.target.selectedOptions[0];
                                    selectedUserName = option?.dataset.name || '';
                                    selectedUserEmail = option?.dataset.email || '';
                                "
                                class="w-full appearance-none rounded-xl border
                                       border-white/10 bg-gray-950/60 py-3.5
                                       pl-11 pr-10 text-sm text-gray-200
                                       outline-none transition
                                       focus:border-indigo-500/50
                                       focus:ring-4 focus:ring-indigo-500/10"
                            >

                                <option value="">
                                    Select a user...
                                </option>

                                @foreach($users as $user)

                                    <option
                                        value="{{ $user->id }}"
                                        data-name="{{ $user->name }}"
                                        data-email="{{ $user->email }}"
                                        @selected((string) old('user_id', '') === (string) $user->id)
                                    >
                                        {{ $user->name }} — {{ $user->email }}
                                    </option>

                                @endforeach

                            </select>

                            <i
                                class="fa-solid fa-chevron-down pointer-events-none
                                       absolute right-4 top-1/2 -translate-y-1/2
                                       text-xs text-gray-500"
                            ></i>

                        </div>

                        @error('user_id')
                            <p class="mt-2 text-xs text-red-400">
                                {{ $message }}
                            </p>
                        @enderror

                        @if($users->isEmpty())

                            <p class="mt-3 flex items-center gap-2 text-xs text-amber-400">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                No available users were found.
                            </p>

                        @else

                            <p class="mt-2 text-xs leading-5 text-gray-600">
                                {{ $users->count() }} available
                                {{ $users->count() === 1 ? 'user' : 'users' }}.
                                Deleted accounts are excluded.
                            </p>

                        @endif

                    </section>

                    {{-- SECTION DIVIDER --}}
                    <div class="border-t border-white/5"></div>

                    {{-- =================================================
                         PERMISSION SELECTION
                    ================================================== --}}
                    <section>

                        <div class="mb-4 flex items-start gap-3">

                            <div
                                class="flex h-10 w-10 flex-shrink-0 items-center
                                       justify-center rounded-xl bg-violet-500/10
                                       text-violet-400"
                            >
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>

                            <div>
                                <h3 class="text-sm font-bold text-white">
                                    Permission to grant
                                </h3>

                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Choose the access capability to assign to this user.
                                </p>
                            </div>

                        </div>

                        <label
                            for="permission_id"
                            class="mb-2 block text-xs font-semibold uppercase
                                   tracking-wider text-gray-400"
                        >
                            Permission <span class="text-red-400">*</span>
                        </label>

                        <div class="relative">

                            <i
                                class="fa-solid fa-shield pointer-events-none
                                       absolute left-4 top-1/2 -translate-y-1/2
                                       text-sm text-gray-500"
                            ></i>

                            <select
                                id="permission_id"
                                name="permission_id"
                                required
                                @change="
                                    const option = $event.target.selectedOptions[0];
                                    selectedPermissionName = option?.dataset.name || '';
                                    selectedPermissionSlug = option?.dataset.slug || '';
                                    selectedPermissionModule = option?.dataset.module || '';
                                "
                                class="w-full appearance-none rounded-xl border
                                       border-white/10 bg-gray-950/60 py-3.5
                                       pl-11 pr-10 text-sm text-gray-200
                                       outline-none transition
                                       focus:border-indigo-500/50
                                       focus:ring-4 focus:ring-indigo-500/10"
                            >

                                <option value="">
                                    Select a permission...
                                </option>

                                @foreach($permissions->groupBy('module') as $module => $modulePermissions)

                                    <optgroup label="{{ ucfirst($module ?: 'system') }}">

                                        @foreach($modulePermissions as $permission)

                                            <option
                                                value="{{ $permission->id }}"
                                                data-name="{{ $permission->name }}"
                                                data-slug="{{ $permission->slug }}"
                                                data-module="{{ $permission->module }}"
                                                @selected((string) old('permission_id', '') === (string) $permission->id)
                                            >
                                                {{ $permission->name }} — {{ $permission->slug }}
                                            </option>

                                        @endforeach

                                    </optgroup>

                                @endforeach

                            </select>

                            <i
                                class="fa-solid fa-chevron-down pointer-events-none
                                       absolute right-4 top-1/2 -translate-y-1/2
                                       text-xs text-gray-500"
                            ></i>

                        </div>

                        @error('permission_id')
                            <p class="mt-2 text-xs text-red-400">
                                {{ $message }}
                            </p>
                        @enderror

                        @if($permissions->isEmpty())

                            <p class="mt-3 flex items-center gap-2 text-xs text-amber-400">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                No available permissions were found.
                            </p>

                        @else

                            <p class="mt-2 text-xs leading-5 text-gray-600">
                                {{ $permissions->count() }} available
                                {{ $permissions->count() === 1 ? 'permission' : 'permissions' }},
                                grouped by module.
                            </p>

                        @endif

                    </section>

                </div>

                {{-- FORM FOOTER --}}
                <div
                    class="flex flex-col-reverse gap-3 border-t border-white/5
                           bg-gray-950/30 px-6 py-5 sm:flex-row
                           sm:items-center sm:justify-between sm:px-7"
                >

                    <p class="text-xs leading-5 text-gray-600">
                        <i class="fa-solid fa-lock mr-1"></i>
                        Only the selected assignment will be submitted.
                    </p>

                    <div class="flex flex-col-reverse gap-2 sm:flex-row">

                        <a
                            href="{{ route('permission.user.list') }}"
                            class="inline-flex items-center justify-center gap-2
                                   rounded-xl border border-white/10 bg-white/5
                                   px-4 py-3 text-sm font-semibold text-gray-300
                                   transition hover:bg-white/10 hover:text-white"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            @disabled($users->isEmpty() || $permissions->isEmpty())
                            class="inline-flex items-center justify-center gap-2
                                   rounded-xl bg-indigo-600 px-5 py-3
                                   text-sm font-bold text-white
                                   shadow-lg shadow-indigo-600/20
                                   transition hover:bg-indigo-500
                                   disabled:cursor-not-allowed
                                   disabled:opacity-40"
                        >
                            <i class="fa-solid fa-link"></i>
                            Grant permission
                        </button>

                    </div>

                </div>

            </form>

        </main>

        {{-- =====================================================
             LIVE PREVIEW / SIDEBAR
        ====================================================== --}}
        <aside class="space-y-5">

            {{-- PREVIEW CARD --}}
            <section
                class="overflow-hidden rounded-2xl border border-white/5
                       bg-gray-900/80 shadow-xl shadow-black/5"
            >

                <div class="border-b border-white/5 px-5 py-4">

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-9 w-9 items-center justify-center
                                   rounded-xl bg-white/5 text-gray-300"
                        >
                            <i class="fa-solid fa-eye"></i>
                        </div>

                        <div>
                            <h2 class="text-sm font-bold text-white">
                                Assignment preview
                            </h2>

                            <p class="mt-0.5 text-xs text-gray-500">
                                Review your selection
                            </p>
                        </div>

                    </div>

                </div>

                <div class="space-y-5 p-5">

                    {{-- SELECTED USER --}}
                    <div>

                        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-gray-500">
                            Target user
                        </p>

                        <div class="mt-3 flex items-start gap-3">

                            <div
                                class="flex h-10 w-10 flex-shrink-0 items-center
                                       justify-center rounded-xl bg-blue-500/10
                                       text-blue-400"
                            >
                                <i class="fa-solid fa-user"></i>
                            </div>

                            <div class="min-w-0 flex-1">

                                <p
                                    class="break-words text-sm font-semibold text-white"
                                    x-text="selectedUserName || 'No user selected'"
                                ></p>

                                <p
                                    class="mt-1 break-all text-xs text-gray-500"
                                    x-text="selectedUserEmail || 'User email will appear here'"
                                ></p>

                            </div>

                        </div>

                    </div>

                    <div class="flex items-center gap-3">

                        <span class="h-px flex-1 bg-white/5"></span>

                        <span
                            class="flex h-8 w-8 items-center justify-center
                                   rounded-full border border-white/10
                                   bg-gray-950 text-gray-500"
                        >
                            <i class="fa-solid fa-arrow-down text-xs"></i>
                        </span>

                        <span class="h-px flex-1 bg-white/5"></span>

                    </div>

                    {{-- SELECTED PERMISSION --}}
                    <div>

                        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-gray-500">
                            Permission
                        </p>

                        <div class="mt-3 flex items-start gap-3">

                            <div
                                class="flex h-10 w-10 flex-shrink-0 items-center
                                       justify-center rounded-xl bg-violet-500/10
                                       text-violet-400"
                            >
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>

                            <div class="min-w-0 flex-1">

                                <p
                                    class="break-words text-sm font-semibold text-white"
                                    x-text="selectedPermissionName || 'No permission selected'"
                                ></p>

                                <code
                                    x-show="selectedPermissionSlug"
                                    x-cloak
                                    class="mt-2 inline-block max-w-full break-all
                                           rounded-lg border border-white/5
                                           bg-gray-950/70 px-2 py-1
                                           text-[11px] text-indigo-300"
                                    x-text="selectedPermissionSlug"
                                ></code>

                                <span
                                    x-show="selectedPermissionModule"
                                    x-cloak
                                    class="mt-2 inline-flex items-center gap-1.5
                                           rounded-full border border-violet-500/20
                                           bg-violet-500/10 px-2 py-1
                                           text-[10px] font-bold uppercase
                                           tracking-wider text-violet-300"
                                >
                                    <i class="fa-solid fa-cubes-stacked"></i>
                                    <span x-text="selectedPermissionModule"></span>
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </section>

            {{-- INFORMATION CARD --}}
            <section class="rounded-2xl border border-white/5 bg-gray-900/60 p-5">

                <div class="flex items-center gap-2">

                    <i class="fa-solid fa-circle-info text-indigo-400"></i>

                    <h2 class="text-sm font-bold text-white">
                        Before you continue
                    </h2>

                </div>

                <div class="mt-4 space-y-4">

                    <div class="flex items-start gap-3">

                        <span
                            class="flex h-7 w-7 flex-shrink-0 items-center
                                   justify-center rounded-lg bg-white/5
                                   text-xs text-gray-400"
                        >
                            <i class="fa-solid fa-user-check"></i>
                        </span>

                        <p class="text-xs leading-5 text-gray-400">
                            Confirm that you selected the correct user account.
                        </p>

                    </div>

                    <div class="flex items-start gap-3">

                        <span
                            class="flex h-7 w-7 flex-shrink-0 items-center
                                   justify-center rounded-lg bg-white/5
                                   text-xs text-gray-400"
                        >
                            <i class="fa-solid fa-shield"></i>
                        </span>

                        <p class="text-xs leading-5 text-gray-400">
                            Verify that the selected permission grants the
                            intended level of access.
                        </p>

                    </div>

                    <div class="flex items-start gap-3">

                        <span
                            class="flex h-7 w-7 flex-shrink-0 items-center
                                   justify-center rounded-lg bg-white/5
                                   text-xs text-gray-400"
                        >
                            <i class="fa-solid fa-lock"></i>
                        </span>

                        <p class="text-xs leading-5 text-gray-400">
                            The server must validate the selection and verify
                            that the assignment is allowed.
                        </p>

                    </div>

                </div>

            </section>

        </aside>

    </div>

</div>

@endsection