@extends('layouts.base')

@section('title', 'Permissions - Iris App')

@section('content')

@php
    $sortBy = request('sort_by', 'name');
    $sortOrder = request('sort_order', 'asc');

    $activeFilterCount = collect([
        request('name'),
        request('slug'),
        request('module'),
        request('description'),
        request('trashed'),
    ])->filter(fn ($value) => filled($value))->count();

    $deleteRoute = Route::has('permission.destroy')
        ? 'permission.destroy'
        : (Route::has('permission.delete') ? 'permission.delete' : null);
@endphp

<div class="min-h-full">

    {{-- =========================================================
         PAGE HEADER
    ========================================================== --}}
    <div class="mb-8">

        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

            <div>
                <div class="mb-3 flex items-center gap-2">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400 ring-1 ring-indigo-500/20">
                        <i class="fa-solid fa-shield-halved"></i>
                    </span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-400">
                        Access Control
                    </span>
                </div>

                <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                    Permissions
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                    Manage the permissions that define what users can access and do throughout Iris.
                    Keep names, slugs and modules organized as the system evolves.
                </p>
            </div>

            @if(Route::has('permission.create'))
                <a
                    href="{{ route('permission.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3
                           text-sm font-bold text-white shadow-lg shadow-indigo-600/20
                           transition duration-200 hover:-translate-y-0.5 hover:bg-indigo-500 active:translate-y-0"
                >
                    <i class="fa-solid fa-plus"></i>
                    New Permission
                </a>
            @endif

        </div>

        {{-- FEEDBACK --}}
        @if(session('success'))
            <div class="mt-6 flex items-start gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4">
                <i class="fa-solid fa-circle-check mt-0.5 text-emerald-400"></i>
                <p class="text-sm text-emerald-300">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="mt-6 flex items-start gap-3 rounded-xl border border-red-500/20 bg-red-500/10 p-4">
                <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-400"></i>
                <p class="text-sm text-red-300">{{ session('error') }}</p>
            </div>
        @endif

        {{-- SUMMARY --}}
        <div class="mt-7 grid grid-cols-1 gap-3 sm:grid-cols-3">

            {{-- RESULTS --}}
            <div class="rounded-2xl border border-white/5 bg-gray-900/70 px-5 py-4 shadow-xl shadow-black/5 backdrop-blur">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Results</p>
                        <p class="mt-1 text-2xl font-black text-white">{{ $permissions->total() }}</p>
                        <p class="mt-1 text-xs text-gray-600">Matching permissions</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                </div>
            </div>

            {{-- CURRENT PAGE --}}
            <div class="rounded-2xl border border-white/5 bg-gray-900/70 px-5 py-4 shadow-xl shadow-black/5 backdrop-blur">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">On this page</p>
                        <p class="mt-1 text-2xl font-black text-white">{{ $permissions->count() }}</p>
                        <p class="mt-1 text-xs text-gray-600">Currently displayed</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                </div>
            </div>

            {{-- FILTERS --}}
            <div class="rounded-2xl border border-white/5 bg-gray-900/70 px-5 py-4 shadow-xl shadow-black/5 backdrop-blur">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Active filters</p>
                        <p class="mt-1 text-2xl font-black text-white">{{ $activeFilterCount }}</p>
                        <p class="mt-1 text-xs text-gray-600">Applied to this query</p>
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

        {{-- FILTER SIDEBAR --}}
        <aside class="h-fit xl:sticky xl:top-6">

            <details open class="group overflow-hidden rounded-2xl border border-white/5 bg-gray-900/80 shadow-2xl shadow-black/10">

                <summary class="flex cursor-pointer list-none items-center justify-between px-5 py-4 [&::-webkit-details-marker]:hidden">
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
                            <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-indigo-500/15 px-2 text-[11px] font-bold text-indigo-400">
                                {{ $activeFilterCount }}
                            </span>
                        @endif
                        <i class="fa-solid fa-chevron-down text-xs text-gray-500 transition-transform duration-200 group-open:rotate-180"></i>
                    </div>
                </summary>

                <div class="border-t border-white/5 p-5">
                    <form method="GET" action="{{ route('permission.list') }}" class="space-y-5">

                        {{-- NAME --}}
                        <div>
                            <label for="filter_name" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">
                                Permission name
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-500"></i>
                                <input
                                    type="text"
                                    id="filter_name"
                                    name="name"
                                    value="{{ request('name') }}"
                                    maxlength="150"
                                    placeholder="Search permissions..."
                                    class="w-full rounded-xl border border-white/10 bg-gray-950/60 py-2.5 pl-10 pr-3
                                           text-sm text-white placeholder:text-gray-600 outline-none transition
                                           focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                                />
                            </div>
                            <label class="mt-2.5 inline-flex cursor-pointer items-center gap-2 text-xs text-gray-500">
                                <input type="checkbox" name="name_ignore_case" value="1" @checked(request()->boolean('name_ignore_case'))
                                       class="h-3.5 w-3.5 rounded border-gray-600 bg-gray-800 text-indigo-600 focus:ring-indigo-500/20">
                                <span>Ignore case</span>
                            </label>
                        </div>

                        {{-- SLUG --}}
                        <div>
                            <label for="filter_slug" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">
                                Permission slug
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-code absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-500"></i>
                                <input
                                    type="text"
                                    id="filter_slug"
                                    name="slug"
                                    value="{{ request('slug') }}"
                                    maxlength="200"
                                    placeholder="e.g. ticket.create"
                                    class="w-full rounded-xl border border-white/10 bg-gray-950/60 py-2.5 pl-10 pr-3
                                           text-sm font-mono text-white placeholder:text-gray-600 outline-none transition
                                           focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                                />
                            </div>
                            <label class="mt-2.5 inline-flex cursor-pointer items-center gap-2 text-xs text-gray-500">
                                <input type="checkbox" name="slug_ignore_case" value="1" @checked(request()->boolean('slug_ignore_case'))
                                       class="h-3.5 w-3.5 rounded border-gray-600 bg-gray-800 text-indigo-600 focus:ring-indigo-500/20">
                                <span>Ignore case</span>
                            </label>
                        </div>

                        {{-- MODULE --}}
                        <div>
                            <label for="filter_module" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">
                                Module
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-cubes-stacked absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-500"></i>
                                <input
                                    type="text"
                                    id="filter_module"
                                    name="module"
                                    value="{{ request('module') }}"
                                    maxlength="100"
                                    placeholder="e.g. ticket, user, system"
                                    class="w-full rounded-xl border border-white/10 bg-gray-950/60 py-2.5 pl-10 pr-3
                                           text-sm text-white placeholder:text-gray-600 outline-none transition
                                           focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                                />
                            </div>
                            <label class="mt-2.5 inline-flex cursor-pointer items-center gap-2 text-xs text-gray-500">
                                <input type="checkbox" name="module_ignore_case" value="1" @checked(request()->boolean('module_ignore_case'))
                                       class="h-3.5 w-3.5 rounded border-gray-600 bg-gray-800 text-indigo-600 focus:ring-indigo-500/20">
                                <span>Ignore case</span>
                            </label>
                        </div>

                        {{-- DESCRIPTION --}}
                        <div>
                            <label for="filter_description" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">
                                Description
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-align-left absolute left-3.5 top-3 text-sm text-gray-500"></i>
                                <input
                                    type="text"
                                    id="filter_description"
                                    name="description"
                                    value="{{ request('description') }}"
                                    maxlength="500"
                                    placeholder="Search description..."
                                    class="w-full rounded-xl border border-white/10 bg-gray-950/60 py-2.5 pl-10 pr-3
                                           text-sm text-white placeholder:text-gray-600 outline-none transition
                                           focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                                />
                            </div>
                        </div>

                        {{-- ARCHIVED --}}
                        <div>
                            <label for="filter_trashed" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">
                                Archived items
                            </label>
                            <select
                                id="filter_trashed"
                                name="trashed"
                                class="w-full rounded-xl border border-white/10 bg-gray-950/60 px-3 py-2.5
                                       text-sm text-gray-200 outline-none transition
                                       focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                            >
                                <option value="" @selected(!request('trashed'))>Without archived</option>
                                <option value="with" @selected(request('trashed') === 'with')>Include archived</option>
                                <option value="only" @selected(request('trashed') === 'only')>Archived only</option>
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
                                    class="min-w-0 rounded-xl border border-white/10 bg-gray-950/60 px-3 py-2.5
                                           text-sm text-gray-200 outline-none transition
                                           focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                                >
                                    <option value="name" @selected($sortBy === 'name')>Name</option>
                                    <option value="slug" @selected($sortBy === 'slug')>Slug</option>
                                    <option value="module" @selected($sortBy === 'module')>Module</option>
                                    <option value="id" @selected($sortBy === 'id')>ID</option>
                                    <option value="version" @selected($sortBy === 'version')>Version</option>
                                    <option value="created_at" @selected($sortBy === 'created_at')>Created date</option>
                                    <option value="updated_at" @selected($sortBy === 'updated_at')>Updated date</option>
                                </select>

                                <select
                                    name="sort_order"
                                    aria-label="Sort direction"
                                    class="rounded-xl border border-white/10 bg-gray-950/60 px-3 py-2.5
                                           text-sm text-gray-200 outline-none transition
                                           focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                                >
                                    <option value="asc" @selected($sortOrder === 'asc')>↑</option>
                                    <option value="desc" @selected($sortOrder === 'desc')>↓</option>
                                </select>
                            </div>
                        </div>

                        {{-- PAGE SIZE --}}
                        <div>
                            <label for="filter_per_page" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">
                                Results per page
                            </label>
                            <select
                                id="filter_per_page"
                                name="per_page"
                                class="w-full rounded-xl border border-white/10 bg-gray-950/60 px-3 py-2.5
                                       text-sm text-gray-200 outline-none transition
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
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-3 py-2.5
                                       text-sm font-bold text-white shadow-lg shadow-indigo-600/20
                                       transition hover:bg-indigo-500 active:scale-[0.98]"
                            >
                                <i class="fa-solid fa-filter"></i>
                                Apply
                            </button>

                            <a
                                href="{{ route('permission.list') }}"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10
                                       bg-white/5 px-3 py-2.5 text-sm font-semibold text-gray-300
                                       transition hover:bg-white/10 hover:text-white active:scale-[0.98]"
                            >
                                <i class="fa-solid fa-rotate-left"></i>
                                Clear
                            </a>
                        </div>

                    </form>
                </div>
            </details>

            {{-- PERMISSION SLUG INFO --}}
            <div class="mt-4 rounded-2xl border border-white/5 bg-gray-900/80 p-5 shadow-xl shadow-black/5">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-violet-500/10 text-violet-400">
                        <i class="fa-solid fa-fingerprint"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">Permission slug</h3>
                        <p class="mt-2 text-xs leading-5 text-gray-500">
                            Slugs are stable identifiers used by authorization checks, such as
                            <code class="text-gray-300">ticket.create</code> or
                            <code class="text-gray-300">user.update</code>.
                        </p>
                    </div>
                </div>
            </div>

            {{-- MODULE INFO --}}
            <div class="mt-4 rounded-2xl border border-white/5 bg-gray-900/80 p-5 shadow-xl shadow-black/5">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400">
                        <i class="fa-solid fa-cubes-stacked"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">Permission modules</h3>
                        <p class="mt-2 text-xs leading-5 text-gray-500">
                            Group permissions by their responsibility, for example
                            <code class="text-gray-300">ticket</code>,
                            <code class="text-gray-300">user</code> or
                            <code class="text-gray-300">system</code>.
                        </p>
                    </div>
                </div>
            </div>

        </aside>


        {{-- =====================================================
             PERMISSION RESULTS
        ====================================================== --}}
        <main class="min-w-0">

            {{-- RESULTS TOOLBAR --}}
            <div class="mb-4 flex flex-col gap-3 rounded-2xl border border-white/5 bg-gray-900/70 p-4
                        shadow-xl shadow-black/5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm font-bold text-white">Permission collection</span>
                        @if($permissions->total() > 0)
                            <span class="rounded-full bg-white/5 px-2 py-0.5 text-[11px] font-semibold text-gray-400">
                                {{ $permissions->total() }} total
                            </span>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Review permission identifiers and their assigned modules.
                    </p>
                </div>

                @if($permissions->total() > 0)
                    <div class="text-xs text-gray-500">
                        Showing
                        <span class="font-semibold text-gray-300">{{ $permissions->firstItem() }}–{{ $permissions->lastItem() }}</span>
                        of
                        <span class="font-semibold text-gray-300">{{ $permissions->total() }}</span>
                    </div>
                @endif
            </div>

            {{-- EMPTY STATE --}}
            @if($permissions->isEmpty())

                <div class="rounded-2xl border border-dashed border-white/10 bg-gray-900/50 px-6 py-16 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-500/10 text-2xl text-indigo-400">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                    <h3 class="mt-5 text-lg font-bold text-white">No permissions found</h3>

                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-gray-500">
                        No permissions match your current filters. Try changing your search criteria or create a new permission.
                    </p>

                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <a
                            href="{{ route('permission.list') }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5
                                   px-4 py-2.5 text-sm font-semibold text-gray-300 transition
                                   hover:bg-white/10 hover:text-white"
                        >
                            <i class="fa-solid fa-rotate-left"></i>
                            Clear filters
                        </a>

                        @if(Route::has('permission.create'))
                            <a
                                href="{{ route('permission.create') }}"
                                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5
                                       text-sm font-bold text-white transition hover:bg-indigo-500"
                            >
                                <i class="fa-solid fa-plus"></i>
                                Create permission
                            </a>
                        @endif
                    </div>
                </div>

            @else

                {{-- PERMISSION LIST --}}
                <div class="space-y-3">

                    @foreach($permissions as $permission)

                        <div
                            x-data="{ open: false, showDeleteModal: false }"
                            @keydown.escape.window="open = false; showDeleteModal = false"
                            class="relative"
                            :class="(open || showDeleteModal) ? 'z-40' : 'z-0'"
                        >

                            <article class="group relative rounded-2xl border border-white/5 bg-gray-900/80 p-5
                                            shadow-xl shadow-black/5 transition-colors duration-200
                                            hover:border-white/10 hover:bg-gray-900">

                                {{-- ACCENT --}}
                                <div class="absolute inset-y-0 left-0 w-1 rounded-l-2xl bg-indigo-500/70"></div>

                                {{-- ACTION MENU --}}
                                <div class="absolute right-4 top-4 z-30" @click.outside="open = false">
                                    <button
                                        type="button"
                                        @click="open = !open"
                                        :aria-expanded="open.toString()"
                                        aria-haspopup="true"
                                        aria-label="Actions for {{ $permission->name }}"
                                        class="flex h-9 w-9 items-center justify-center rounded-xl border border-transparent
                                               text-gray-500 transition hover:border-white/10 hover:bg-white/5 hover:text-white"
                                    >
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>

                                    <div
                                        x-show="open"
                                        x-cloak
                                        x-transition:enter="transition ease-out duration-150"
                                        x-transition:enter-start="opacity-0"
                                        x-transition:enter-end="opacity-100"
                                        x-transition:leave="transition ease-in duration-100"
                                        x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0"
                                        class="absolute right-0 top-11 z-40 w-52 overflow-hidden rounded-xl
                                               border border-white/10 bg-gray-900 p-1 shadow-2xl shadow-black/40"
                                    >

                                        @php
                                            $hasPermissionActions = Route::has('permission.show')
                                                || Route::has('permission.details')
                                                || Route::has('permission.edit')
                                                || $deleteRoute !== null
                                                || (Route::has('permission.restore') && $permission->trashed());
                                        @endphp

                                        {{-- DETAILS --}}
                                        <a
                                            href="{{ route('permission.show', $permission->id) }}"
                                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-300 transition hover:bg-white/5 hover:text-white"
                                        >
                                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/5 text-gray-300">
                                                <i class="fa-solid fa-eye text-xs"></i>
                                            </span>
                                            View details
                                        </a>
                                    

                                        {{-- EDIT --}}
                                        @if(!$permission->trashed() && Route::has('permission.update'))
                                            <a
                                                href="{{ route('permission.update', $permission->id) }}"
                                                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-300 transition hover:bg-white/5 hover:text-white"
                                            >
                                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-400">
                                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                </span>
                                                Edit permission
                                            </a>
                                        @endif

                                        {{-- RESTORE --}}
                                        @if($permission->trashed() && Route::has('permission.restore'))
                                            <form action="{{ route('permission.restore', $permission->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    type="submit"
                                                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm text-gray-300 transition hover:bg-white/5 hover:text-white"
                                                >
                                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                                                        <i class="fa-solid fa-rotate-left text-xs"></i>
                                                    </span>
                                                    Restore permission
                                                </button>
                                            </form>
                                        @endif

                                        {{-- DELETE --}}
                                        @if(!$permission->trashed() && $deleteRoute !== null)
                                            <div class="my-1 border-t border-white/5"></div>
                                            <button
                                                type="button"
                                                @click="showDeleteModal = true; open = false"
                                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm text-red-400 transition hover:bg-red-500/10 hover:text-red-300"
                                            >
                                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-500/10 text-red-400">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </span>
                                                Delete permission
                                            </button>
                                        @endif

                                        @unless($hasPermissionActions)
                                            <span class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-600">
                                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/[0.03] text-gray-600">
                                                    <i class="fa-solid fa-hourglass-half text-xs"></i>
                                                </span>
                                                No actions configured
                                            </span>
                                        @endunless

                                    </div>
                                </div>

                                {{-- PERMISSION CONTENT --}}
                                <div class="flex items-start gap-4 pl-1 pr-8">

                                    {{-- ICON --}}
                                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl border border-white/5 bg-gray-950/60 text-indigo-400">
                                        <i class="fa-solid fa-shield-halved text-lg"></i>
                                    </div>

                                    <div class="min-w-0 flex-1">

                                        {{-- NAME + BADGES --}}
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-base font-bold text-white">{{ $permission->name }}</h3>

                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-indigo-500/20 bg-indigo-500/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-indigo-300">
                                                <i class="fa-solid fa-cube"></i>
                                                {{ ucfirst($permission->module ?: 'system') }}
                                            </span>

                                            @if($permission->trashed())
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-red-500/20 bg-red-500/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-red-300">
                                                    <i class="fa-solid fa-box-archive"></i>
                                                    Archived
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-300">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                                    Available
                                                </span>
                                            @endif
                                        </div>

                                        {{-- SLUG --}}
                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <code class="rounded-lg border border-white/5 bg-gray-950/70 px-2.5 py-1.5 text-xs font-medium text-gray-300">
                                                {{ $permission->slug }}
                                            </code>
                                            <span class="text-[11px] text-gray-600">ID: {{ $permission->id }}</span>
                                        </div>

                                        {{-- DESCRIPTION --}}
                                        @if($permission->description)
                                            <p class="mt-3 max-w-3xl text-sm leading-6 text-gray-400">
                                                {{ $permission->description }}
                                            </p>
                                        @else
                                            <p class="mt-3 text-xs italic text-gray-600">No description provided.</p>
                                        @endif

                                        {{-- METADATA --}}
                                        <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-white/5 pt-3 text-xs text-gray-500">

                                            <span class="inline-flex items-center gap-1.5">
                                                <i class="fa-solid fa-cubes-stacked text-[10px] text-gray-600"></i>
                                                Module
                                                <span class="font-semibold text-gray-300">{{ $permission->module ?: 'system' }}</span>
                                            </span>

                                            <span class="hidden h-3 w-px bg-white/10 sm:block"></span>

                                            <span class="inline-flex items-center gap-1.5">
                                                <i class="fa-solid fa-code-branch text-[10px] text-gray-600"></i>
                                                Version
                                                <span class="font-semibold text-gray-300">{{ $permission->version }}</span>
                                            </span>

                                            @if($permission->created_at)
                                                <span class="hidden h-3 w-px bg-white/10 sm:block"></span>
                                                <span class="inline-flex items-center gap-1.5">
                                                    <i class="fa-regular fa-calendar text-[10px] text-gray-600"></i>
                                                    Created {{ $permission->created_at->format('d M Y') }}
                                                </span>
                                            @endif

                                            @if($permission->updated_at && $permission->updated_at != $permission->created_at)
                                                <span class="hidden h-3 w-px bg-white/10 sm:block"></span>
                                                <span class="inline-flex items-center gap-1.5">
                                                    <i class="fa-solid fa-clock-rotate-left text-[10px] text-gray-600"></i>
                                                    Updated {{ $permission->updated_at->format('d M Y') }}
                                                </span>
                                            @endif

                                        </div>

                                    </div>
                                </div>
                            </article>

                            {{-- DELETE CONFIRMATION MODAL --}}
                            @if(!$permission->trashed() && $deleteRoute !== null)
                                <template x-teleport="body">
                                    <div
                                        x-show="showDeleteModal"
                                        x-cloak
                                        x-transition.opacity.duration.150ms
                                        @click.self="showDeleteModal = false"
                                        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm"
                                        role="dialog"
                                        aria-modal="true"
                                        aria-labelledby="delete-permission-title-{{ $permission->id }}"
                                    >
                                        <div class="w-full max-w-md overflow-hidden rounded-2xl border border-white/10 bg-gray-900 shadow-2xl shadow-black/50">

                                            {{-- MODAL HEADER --}}
                                            <div class="border-b border-white/5 px-6 py-5">
                                                <div class="flex items-start gap-4">
                                                    <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-red-500/10 text-red-400 ring-1 ring-red-500/10">
                                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                                    </div>

                                                    <div class="min-w-0 flex-1">
                                                        <h3 id="delete-permission-title-{{ $permission->id }}" class="text-lg font-bold text-white">
                                                            Delete permission?
                                                        </h3>
                                                        <p class="mt-1 text-sm text-gray-500">This action requires confirmation.</p>
                                                    </div>

                                                    <button
                                                        type="button"
                                                        @click="showDeleteModal = false"
                                                        aria-label="Close confirmation"
                                                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-gray-500 transition hover:bg-white/5 hover:text-white"
                                                    >
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- MODAL BODY --}}
                                            <div class="space-y-4 px-6 py-5">
                                                <p class="text-sm leading-6 text-gray-400">
                                                    Are you sure you want to delete
                                                    <strong class="font-semibold text-white">{{ $permission->name }}</strong>?
                                                </p>

                                                <div class="rounded-xl border border-amber-500/10 bg-amber-500/5 p-4">
                                                    <div class="flex gap-3">
                                                        <i class="fa-solid fa-circle-info mt-0.5 text-amber-400"></i>
                                                        <div class="text-sm leading-6 text-gray-400">
                                                            <p class="font-semibold text-gray-200">Before you continue</p>
                                                            <p class="mt-1 text-xs leading-5 text-gray-500">
                                                                This permission may be referenced by users, roles or authorization checks.
                                                                Verify its dependencies before removing it.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-3 rounded-xl border border-white/5 bg-gray-950/50 p-3">
                                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border border-white/5 bg-indigo-500/10 text-indigo-400">
                                                        <i class="fa-solid fa-shield-halved"></i>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-semibold text-white">{{ $permission->name }}</p>
                                                        <p class="truncate font-mono text-xs text-gray-500">{{ $permission->slug }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- MODAL FOOTER --}}
                                            <div class="flex flex-col-reverse gap-3 border-t border-white/5 bg-gray-950/30 px-6 py-4 sm:flex-row">
                                                <button
                                                    type="button"
                                                    @click="showDeleteModal = false"
                                                    class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-gray-300 transition hover:bg-white/10 hover:text-white"
                                                >
                                                    Cancel
                                                </button>

                                                <form action="{{ route($deleteRoute, $permission->id) }}" method="POST" class="w-full">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-red-600/20 transition hover:bg-red-500 active:scale-[0.98]"
                                                    >
                                                        <i class="fa-solid fa-trash-can mr-1.5"></i>
                                                        Yes, delete permission
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
                @if($permissions->hasPages())
                    <div class="mt-6 overflow-hidden rounded-2xl border border-white/5 bg-gray-900/60 p-4">
                        {{ $permissions->withQueryString()->links() }}
                    </div>
                @endif

            @endif

        </main>

    </div>

</div>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>

@endsection
