@extends('layouts.base')

@section('title', 'Permission Users - Iris App')

@section('content')

@php
    $sortBy = request('sort_by', 'created_at');
    $sortOrder = request('sort_order', 'desc');

    $activeFilterCount = collect([
        request('id'),
        request('user_id'),
        request('user_name'),
        request('user_name_ignore_case'),
        request('user_email'),
        request('user_email_ignore_case'),
        request('permission_id'),
        request('permission_name'),
        request('permission_name_ignore_case'),
        request('permission_slug'),
        request('permission_slug_ignore_case'),
        request('permission_module'),
        request('version_min'),
        request('version_max'),
        request('created_at_from'),
        request('created_at_to'),
        request('updated_at_from'),
        request('updated_at_to'),
        request('trashed'),
    ])->filter(fn ($value) => filled($value))->count();
@endphp

<div class="min-h-full">

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
                    Permission Users
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                    Manage and inspect permission assignments across users.
                    Search by user, permission, module, version and dates.
                </p>
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

        {{-- SUMMARY --}}
        <div class="mt-7 grid grid-cols-1 gap-3 sm:grid-cols-3">

            {{-- TOTAL RESULTS --}}
            <div class="rounded-2xl border border-white/5 bg-gray-900/70 px-5 py-4 shadow-xl shadow-black/5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            Results
                        </p>

                        <p class="mt-1 text-2xl font-black text-white">
                            {{ $permissionUsers->total() }}
                        </p>

                        <p class="mt-1 text-xs text-gray-600">
                            Matching assignments
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>

            {{-- CURRENT PAGE --}}
            <div class="rounded-2xl border border-white/5 bg-gray-900/70 px-5 py-4 shadow-xl shadow-black/5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            On this page
                        </p>

                        <p class="mt-1 text-2xl font-black text-white">
                            {{ $permissionUsers->count() }}
                        </p>

                        <p class="mt-1 text-xs text-gray-600">
                            Currently displayed
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                </div>
            </div>

            {{-- ACTIVE FILTERS --}}
            <div class="rounded-2xl border border-white/5 bg-gray-900/70 px-5 py-4 shadow-xl shadow-black/5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            Active filters
                        </p>

                        <p class="mt-1 text-2xl font-black text-white">
                            {{ $activeFilterCount }}
                        </p>

                        <p class="mt-1 text-xs text-gray-600">
                            Applied to this query
                        </p>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                        <i class="fa-solid fa-filter"></i>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- =========================================================
         MAIN CONTENT
    ========================================================== --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[300px_minmax(0,1fr)]">

        {{-- =====================================================
             FILTER SIDEBAR
        ====================================================== --}}
        <aside class="h-fit xl:sticky xl:top-6">

            <details
                open
                class="group overflow-hidden rounded-2xl border border-white/5
                       bg-gray-900/80 shadow-2xl shadow-black/10"
            >
                <summary
                    class="flex cursor-pointer list-none items-center justify-between
                           px-5 py-4 [&::-webkit-details-marker]:hidden"
                >
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/5 text-gray-300">
                            <i class="fa-solid fa-sliders"></i>
                        </div>

                        <div>
                            <h2 class="text-sm font-bold text-white">Filters</h2>
                            <p class="text-xs text-gray-500">Refine your results</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($activeFilterCount > 0)
                            <span
                                class="inline-flex h-6 min-w-6 items-center justify-center
                                       rounded-full bg-indigo-500/15 px-2
                                       text-[11px] font-bold text-indigo-400"
                            >
                                {{ $activeFilterCount }}
                            </span>
                        @endif

                        <i
                            class="fa-solid fa-chevron-down text-xs text-gray-500
                                   transition-transform duration-200 group-open:rotate-180"
                        ></i>
                    </div>
                </summary>

                <div class="border-t border-white/5 p-5">

                    <form
                        method="GET"
                        action="{{ route('permission.user.list') }}"
                        class="space-y-5"
                    >

                        {{-- ASSIGNMENT ID --}}
                        <div>
                            <label
                                for="filter_id"
                                class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400"
                            >
                                Assignment ID
                            </label>

                            <input
                                type="number"
                                id="filter_id"
                                name="id"
                                value="{{ request('id') }}"
                                min="1"
                                placeholder="Assignment ID"
                                class="w-full rounded-xl border border-white/10 bg-gray-950/60
                                       px-3 py-2.5 text-sm text-white placeholder:text-gray-600
                                       outline-none transition focus:border-indigo-500/50
                                       focus:ring-4 focus:ring-indigo-500/10"
                            />
                        </div>

                        {{-- USER ID --}}
                        <div>
                            <label
                                for="filter_user_id"
                                class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400"
                            >
                                User ID
                            </label>

                            <input
                                type="number"
                                id="filter_user_id"
                                name="user_id"
                                value="{{ request('user_id') }}"
                                min="1"
                                placeholder="User ID"
                                class="w-full rounded-xl border border-white/10 bg-gray-950/60
                                       px-3 py-2.5 text-sm text-white placeholder:text-gray-600
                                       outline-none transition focus:border-indigo-500/50
                                       focus:ring-4 focus:ring-indigo-500/10"
                            />
                        </div>

                        {{-- USER NAME --}}
                        <div>
                            <label
                                for="filter_user_name"
                                class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400"
                            >
                                User name
                            </label>

                            <input
                                type="text"
                                id="filter_user_name"
                                name="user_name"
                                value="{{ request('user_name') }}"
                                maxlength="255"
                                placeholder="Search user name..."
                                class="w-full rounded-xl border border-white/10 bg-gray-950/60
                                       px-3 py-2.5 text-sm text-white placeholder:text-gray-600
                                       outline-none transition focus:border-indigo-500/50
                                       focus:ring-4 focus:ring-indigo-500/10"
                            />

                            <label class="mt-2.5 inline-flex cursor-pointer items-center gap-2 text-xs text-gray-500">
                                <input
                                    type="checkbox"
                                    name="user_name_ignore_case"
                                    value="1"
                                    @checked(request()->boolean('user_name_ignore_case'))
                                    class="h-3.5 w-3.5 rounded border-gray-600 bg-gray-800
                                           text-indigo-600 focus:ring-indigo-500/20"
                                />

                                <span>Ignore case</span>
                            </label>
                        </div>

                        {{-- USER EMAIL --}}
                        <div>
                            <label
                                for="filter_user_email"
                                class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400"
                            >
                                User email
                            </label>

                            <input
                                type="text"
                                id="filter_user_email"
                                name="user_email"
                                value="{{ request('user_email') }}"
                                maxlength="255"
                                placeholder="Search user email..."
                                class="w-full rounded-xl border border-white/10 bg-gray-950/60
                                       px-3 py-2.5 text-sm text-white placeholder:text-gray-600
                                       outline-none transition focus:border-indigo-500/50
                                       focus:ring-4 focus:ring-indigo-500/10"
                            />

                            <label class="mt-2.5 inline-flex cursor-pointer items-center gap-2 text-xs text-gray-500">
                                <input
                                    type="checkbox"
                                    name="user_email_ignore_case"
                                    value="1"
                                    @checked(request()->boolean('user_email_ignore_case'))
                                    class="h-3.5 w-3.5 rounded border-gray-600 bg-gray-800
                                           text-indigo-600 focus:ring-indigo-500/20"
                                />

                                <span>Ignore case</span>
                            </label>
                        </div>

                        {{-- PERMISSION ID --}}
                        <div>
                            <label
                                for="filter_permission_id"
                                class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400"
                            >
                                Permission ID
                            </label>

                            <input
                                type="number"
                                id="filter_permission_id"
                                name="permission_id"
                                value="{{ request('permission_id') }}"
                                min="1"
                                placeholder="Permission ID"
                                class="w-full rounded-xl border border-white/10 bg-gray-950/60
                                       px-3 py-2.5 text-sm text-white placeholder:text-gray-600
                                       outline-none transition focus:border-indigo-500/50
                                       focus:ring-4 focus:ring-indigo-500/10"
                            />
                        </div>

                        {{-- PERMISSION NAME --}}
                        <div>
                            <label
                                for="filter_permission_name"
                                class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400"
                            >
                                Permission name
                            </label>

                            <input
                                type="text"
                                id="filter_permission_name"
                                name="permission_name"
                                value="{{ request('permission_name') }}"
                                maxlength="255"
                                placeholder="Search permission..."
                                class="w-full rounded-xl border border-white/10 bg-gray-950/60
                                       px-3 py-2.5 text-sm text-white placeholder:text-gray-600
                                       outline-none transition focus:border-indigo-500/50
                                       focus:ring-4 focus:ring-indigo-500/10"
                            />

                            <label class="mt-2.5 inline-flex cursor-pointer items-center gap-2 text-xs text-gray-500">
                                <input
                                    type="checkbox"
                                    name="permission_name_ignore_case"
                                    value="1"
                                    @checked(request()->boolean('permission_name_ignore_case'))
                                    class="h-3.5 w-3.5 rounded border-gray-600 bg-gray-800
                                           text-indigo-600 focus:ring-indigo-500/20"
                                />

                                <span>Ignore case</span>
                            </label>
                        </div>

                        {{-- PERMISSION SLUG --}}
                        <div>
                            <label
                                for="filter_permission_slug"
                                class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400"
                            >
                                Permission slug
                            </label>

                            <input
                                type="text"
                                id="filter_permission_slug"
                                name="permission_slug"
                                value="{{ request('permission_slug') }}"
                                maxlength="255"
                                placeholder="e.g. ticket.create"
                                class="w-full rounded-xl border border-white/10 bg-gray-950/60
                                       px-3 py-2.5 text-sm font-mono text-white
                                       placeholder:text-gray-600 outline-none transition
                                       focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                            />

                            <label class="mt-2.5 inline-flex cursor-pointer items-center gap-2 text-xs text-gray-500">
                                <input
                                    type="checkbox"
                                    name="permission_slug_ignore_case"
                                    value="1"
                                    @checked(request()->boolean('permission_slug_ignore_case'))
                                    class="h-3.5 w-3.5 rounded border-gray-600 bg-gray-800
                                           text-indigo-600 focus:ring-indigo-500/20"
                                />

                                <span>Ignore case</span>
                            </label>
                        </div>

                        {{-- PERMISSION MODULE --}}
                        <div>
                            <label
                                for="filter_permission_module"
                                class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400"
                            >
                                Permission module
                            </label>

                            <input
                                type="text"
                                id="filter_permission_module"
                                name="permission_module"
                                value="{{ request('permission_module') }}"
                                maxlength="255"
                                placeholder="e.g. tickets"
                                class="w-full rounded-xl border border-white/10 bg-gray-950/60
                                       px-3 py-2.5 text-sm text-white placeholder:text-gray-600
                                       outline-none transition focus:border-indigo-500/50
                                       focus:ring-4 focus:ring-indigo-500/10"
                            />

                            <p class="mt-1.5 text-[11px] text-gray-600">
                                Exact module name.
                            </p>
                        </div>

                        {{-- VERSION RANGE --}}
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">
                                Version range
                            </label>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label for="filter_version_min" class="mb-1 block text-[11px] text-gray-500">
                                        Minimum
                                    </label>

                                    <input
                                        type="number"
                                        id="filter_version_min"
                                        name="version_min"
                                        value="{{ request('version_min') }}"
                                        min="1"
                                        placeholder="Min."
                                        class="w-full min-w-0 rounded-xl border border-white/10
                                               bg-gray-950/60 px-3 py-2.5 text-sm text-white
                                               placeholder:text-gray-600 outline-none transition
                                               focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                                    />
                                </div>

                                <div>
                                    <label for="filter_version_max" class="mb-1 block text-[11px] text-gray-500">
                                        Maximum
                                    </label>

                                    <input
                                        type="number"
                                        id="filter_version_max"
                                        name="version_max"
                                        value="{{ request('version_max') }}"
                                        min="1"
                                        placeholder="Max."
                                        class="w-full min-w-0 rounded-xl border border-white/10
                                               bg-gray-950/60 px-3 py-2.5 text-sm text-white
                                               placeholder:text-gray-600 outline-none transition
                                               focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                                    />
                                </div>
                            </div>
                        </div>

                        {{-- CREATED DATE RANGE --}}
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">
                                Created date
                            </label>

                            <div class="space-y-2">
                                <div>
                                    <label for="filter_created_at_from" class="mb-1 block text-[11px] text-gray-500">
                                        From
                                    </label>

                                    <input
                                        type="date"
                                        id="filter_created_at_from"
                                        name="created_at_from"
                                        value="{{ request('created_at_from') }}"
                                        class="w-full rounded-xl border border-white/10 bg-gray-950/60
                                               px-3 py-2.5 text-sm text-gray-200 outline-none transition
                                               focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                                    />
                                </div>

                                <div>
                                    <label for="filter_created_at_to" class="mb-1 block text-[11px] text-gray-500">
                                        To
                                    </label>

                                    <input
                                        type="date"
                                        id="filter_created_at_to"
                                        name="created_at_to"
                                        value="{{ request('created_at_to') }}"
                                        class="w-full rounded-xl border border-white/10 bg-gray-950/60
                                               px-3 py-2.5 text-sm text-gray-200 outline-none transition
                                               focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                                    />
                                </div>
                            </div>
                        </div>

                        {{-- UPDATED DATE RANGE --}}
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">
                                Updated date
                            </label>

                            <div class="space-y-2">
                                <div>
                                    <label for="filter_updated_at_from" class="mb-1 block text-[11px] text-gray-500">
                                        From
                                    </label>

                                    <input
                                        type="date"
                                        id="filter_updated_at_from"
                                        name="updated_at_from"
                                        value="{{ request('updated_at_from') }}"
                                        class="w-full rounded-xl border border-white/10 bg-gray-950/60
                                               px-3 py-2.5 text-sm text-gray-200 outline-none transition
                                               focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                                    />
                                </div>

                                <div>
                                    <label for="filter_updated_at_to" class="mb-1 block text-[11px] text-gray-500">
                                        To
                                    </label>

                                    <input
                                        type="date"
                                        id="filter_updated_at_to"
                                        name="updated_at_to"
                                        value="{{ request('updated_at_to') }}"
                                        class="w-full rounded-xl border border-white/10 bg-gray-950/60
                                               px-3 py-2.5 text-sm text-gray-200 outline-none transition
                                               focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                                    />
                                </div>
                            </div>
                        </div>

                        {{-- SOFT DELETES --}}
                        <div>
                            <label
                                for="filter_trashed"
                                class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400"
                            >
                                Archived assignments
                            </label>

                            <select
                                id="filter_trashed"
                                name="trashed"
                                class="w-full rounded-xl border border-white/10 bg-gray-950/60
                                       px-3 py-2.5 text-sm text-gray-200 outline-none transition
                                       focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                            >
                                <option value="without" @selected(request('trashed', 'without') === 'without')>
                                    Without archived
                                </option>

                                <option value="with" @selected(request('trashed') === 'with')>
                                    Include archived
                                </option>

                                <option value="only" @selected(request('trashed') === 'only')>
                                    Archived only
                                </option>
                            </select>
                        </div>

                        {{-- SORTING --}}
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">
                                Sorting
                            </label>

                            <div class="grid grid-cols-[1fr_auto] gap-2">
                                <select
                                    name="sort_by"
                                    class="min-w-0 rounded-xl border border-white/10 bg-gray-950/60
                                           px-3 py-2.5 text-sm text-gray-200 outline-none transition
                                           focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                                >
                                    <option value="created_at" @selected($sortBy === 'created_at')>
                                        Created date
                                    </option>

                                    <option value="updated_at" @selected($sortBy === 'updated_at')>
                                        Updated date
                                    </option>

                                    <option value="id" @selected($sortBy === 'id')>
                                        Assignment ID
                                    </option>

                                    <option value="user_id" @selected($sortBy === 'user_id')>
                                        User ID
                                    </option>

                                    <option value="permission_id" @selected($sortBy === 'permission_id')>
                                        Permission ID
                                    </option>

                                    <option value="version" @selected($sortBy === 'version')>
                                        Version
                                    </option>

                                    <option value="deleted_at" @selected($sortBy === 'deleted_at')>
                                        Archived date
                                    </option>
                                </select>

                                <select
                                    name="sort_order"
                                    aria-label="Sort direction"
                                    class="rounded-xl border border-white/10 bg-gray-950/60
                                           px-3 py-2.5 text-sm text-gray-200 outline-none transition
                                           focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                                >
                                    <option value="asc" @selected($sortOrder === 'asc')>↑</option>
                                    <option value="desc" @selected($sortOrder === 'desc')>↓</option>
                                </select>
                            </div>
                        </div>

                        {{-- PAGE SIZE --}}
                        <div>
                            <label
                                for="filter_per_page"
                                class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400"
                            >
                                Results per page
                            </label>

                            <select
                                id="filter_per_page"
                                name="per_page"
                                class="w-full rounded-xl border border-white/10 bg-gray-950/60
                                       px-3 py-2.5 text-sm text-gray-200 outline-none transition
                                       focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                            >
                                @foreach([15, 25, 50, 100] as $size)
                                    <option value="{{ $size }}" @selected((int) request('per_page', 15) === $size)>
                                        {{ $size }} per page
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- ACTIONS --}}
                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl
                                       bg-indigo-600 px-3 py-2.5 text-sm font-bold text-white
                                       transition hover:bg-indigo-500"
                            >
                                <i class="fa-solid fa-filter"></i>
                                Apply
                            </button>

                            <a
                                href="{{ route('permission.user.list') }}"
                                class="inline-flex items-center justify-center gap-2 rounded-xl
                                       border border-white/10 bg-white/5 px-3 py-2.5
                                       text-sm font-semibold text-gray-300 transition
                                       hover:bg-white/10 hover:text-white"
                            >
                                <i class="fa-solid fa-rotate-left"></i>
                                Reset
                            </a>
                        </div>

                    </form>
                </div>
            </details>
        </aside>

        {{-- =====================================================
             RESULTS
        ====================================================== --}}
        <main class="min-w-0">

            {{-- RESULTS TOOLBAR --}}
            <div
                class="mb-4 flex flex-col gap-3 rounded-2xl border border-white/5
                       bg-gray-900/70 p-4 shadow-xl shadow-black/5
                       sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm font-bold text-white">
                            Permission assignments
                        </span>

                        @if($permissionUsers->total() > 0)
                            <span class="rounded-full bg-white/5 px-2 py-0.5 text-[11px] font-semibold text-gray-400">
                                {{ $permissionUsers->total() }} total
                            </span>
                        @endif
                    </div>

                    <p class="mt-1 text-xs text-gray-500">
                        Review users and the permissions assigned to them.
                    </p>
                </div>

                @if($permissionUsers->total() > 0)
                    <div class="text-xs text-gray-500">
                        Showing
                        <span class="font-semibold text-gray-300">
                            {{ $permissionUsers->firstItem() }}–{{ $permissionUsers->lastItem() }}
                        </span>
                        of
                        <span class="font-semibold text-gray-300">
                            {{ $permissionUsers->total() }}
                        </span>
                    </div>
                @endif
            </div>

            {{-- EMPTY STATE --}}
            @if($permissionUsers->isEmpty())

                <div class="rounded-2xl border border-dashed border-white/10 bg-gray-900/50 px-6 py-16 text-center">

                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center
                               rounded-2xl bg-indigo-500/10 text-2xl text-indigo-400"
                    >
                        <i class="fa-solid fa-user-shield"></i>
                    </div>

                    <h3 class="mt-5 text-lg font-bold text-white">
                        No permission assignments found
                    </h3>

                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-gray-500">
                        No assignments match your current filters.
                        Try changing your search criteria.
                    </p>

                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <a
                            href="{{ route('permission.user.list') }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-white/10
                                   bg-white/5 px-4 py-2.5 text-sm font-semibold text-gray-300
                                   transition hover:bg-white/10 hover:text-white"
                        >
                            <i class="fa-solid fa-rotate-left"></i>
                            Clear filters
                        </a>
                    </div>
                </div>

            @else

                {{-- PERMISSION USER CARDS --}}
                <div class="space-y-3">

                    @foreach($permissionUsers as $permissionUser)

                        {{-- CARD ACTIONS STATE --}}
                        <div
                            x-data="{
                                open: false,
                                showDeleteModal: false
                            }"
                            @keydown.escape.window="open = false; showDeleteModal = false"
                            class="relative"
                            :class="(open || showDeleteModal) ? 'z-40' : 'z-0'"
                        >

                            <article
                                class="group relative overflow-hidden rounded-2xl border
                                       border-white/5 bg-gray-900/80 p-5
                                       shadow-xl shadow-black/5 transition-colors
                                       duration-200 hover:border-white/10 hover:bg-gray-900"
                            >

                                {{-- ACCENT --}}
                                <div
                                    class="absolute inset-y-0 left-0 w-1 rounded-l-2xl
                                           {{ $permissionUser->trashed()
                                               ? 'bg-red-500/70'
                                               : 'bg-indigo-500/70' }}"
                                ></div>

                                <div class="pl-1">

                                    {{-- HEADER --}}
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                                        <div class="flex min-w-0 items-start gap-4">

                                            {{-- USER ICON --}}
                                            <div
                                                class="flex h-12 w-12 flex-shrink-0 items-center
                                                       justify-center rounded-xl border border-white/5
                                                       bg-gray-950/60 text-indigo-400"
                                            >
                                                <i class="fa-solid fa-user text-lg"></i>
                                            </div>

                                            <div class="min-w-0 flex-1">

                                                {{-- USER NAME --}}
                                                <div class="flex flex-wrap items-center gap-2">

                                                    <h3 class="break-words text-base font-bold text-white">
                                                        {{ $permissionUser->user?->name ?? 'Unknown user' }}
                                                    </h3>

                                                    @if($permissionUser->trashed())
                                                        <span
                                                            class="inline-flex items-center gap-1.5 rounded-full
                                                                   border border-red-500/20 bg-red-500/10
                                                                   px-2.5 py-1 text-[10px] font-bold
                                                                   uppercase tracking-wider text-red-300"
                                                        >
                                                            <i class="fa-solid fa-box-archive"></i>
                                                            Archived
                                                        </span>
                                                    @else
                                                        <span
                                                            class="inline-flex items-center gap-1.5 rounded-full
                                                                   border border-emerald-500/20 bg-emerald-500/10
                                                                   px-2.5 py-1 text-[10px] font-bold
                                                                   uppercase tracking-wider text-emerald-300"
                                                        >
                                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                                            Active
                                                        </span>
                                                    @endif

                                                </div>

                                                {{-- USER EMAIL + ID --}}
                                                <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">

                                                    @if($permissionUser->user?->email)
                                                        <span class="inline-flex items-center gap-1.5 break-all">
                                                            <i class="fa-solid fa-envelope text-[10px] text-gray-600"></i>
                                                            {{ $permissionUser->user->email }}
                                                        </span>
                                                    @endif

                                                    <span>
                                                        User ID:
                                                        <span class="font-semibold text-gray-300">
                                                            {{ $permissionUser->user_id }}
                                                        </span>
                                                    </span>

                                                </div>
                                            </div>
                                        </div>

                                        {{-- ASSIGNMENT ID + ACTION MENU --}}
                                        <div class="flex flex-shrink-0 items-center gap-2 sm:pl-4">

                                            <span class="text-[11px] font-medium uppercase tracking-wider text-gray-600">
                                                Assignment
                                            </span>

                                            <code
                                                class="rounded-lg border border-white/5 bg-gray-950/70
                                                       px-2.5 py-1.5 text-xs font-semibold text-gray-300"
                                            >
                                                #{{ $permissionUser->id }}
                                            </code>

                                            {{-- THREE DOTS MENU --}}
                                            <div class="relative ml-1" @click.outside="open = false">

                                                <button
                                                    type="button"
                                                    @click="open = !open"
                                                    :aria-expanded="open.toString()"
                                                    aria-haspopup="true"
                                                    aria-label="Actions for permission assignment {{ $permissionUser->id }}"
                                                    class="flex h-9 w-9 items-center justify-center rounded-xl
                                                           border border-white/5 bg-white/[0.02]
                                                           text-gray-400 transition
                                                           hover:border-white/10 hover:bg-white/5 hover:text-white"
                                                >
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </button>

                                                {{-- DROPDOWN --}}
                                                <div
                                                    x-show="open"
                                                    x-cloak
                                                    x-transition:enter="transition ease-out duration-150"
                                                    x-transition:enter-start="opacity-0 scale-95"
                                                    x-transition:enter-end="opacity-100 scale-100"
                                                    x-transition:leave="transition ease-in duration-100"
                                                    x-transition:leave-start="opacity-100 scale-100"
                                                    x-transition:leave-end="opacity-0 scale-95"
                                                    class="absolute right-0 top-11 z-50 w-52 origin-top-right
                                                           overflow-hidden rounded-xl border border-white/10
                                                           bg-gray-900 p-1 shadow-2xl shadow-black/40"
                                                >

                                                    {{-- VIEW DETAILS --}}
                                                    @if(Route::has('permission.user.view-details'))

                                                        <a
                                                            href="{{ route('permission.user.view-details', $permissionUser->id) }}"
                                                            class="flex items-center gap-3 rounded-lg px-3 py-2.5
                                                                   text-sm text-gray-300 transition
                                                                   hover:bg-white/5 hover:text-white"
                                                        >
                                                            <span
                                                                class="flex h-7 w-7 items-center justify-center
                                                                       rounded-lg bg-indigo-500/10 text-indigo-400"
                                                            >
                                                                <i class="fa-solid fa-eye text-xs"></i>
                                                            </span>

                                                            View details
                                                        </a>

                                                    @endif

                                                    {{-- DELETE --}}
                                                    @if(!$permissionUser->trashed() && Route::has('permission.user.delete'))

                                                        <div class="my-1 border-t border-white/5"></div>

                                                        <button
                                                            type="button"
                                                            @click="showDeleteModal = true; open = false"
                                                            class="flex w-full items-center gap-3 rounded-lg
                                                                   px-3 py-2.5 text-left text-sm text-red-400
                                                                   transition hover:bg-red-500/10 hover:text-red-300"
                                                        >
                                                            <span
                                                                class="flex h-7 w-7 items-center justify-center
                                                                       rounded-lg bg-red-500/10 text-red-400"
                                                            >
                                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                                            </span>

                                                            Delete
                                                        </button>

                                                    @endif

                                                    {{-- NO ACTIONS --}}
                                                    @if(
                                                        !Route::has('permission.user.view-details')
                                                        && ($permissionUser->trashed() || !Route::has('permission.user.delete'))
                                                    )
                                                        <span class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-500">
                                                            <i class="fa-solid fa-hourglass-half text-xs"></i>
                                                            No actions available
                                                        </span>
                                                    @endif

                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    {{-- PERMISSION --}}
                                    <div class="mt-5 rounded-xl border border-white/5 bg-gray-950/40 p-4">

                                        <div class="flex items-start gap-3">

                                            <div
                                                class="flex h-9 w-9 flex-shrink-0 items-center
                                                       justify-center rounded-lg bg-indigo-500/10
                                                       text-indigo-400"
                                            >
                                                <i class="fa-solid fa-shield-halved"></i>
                                            </div>

                                            <div class="min-w-0 flex-1">

                                                <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-gray-500">
                                                    Assigned permission
                                                </p>

                                                <h4 class="mt-1 break-words text-sm font-bold text-white">
                                                    {{ $permissionUser->permission?->name ?? 'Unknown permission' }}
                                                </h4>

                                                @if($permissionUser->permission?->slug)
                                                    <div class="mt-2">
                                                        <code class="inline-block max-w-full break-all rounded-lg border border-white/5 bg-gray-900 px-2.5 py-1.5 text-xs text-gray-300">
                                                            {{ $permissionUser->permission->slug }}
                                                        </code>
                                                    </div>
                                                @endif

                                                <div class="mt-3 flex flex-wrap items-center gap-2">

                                                    @if($permissionUser->permission?->module)
                                                        <span
                                                            class="inline-flex items-center gap-1.5 rounded-full
                                                                   border border-indigo-500/20 bg-indigo-500/10
                                                                   px-2.5 py-1 text-[10px] font-bold
                                                                   uppercase tracking-wider text-indigo-300"
                                                        >
                                                            <i class="fa-solid fa-cubes-stacked"></i>
                                                            {{ $permissionUser->permission->module }}
                                                        </span>
                                                    @endif

                                                    <span class="text-[11px] text-gray-600">
                                                        Permission ID:
                                                        {{ $permissionUser->permission_id }}
                                                    </span>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- METADATA --}}
                                    <div
                                        class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2
                                               border-t border-white/5 pt-3 text-xs text-gray-500"
                                    >

                                        <span class="inline-flex items-center gap-1.5">
                                            <i class="fa-solid fa-code-branch text-[10px] text-gray-600"></i>
                                            Version
                                            <span class="font-semibold text-gray-300">
                                                {{ $permissionUser->version }}
                                            </span>
                                        </span>

                                        @if($permissionUser->created_at)
                                            <span class="hidden h-3 w-px bg-white/10 sm:block"></span>

                                            <span class="inline-flex items-center gap-1.5">
                                                <i class="fa-regular fa-calendar text-[10px] text-gray-600"></i>
                                                Created
                                                <span class="font-semibold text-gray-300">
                                                    {{ $permissionUser->created_at->format('d M Y H:i') }}
                                                </span>
                                            </span>
                                        @endif

                                        @if($permissionUser->updated_at && $permissionUser->updated_at != $permissionUser->created_at)
                                            <span class="hidden h-3 w-px bg-white/10 sm:block"></span>

                                            <span class="inline-flex items-center gap-1.5">
                                                <i class="fa-solid fa-clock-rotate-left text-[10px] text-gray-600"></i>
                                                Updated
                                                <span class="font-semibold text-gray-300">
                                                    {{ $permissionUser->updated_at->format('d M Y H:i') }}
                                                </span>
                                            </span>
                                        @endif

                                        @if($permissionUser->trashed())
                                            <span class="hidden h-3 w-px bg-white/10 sm:block"></span>

                                            <span class="inline-flex items-center gap-1.5 text-red-400">
                                                <i class="fa-solid fa-box-archive text-[10px]"></i>
                                                Deleted

                                                @if($permissionUser->deleted_at)
                                                    {{ $permissionUser->deleted_at->format('d M Y H:i') }}
                                                @endif
                                            </span>
                                        @endif

                                    </div>

                                </div>
                            </article>

                            {{-- =================================================
                                 DELETE CONFIRMATION MODAL
                            ================================================== --}}
                            @if(!$permissionUser->trashed() && Route::has('permission.user.delete'))

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

                                            {{-- MODAL HEADER --}}
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

                                            {{-- MODAL BODY --}}
                                            <div class="space-y-4 px-6 py-5">

                                                <p class="text-sm leading-6 text-gray-400">
                                                    You are about to delete this permission assignment.
                                                    This operation will perform a
                                                    <strong class="text-gray-200">soft delete</strong>,
                                                    meaning the record will be marked as deleted rather than
                                                    permanently removed from the database.
                                                </p>

                                                {{-- ASSIGNMENT SUMMARY --}}
                                                <div class="rounded-xl border border-white/5 bg-gray-950/60 p-4">

                                                    <div class="flex items-start gap-3">

                                                        <div
                                                            class="flex h-9 w-9 flex-shrink-0 items-center
                                                                   justify-center rounded-lg bg-white/5
                                                                   text-gray-400"
                                                        >
                                                            <i class="fa-solid fa-user-shield"></i>
                                                        </div>

                                                        <div class="min-w-0 flex-1 space-y-2">

                                                            {{-- TARGET USER --}}
                                                            <div>
                                                                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-500">
                                                                    User
                                                                </p>

                                                                <p class="mt-1 break-words text-sm font-semibold text-white">
                                                                    {{ $permissionUser->user?->name ?? 'Unknown user' }}
                                                                </p>

                                                                <p class="mt-0.5 break-all text-xs text-gray-500">
                                                                    {{ $permissionUser->user?->email ?? 'No email available' }}
                                                                </p>
                                                            </div>

                                                            {{-- TARGET PERMISSION --}}
                                                            <div class="border-t border-white/5 pt-2">

                                                                <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-500">
                                                                    Permission
                                                                </p>

                                                                <p class="mt-1 break-words text-sm font-semibold text-white">
                                                                    {{ $permissionUser->permission?->name ?? 'Unknown permission' }}
                                                                </p>

                                                                @if($permissionUser->permission?->slug)
                                                                    <code class="mt-1 inline-block max-w-full break-all text-xs text-gray-400">
                                                                        {{ $permissionUser->permission->slug }}
                                                                    </code>
                                                                @endif

                                                            </div>

                                                            {{-- ASSIGNMENT ID --}}
                                                            <div class="border-t border-white/5 pt-2">

                                                                <p class="text-xs text-gray-500">
                                                                    Assignment ID:

                                                                    <span class="font-semibold text-gray-300">
                                                                        #{{ $permissionUser->id }}
                                                                    </span>
                                                                </p>

                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- WARNING --}}
                                                <div
                                                    class="flex items-start gap-2.5 rounded-xl
                                                           border border-amber-500/20 bg-amber-500/5 p-3"
                                                >
                                                    <i class="fa-solid fa-triangle-exclamation mt-0.5 text-amber-400"></i>

                                                    <p class="text-xs leading-5 text-amber-200/80">
                                                        This assignment will disappear from the default active listing.
                                                        Restoring it will require a separate authorized operation.
                                                        Verify that you are deleting the intended assignment.
                                                    </p>
                                                </div>

                                            </div>

                                            {{-- MODAL ACTIONS --}}
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

                        </div>

                    @endforeach

                </div>

                {{-- PAGINATION --}}
                @if($permissionUsers->hasPages())
                    <div
                        class="mt-6 rounded-2xl border border-white/5
                               bg-gray-900/70 p-4"
                    >
                        {{ $permissionUsers->links() }}
                    </div>
                @endif

            @endif

        </main>
    </div>
</div>

@endsection