@extends('layouts.base')

@section('title', 'Permission Details - Iris App')

@section('content')

@php
    $isArchived = method_exists($permission, 'trashed') && $permission->trashed();
    $moduleName = $permission->module ?: 'system';
    $moduleLabel = str($moduleName)->replace(['-', '_'], ' ')->headline();
@endphp

<div class="mx-auto w-full max-w-7xl space-y-6">

    {{-- =========================================================
         PAGE HEADER
    ========================================================== --}}
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

        <div>
            <div class="mb-3 flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400 ring-1 ring-indigo-500/20">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>
                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-400">
                    Access Control
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                    Permission Details
                </h1>

                @if($isArchived)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-red-500/20 bg-red-500/10 px-3 py-1 text-xs font-bold text-red-300">
                        <i class="fa-solid fa-box-archive"></i>
                        Archived
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        Available
                    </span>
                @endif
            </div>

            <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                Inspect the permission identifier, module, description and audit information.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @if(Route::has('permission.list'))
                <a
                    href="{{ route('permission.list') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-gray-300 transition hover:bg-white/10 hover:text-white active:scale-[0.98]"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to permissions
                </a>
            @endif

            @if(!$isArchived && Route::has('permission.edit'))
                <a
                    href="{{ route('permission.edit', $permission->id) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:-translate-y-0.5 hover:bg-indigo-500 active:translate-y-0"
                >
                    <i class="fa-solid fa-pen-to-square"></i>
                    Edit permission
                </a>
            @endif
        </div>

    </div>


    {{-- =========================================================
         HERO / PERMISSION PREVIEW
    ========================================================== --}}
    <section class="relative overflow-hidden rounded-3xl border border-white/5 bg-gray-900/80 shadow-2xl shadow-black/10">
        <div class="absolute inset-x-0 top-0 h-1 bg-indigo-500"></div>
        <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-indigo-500 opacity-[0.08] blur-3xl"></div>

        <div class="relative grid grid-cols-1 gap-8 p-6 sm:p-8 lg:grid-cols-[minmax(0,1fr)_280px] lg:items-center">

            <div class="flex min-w-0 items-start gap-5">
                <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-2xl border border-indigo-500/20 bg-indigo-500/10 text-indigo-400 shadow-lg">
                    <i class="fa-solid fa-key text-2xl"></i>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">
                        Permission name
                    </p>
                    <h2 class="break-words text-2xl font-black tracking-tight text-white sm:text-3xl">
                        {{ $permission->name }}
                    </h2>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <code class="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-gray-950/70 px-3 py-1.5 font-mono text-xs font-semibold text-indigo-300">
                            <i class="fa-solid fa-code text-gray-500"></i>
                            {{ $permission->slug }}
                        </code>

                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-white/5 bg-white/[0.03] px-3 py-1.5 text-xs text-gray-400">
                            <i class="fa-solid fa-fingerprint text-gray-500"></i>
                            ID {{ $permission->id }}
                        </span>
                    </div>

                    <p class="mt-4 max-w-3xl text-sm leading-6 text-gray-400">
                        {{ $permission->description ?: 'No description has been provided for this permission.' }}
                    </p>
                </div>
            </div>

            <div class="rounded-2xl border border-white/5 bg-gray-950/50 p-5">
                <div class="mb-4 flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Module</span>
                    <i class="fa-solid fa-layer-group text-gray-600"></i>
                </div>

                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-indigo-500/20 bg-indigo-500/10 text-indigo-400">
                        <i class="fa-solid fa-cubes"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="break-words text-sm font-bold text-white">{{ $moduleLabel }}</p>
                        <p class="mt-1 truncate font-mono text-xs text-gray-500">{{ $moduleName }}</p>
                    </div>
                </div>

                <div class="mt-5 h-1.5 overflow-hidden rounded-full bg-white/5">
                    <div class="h-full w-full rounded-full bg-indigo-500"></div>
                </div>
            </div>

        </div>
    </section>


    {{-- =========================================================
         QUICK OVERVIEW
    ========================================================== --}}
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-2xl border border-white/5 bg-gray-900/70 p-5 shadow-xl shadow-black/5">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Permission module</p>
                    <p class="mt-2 break-words text-xl font-black text-white">{{ $moduleLabel }}</p>
                    <p class="mt-1 text-xs text-gray-600">Logical permission grouping</p>
                </div>
                <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400">
                    <i class="fa-solid fa-layer-group"></i>
                </span>
            </div>
        </div>

        <div class="rounded-2xl border border-white/5 bg-gray-900/70 p-5 shadow-xl shadow-black/5">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Identifier</p>
                    <p class="mt-2 break-all font-mono text-lg font-black text-white">{{ $permission->slug }}</p>
                    <p class="mt-1 text-xs text-gray-600">Used by authorization checks</p>
                </div>
                <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-violet-500/10 text-violet-400">
                    <i class="fa-solid fa-fingerprint"></i>
                </span>
            </div>
        </div>

        <div class="rounded-2xl border border-white/5 bg-gray-900/70 p-5 shadow-xl shadow-black/5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Version</p>
                    <p class="mt-2 text-2xl font-black text-white">v{{ $permission->version }}</p>
                    <p class="mt-1 text-xs text-gray-600">Concurrency revision</p>
                </div>
                <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-sky-500/10 text-sky-400">
                    <i class="fa-solid fa-code-branch"></i>
                </span>
            </div>
        </div>

        <div class="rounded-2xl border border-white/5 bg-gray-900/70 p-5 shadow-xl shadow-black/5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Record state</p>
                    <p class="mt-2 text-xl font-black {{ $isArchived ? 'text-red-300' : 'text-emerald-300' }}">
                        {{ $isArchived ? 'Archived' : 'Available' }}
                    </p>
                    <p class="mt-1 text-xs text-gray-600">Permission record status</p>
                </div>
                <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl {{ $isArchived ? 'bg-red-500/10 text-red-400' : 'bg-emerald-500/10 text-emerald-400' }}">
                    <i class="fa-solid {{ $isArchived ? 'fa-box-archive' : 'fa-shield-halved' }}"></i>
                </span>
            </div>
        </div>

    </section>


    {{-- =========================================================
         DETAILS GRID
    ========================================================== --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)]">

        {{-- PERMISSION INFORMATION --}}
        <section class="overflow-hidden rounded-2xl border border-white/5 bg-gray-900/80 shadow-xl shadow-black/5">
            <div class="border-b border-white/5 px-6 py-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400">
                        <i class="fa-solid fa-circle-info"></i>
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-white">Permission information</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Identity and purpose of this authorization rule.</p>
                    </div>
                </div>
            </div>

            <dl class="divide-y divide-white/5 px-6">
                <div class="flex flex-col gap-2 py-4 sm:flex-row sm:items-start sm:justify-between sm:gap-6">
                    <dt class="text-xs font-medium text-gray-500">Name</dt>
                    <dd class="break-words text-sm font-semibold text-white sm:max-w-[70%] sm:text-right">{{ $permission->name }}</dd>
                </div>

                <div class="flex flex-col gap-2 py-4 sm:flex-row sm:items-start sm:justify-between sm:gap-6">
                    <dt class="text-xs font-medium text-gray-500">Slug</dt>
                    <dd class="break-all font-mono text-xs font-semibold text-indigo-300 sm:max-w-[70%] sm:text-right">{{ $permission->slug }}</dd>
                </div>

                <div class="flex flex-col gap-2 py-4 sm:flex-row sm:items-start sm:justify-between sm:gap-6">
                    <dt class="text-xs font-medium text-gray-500">Module</dt>
                    <dd class="break-words text-sm font-semibold text-gray-200 sm:max-w-[70%] sm:text-right">{{ $moduleLabel }}</dd>
                </div>

                <div class="flex flex-col gap-2 py-4 sm:flex-row sm:items-start sm:justify-between sm:gap-6">
                    <dt class="text-xs font-medium text-gray-500">Description</dt>
                    <dd class="whitespace-pre-line break-words text-sm leading-6 text-gray-300 sm:max-w-[70%] sm:text-right">{{ $permission->description ?: 'No description provided.' }}</dd>
                </div>
            </dl>
        </section>


        {{-- RECORD INFORMATION --}}
        <div class="space-y-6">

            <section class="overflow-hidden rounded-2xl border border-white/5 bg-gray-900/80 shadow-xl shadow-black/5">
                <div class="border-b border-white/5 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-500/10 text-sky-400">
                            <i class="fa-solid fa-database"></i>
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-white">Record information</h2>
                            <p class="mt-0.5 text-xs text-gray-500">System identifiers and timestamps.</p>
                        </div>
                    </div>
                </div>

                <dl class="divide-y divide-white/5 px-6">
                    <div class="flex items-start justify-between gap-4 py-4">
                        <dt class="text-xs text-gray-500">Record ID</dt>
                        <dd class="break-all text-right font-mono text-xs font-semibold text-gray-300">{{ $permission->id }}</dd>
                    </div>

                    <div class="flex items-start justify-between gap-4 py-4">
                        <dt class="text-xs text-gray-500">Version</dt>
                        <dd class="text-right text-xs font-semibold text-gray-300">{{ $permission->version }}</dd>
                    </div>

                    <div class="flex items-start justify-between gap-4 py-4">
                        <dt class="text-xs text-gray-500">Created at</dt>
                        <dd class="text-right text-xs font-semibold text-gray-300">
                            {{ $permission->created_at?->format('d/m/Y H:i:s') ?? '—' }}
                        </dd>
                    </div>

                    <div class="flex items-start justify-between gap-4 py-4">
                        <dt class="text-xs text-gray-500">Last updated</dt>
                        <dd class="text-right text-xs font-semibold text-gray-300">
                            {{ $permission->updated_at?->format('d/m/Y H:i:s') ?? '—' }}
                        </dd>
                    </div>

                    @if($permission->deleted_at)
                        <div class="flex items-start justify-between gap-4 py-4">
                            <dt class="text-xs text-gray-500">Archived at</dt>
                            <dd class="text-right text-xs font-semibold text-red-300">
                                {{ $permission->deleted_at->format('d/m/Y H:i:s') }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </section>


            {{-- AUTHORIZATION SUMMARY --}}
            <section class="rounded-2xl border border-white/5 bg-gray-900/80 p-6 shadow-xl shadow-black/5">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-violet-500/10 text-violet-400">
                        <i class="fa-solid fa-shield-halved"></i>
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-white">Authorization summary</h2>
                        <p class="mt-1 text-xs leading-5 text-gray-500">
                            This permission is identified by the slug
                            <code class="break-all rounded bg-gray-950/70 px-1.5 py-0.5 font-mono text-indigo-300">{{ $permission->slug }}</code>
                            and grouped under the <span class="text-gray-300">{{ $moduleLabel }}</span> module.
                        </p>
                    </div>
                </div>

                <div class="mt-5 rounded-xl border border-white/5 bg-gray-950/50 p-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-400">
                            <i class="fa-solid fa-key"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-white">{{ $permission->name }}</p>
                            <p class="mt-1 break-all font-mono text-[11px] text-gray-500">{{ $permission->slug }}</p>
                        </div>
                        <i class="fa-solid {{ $isArchived ? 'fa-box-archive text-red-400' : 'fa-circle-check text-emerald-400' }}"></i>
                    </div>
                </div>

                <p class="mt-4 text-xs leading-5 text-gray-600">
                    Assign this permission only to roles or users that should be allowed to perform the associated action.
                </p>
            </section>

        </div>

    </div>


    {{-- FOOTER --}}
    <div class="flex flex-col-reverse gap-3 border-t border-white/5 pt-5 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-gray-600">
            <i class="fa-solid fa-shield-halved mr-1.5"></i>
            Permission configuration details
        </p>

        <div class="flex flex-wrap justify-end gap-3">
            @if(Route::has('permission.list'))
                <a
                    href="{{ route('permission.list') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-gray-300 transition hover:bg-white/10 hover:text-white"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to list
                </a>
            @endif

            @if(!$isArchived && Route::has('permission.edit'))
                <a
                    href="{{ route('permission.edit', $permission->id) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500"
                >
                    <i class="fa-solid fa-pen-to-square"></i>
                    Edit permission
                </a>
            @endif
        </div>
    </div>

</div>

@endsection
