@extends('layouts.base')

@section('title', 'Ticket Statuses - Iris App')

@section('content')

@php
    $sortBy = request('sort_by', 'display_order');
    $sortOrder = request('sort_order', 'asc');

    $activeFilterCount = collect([
        request('name'),
        request('code'),
        request('is_active'),
        request('is_initial'),
        request('is_final'),
        request('stops_sla'),
        request('requires_comment'),
        request('trashed'),
    ])->filter(fn ($value) => filled($value))->count();

    // Aceita tanto "ticket.status.destroy" quanto "ticket.status.delete"
    $deleteRoute = Route::has('ticket.status.destroy')
        ? 'ticket.status.destroy'
        : (Route::has('ticket.status.delete') ? 'ticket.status.delete' : null);
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
                        <i class="fa-solid fa-circle-half-stroke"></i>
                    </span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-400">
                        Ticket Management
                    </span>
                </div>

                <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                    Ticket Statuses
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                    Manage the workflow states of your support tickets.
                    Configure their appearance, lifecycle behavior and SLA rules.
                </p>
            </div>

            @if(Route::has('ticket.status.create'))
                <a
                    href="{{ route('ticket.status.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3
                           text-sm font-bold text-white shadow-lg shadow-indigo-600/20
                           transition duration-200 hover:-translate-y-0.5 hover:bg-indigo-500 active:translate-y-0"
                >
                    <i class="fa-solid fa-plus"></i>
                    New Status
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

        {{-- =====================================================
             SUMMARY
        ====================================================== --}}
        <div class="mt-7 grid grid-cols-1 gap-3 sm:grid-cols-3">

            {{-- RESULTS --}}
            <div class="rounded-2xl border border-white/5 bg-gray-900/70 px-5 py-4 shadow-xl shadow-black/5 backdrop-blur">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Results</p>
                        <p class="mt-1 text-2xl font-black text-white">{{ $tickets->total() }}</p>
                        <p class="mt-1 text-xs text-gray-600">Matching statuses</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                </div>
            </div>

            {{-- CURRENT PAGE --}}
            <div class="rounded-2xl border border-white/5 bg-gray-900/70 px-5 py-4 shadow-xl shadow-black/5 backdrop-blur">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">On this page</p>
                        <p class="mt-1 text-2xl font-black text-white">{{ $tickets->count() }}</p>
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

        {{-- =====================================================
             FILTER SIDEBAR
        ====================================================== --}}
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

                    <form method="GET" action="{{ route('ticket.status.list') }}" class="space-y-5">

                        {{-- NAME --}}
                        <div>
                            <label for="filter_name" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">
                                Status name
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-500"></i>
                                <input
                                    type="text"
                                    id="filter_name"
                                    name="name"
                                    value="{{ request('name') }}"
                                    maxlength="100"
                                    placeholder="Search statuses..."
                                    class="w-full rounded-xl border border-white/10 bg-gray-950/60 py-2.5 pl-10 pr-3
                                           text-sm text-white placeholder:text-gray-600 outline-none transition
                                           focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                                />
                            </div>
                        </div>

                        {{-- CODE --}}
                        <div>
                            <label for="filter_code" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">
                                Status code
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-code absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-500"></i>
                                <input
                                    type="text"
                                    id="filter_code"
                                    name="code"
                                    value="{{ request('code') }}"
                                    maxlength="50"
                                    placeholder="e.g. IN_PROGRESS"
                                    class="w-full rounded-xl border border-white/10 bg-gray-950/60 py-2.5 pl-10 pr-3
                                           text-sm font-mono text-white placeholder:text-gray-600 outline-none transition
                                           focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                                />
                            </div>
                        </div>

                        {{-- ACTIVE STATE --}}
                        <div>
                            <label for="filter_active" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">
                                Availability
                            </label>
                            <select
                                id="filter_active"
                                name="is_active"
                                class="w-full rounded-xl border border-white/10 bg-gray-950/60 px-3 py-2.5
                                       text-sm text-gray-200 outline-none transition
                                       focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                            >
                                <option value="">All statuses</option>
                                <option value="1" @selected(request('is_active') === '1')>Active</option>
                                <option value="0" @selected(request('is_active') === '0')>Inactive</option>
                            </select>
                        </div>

                        {{-- INITIAL --}}
                        <div>
                            <label for="filter_initial" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">
                                Initial status
                            </label>
                            <select
                                id="filter_initial"
                                name="is_initial"
                                class="w-full rounded-xl border border-white/10 bg-gray-950/60 px-3 py-2.5
                                       text-sm text-gray-200 outline-none transition
                                       focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                            >
                                <option value="">All</option>
                                <option value="1" @selected(request('is_initial') === '1')>Initial only</option>
                                <option value="0" @selected(request('is_initial') === '0')>Not initial</option>
                            </select>
                        </div>

                        {{-- FINAL --}}
                        <div>
                            <label for="filter_final" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">
                                Final status
                            </label>
                            <select
                                id="filter_final"
                                name="is_final"
                                class="w-full rounded-xl border border-white/10 bg-gray-950/60 px-3 py-2.5
                                       text-sm text-gray-200 outline-none transition
                                       focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                            >
                                <option value="">All</option>
                                <option value="1" @selected(request('is_final') === '1')>Final only</option>
                                <option value="0" @selected(request('is_final') === '0')>Not final</option>
                            </select>
                        </div>

                        {{-- SLA --}}
                        <div>
                            <label for="filter_sla" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">
                                SLA timer
                            </label>
                            <select
                                id="filter_sla"
                                name="stops_sla"
                                class="w-full rounded-xl border border-white/10 bg-gray-950/60 px-3 py-2.5
                                       text-sm text-gray-200 outline-none transition
                                       focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                            >
                                <option value="">All</option>
                                <option value="1" @selected(request('stops_sla') === '1')>Stops SLA</option>
                                <option value="0" @selected(request('stops_sla') === '0')>Continues SLA</option>
                            </select>
                        </div>

                        {{-- COMMENT --}}
                        <div>
                            <label for="filter_comment" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">
                                Comment requirement
                            </label>
                            <select
                                id="filter_comment"
                                name="requires_comment"
                                class="w-full rounded-xl border border-white/10 bg-gray-950/60 px-3 py-2.5
                                       text-sm text-gray-200 outline-none transition
                                       focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                            >
                                <option value="">All</option>
                                <option value="1" @selected(request('requires_comment') === '1')>Required</option>
                                <option value="0" @selected(request('requires_comment') === '0')>Not required</option>
                            </select>
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
                                    <option value="display_order" @selected($sortBy === 'display_order')>Display order</option>
                                    <option value="name" @selected($sortBy === 'name')>Name</option>
                                    <option value="code" @selected($sortBy === 'code')>Code</option>
                                    <option value="created_at" @selected($sortBy === 'created_at')>Created date</option>
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

                        {{-- FILTER ACTIONS --}}
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
                                href="{{ route('ticket.status.list') }}"
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

            {{-- WORKFLOW INFORMATION --}}
            <div class="mt-4 rounded-2xl border border-white/5 bg-gray-900/80 p-5 shadow-xl shadow-black/5">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-violet-500/10 text-violet-400">
                        <i class="fa-solid fa-route"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">Ticket lifecycle</h3>
                        <p class="mt-2 text-xs leading-5 text-gray-500">
                            Initial statuses define where tickets begin.
                            Final statuses represent the end of a workflow.
                        </p>
                    </div>
                </div>
            </div>

            {{-- SLA INFORMATION --}}
            <div class="mt-4 rounded-2xl border border-white/5 bg-gray-900/80 p-5 shadow-xl shadow-black/5">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                        <i class="fa-solid fa-stopwatch"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">SLA management</h3>
                        <p class="mt-2 text-xs leading-5 text-gray-500">
                            Configure whether a ticket should stop consuming
                            SLA time while it remains in a specific status.
                        </p>
                    </div>
                </div>
            </div>

        </aside>

        {{-- =====================================================
             TICKET STATUS RESULTS
        ====================================================== --}}
        <main class="min-w-0">

            {{-- RESULTS TOOLBAR --}}
            <div class="mb-4 flex flex-col gap-3 rounded-2xl border border-white/5 bg-gray-900/70 p-4
                        shadow-xl shadow-black/5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm font-bold text-white">Status collection</span>
                        @if($tickets->total() > 0)
                            <span class="rounded-full bg-white/5 px-2 py-0.5 text-[11px] font-semibold text-gray-400">
                                {{ $tickets->total() }} total
                            </span>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Manage the available states in your ticket workflow.
                    </p>
                </div>

                @if($tickets->total() > 0)
                    <div class="text-xs text-gray-500">
                        Showing
                        <span class="font-semibold text-gray-300">{{ $tickets->firstItem() }}–{{ $tickets->lastItem() }}</span>
                        of
                        <span class="font-semibold text-gray-300">{{ $tickets->total() }}</span>
                    </div>
                @endif
            </div>

            {{-- =================================================
                 EMPTY STATE
            ================================================== --}}
            @if($tickets->isEmpty())

                <div class="rounded-2xl border border-dashed border-white/10 bg-gray-900/50 px-6 py-16 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-500/10 text-2xl text-indigo-400">
                        <i class="fa-solid fa-circle-half-stroke"></i>
                    </div>

                    <h3 class="mt-5 text-lg font-bold text-white">No ticket statuses found</h3>

                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-gray-500">
                        No statuses match your current filters.
                        Try changing your search criteria or create a new status.
                    </p>

                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <a
                            href="{{ route('ticket.status.list') }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5
                                   px-4 py-2.5 text-sm font-semibold text-gray-300
                                   transition hover:bg-white/10 hover:text-white"
                        >
                            <i class="fa-solid fa-rotate-left"></i>
                            Clear filters
                        </a>

                        @if(Route::has('ticket.status.create'))
                            <a
                                href="{{ route('ticket.status.create') }}"
                                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5
                                       text-sm font-bold text-white transition hover:bg-indigo-500"
                            >
                                <i class="fa-solid fa-plus"></i>
                                Create status
                            </a>
                        @endif
                    </div>
                </div>

            @else

                {{-- =================================================
                     STATUS LIST
                ================================================== --}}
                <div class="space-y-3">

                    @foreach($tickets as $item)

                        @php
                            $statusColor = $item->color ?: '#6366f1';
                            $statusIcon = $item->icon ?: 'fa-solid fa-circle-half-stroke';
                        @endphp

                        {{--
                            WRAPPER
                            - "relative" + z-index dinâmico: o card com o menu aberto
                              passa por cima dos cards vizinhos (o menu deixa de ser coberto).
                            - Nenhum transform no hover do card: era isso que causava o "piscar".
                        --}}
                        <div
                            x-data="{ open: false, showDeleteModal: false }"
                            @keydown.escape.window="open = false; showDeleteModal = false"
                            class="relative"
                            :class="(open || showDeleteModal) ? 'z-40' : 'z-0'"
                        >

                            <article
                                class="group relative rounded-2xl border border-white/5 bg-gray-900/80 p-5
                                       shadow-xl shadow-black/5 transition-colors duration-200
                                       hover:border-white/10 hover:bg-gray-900"
                            >

                                {{-- COLOR ACCENT --}}
                                <div
                                    class="absolute inset-y-0 left-0 w-1 rounded-l-2xl"
                                    style="background-color: {{ $statusColor }};"
                                ></div>

                                {{-- ACTION MENU --}}
                                <div class="absolute right-4 top-4 z-30" @click.outside="open = false">

                                    <button
                                        type="button"
                                        @click="open = !open"
                                        :aria-expanded="open.toString()"
                                        aria-haspopup="true"
                                        aria-label="Actions for {{ $item->name }}"
                                        class="flex h-9 w-9 items-center justify-center rounded-xl
                                               border border-transparent text-gray-500 transition
                                               hover:border-white/10 hover:bg-white/5 hover:text-white"
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
                                               border border-white/10 bg-gray-900 p-1
                                               shadow-2xl shadow-black/40"
                                    >

                                        {{-- VIEW --}}
                                        @if(Route::has('ticket.status.show-details'))
                                            <a
                                                href="{{ route('ticket.status.show-details', $item->id) }}"
                                                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-300
                                                       transition hover:bg-white/5 hover:text-white"
                                            >
                                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/5 text-gray-300">
                                                    <i class="fa-solid fa-eye text-xs"></i>
                                                </span>
                                                View details
                                            </a>
                                        @else
                                            <span
                                                title="Route not configured yet"
                                                class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-600"
                                            >
                                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/[0.03] text-gray-600">
                                                    <i class="fa-solid fa-eye text-xs"></i>
                                                </span>
                                                View details
                                            </span>
                                        @endif

                                        {{-- EDIT --}}
                                        @if(!$item->trashed())
                                            @if(Route::has('ticket.status.update'))
                                                <a
                                                    href="{{ route('ticket.status.update', $item->id) }}"
                                                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-300
                                                           transition hover:bg-white/5 hover:text-white"
                                                >
                                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-400">
                                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                    </span>
                                                    Edit status
                                                </a>
                                            @else
                                                <span
                                                    title="Route not configured yet"
                                                    class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-600"
                                                >
                                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/[0.03] text-gray-600">
                                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                    </span>
                                                    Edit status
                                                </span>
                                            @endif
                                        @endif

                                        {{-- TOGGLE ACTIVE --}}
                                        @if(!$item->trashed())
                                            @if(Route::has('ticket.status.toggle-active'))
                                                <form action="{{ route('ticket.status.toggle-active', $item->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')

                                                    <button
                                                        type="submit"
                                                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm
                                                               text-gray-300 transition hover:bg-white/5 hover:text-white"
                                                    >
                                                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                                                            <i class="fa-solid {{ $item->is_active ? 'fa-toggle-off' : 'fa-toggle-on' }} text-xs"></i>
                                                        </span>
                                                        {{ $item->is_active ? 'Deactivate status' : 'Activate status' }}
                                                    </button>
                                                </form>
                                            @else
                                                <span
                                                    title="Route not configured yet"
                                                    class="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-600"
                                                >
                                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/[0.03] text-gray-600">
                                                        <i class="fa-solid {{ $item->is_active ? 'fa-toggle-off' : 'fa-toggle-on' }} text-xs"></i>
                                                    </span>
                                                    {{ $item->is_active ? 'Deactivate status' : 'Activate status' }}
                                                </span>
                                            @endif
                                        @endif

                                        {{-- RESTORE --}}
                                        @if(Route::has('ticket.status.restore') && $item->trashed())
                                            <form action="{{ route('ticket.status.restore', $item->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm
                                                           text-gray-300 transition hover:bg-white/5 hover:text-white"
                                                >
                                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                                                        <i class="fa-solid fa-rotate-left text-xs"></i>
                                                    </span>
                                                    Restore status
                                                </button>
                                            </form>
                                        @endif

                                        {{-- DELETE --}}
                                        @if(!$item->trashed())
                                            <div class="my-1 border-t border-white/5"></div>

                                            <button
                                                type="button"
                                                @click="showDeleteModal = true; open = false"
                                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm
                                                       text-red-400 transition hover:bg-red-500/10 hover:text-red-300"
                                            >
                                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-500/10 text-red-400">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </span>
                                                Delete status
                                            </button>
                                        @endif

                                    </div>
                                </div>

                                {{-- CARD CONTENT --}}
                                <div class="flex items-start gap-4 pl-1 pr-8">

                                    {{-- STATUS ICON --}}
                                    <div
                                        class="flex h-12 w-12 flex-shrink-0 items-center justify-center
                                               rounded-xl border border-white/5 bg-gray-950/60"
                                        style="color: {{ $statusColor }};"
                                    >
                                        <i class="{{ $statusIcon }} text-lg"></i>
                                    </div>

                                    {{-- STATUS DETAILS --}}
                                    <div class="min-w-0 flex-1">

                                        {{-- NAME AND BADGES --}}
                                        <div class="flex flex-wrap items-center gap-2">

                                            <h3 class="text-base font-bold text-white">{{ $item->name }}</h3>

                                            @if($item->is_active)
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/20
                                                             bg-emerald-500/10 px-2.5 py-1 text-[10px] font-bold uppercase
                                                             tracking-wider text-emerald-300">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-500/20
                                                             bg-gray-500/10 px-2.5 py-1 text-[10px] font-bold uppercase
                                                             tracking-wider text-gray-400">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-gray-500"></span>
                                                    Inactive
                                                </span>
                                            @endif

                                            @if($item->is_initial)
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-indigo-500/20
                                                             bg-indigo-500/10 px-2.5 py-1 text-[10px] font-bold uppercase
                                                             tracking-wider text-indigo-300">
                                                    <i class="fa-solid fa-play"></i>
                                                    Initial
                                                </span>
                                            @endif

                                            @if($item->is_final)
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-violet-500/20
                                                             bg-violet-500/10 px-2.5 py-1 text-[10px] font-bold uppercase
                                                             tracking-wider text-violet-300">
                                                    <i class="fa-solid fa-flag-checkered"></i>
                                                    Final
                                                </span>
                                            @endif

                                            @if($item->trashed())
                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-red-500/20
                                                             bg-red-500/10 px-2.5 py-1 text-[10px] font-bold uppercase
                                                             tracking-wider text-red-300">
                                                    <i class="fa-solid fa-box-archive"></i>
                                                    Archived
                                                </span>
                                            @endif

                                        </div>

                                        {{-- CODE --}}
                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <code class="rounded-lg border border-white/5 bg-gray-950/70 px-2 py-1 text-xs font-medium text-gray-300">
                                                {{ $item->code }}
                                            </code>
                                            <span class="text-[11px] text-gray-600">ID: {{ $item->id }}</span>
                                        </div>

                                        {{-- DESCRIPTION --}}
                                        @if($item->description)
                                            <p class="mt-3 max-w-3xl text-sm leading-6 text-gray-400">
                                                {{ $item->description }}
                                            </p>
                                        @endif

                                        {{-- WORKFLOW FLAGS --}}
                                        <div class="mt-4 flex flex-wrap gap-2">

                                            @if($item->stops_sla)
                                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-amber-500/15
                                                             bg-amber-500/5 px-2.5 py-1.5 text-[11px] font-medium text-amber-300">
                                                    <i class="fa-solid fa-stopwatch"></i>
                                                    Stops SLA
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-white/5
                                                             bg-white/[0.02] px-2.5 py-1.5 text-[11px] font-medium text-gray-500">
                                                    <i class="fa-solid fa-stopwatch"></i>
                                                    SLA continues
                                                </span>
                                            @endif

                                            @if($item->requires_comment)
                                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-blue-500/15
                                                             bg-blue-500/5 px-2.5 py-1.5 text-[11px] font-medium text-blue-300">
                                                    <i class="fa-solid fa-comment-dots"></i>
                                                    Comment required
                                                </span>
                                            @endif

                                        </div>

                                        {{-- METADATA --}}
                                        <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-white/5 pt-3 text-xs text-gray-500">

                                            <span class="inline-flex items-center gap-1.5">
                                                <i class="fa-solid fa-list-ol text-[10px] text-gray-600"></i>
                                                Order
                                                <span class="font-semibold text-gray-300">{{ $item->display_order }}</span>
                                            </span>

                                            <span class="hidden h-3 w-px bg-white/10 sm:block"></span>

                                            <span class="inline-flex items-center gap-1.5">
                                                <i class="fa-solid fa-palette text-[10px] text-gray-600"></i>
                                                <span class="font-mono text-gray-400">{{ $item->color ?: 'Default' }}</span>
                                            </span>

                                            @if($item->created_at)
                                                <span class="hidden h-3 w-px bg-white/10 sm:block"></span>

                                                <span class="inline-flex items-center gap-1.5">
                                                    <i class="fa-regular fa-calendar text-[10px] text-gray-600"></i>
                                                    Created {{ $item->created_at->format('d M Y') }}
                                                </span>
                                            @endif

                                        </div>

                                    </div>
                                </div>

                            </article>

                            {{-- =================================================
                                 DELETE CONFIRMATION MODAL
                            ================================================== --}}
                            @if(!$item->trashed())
                                <template x-teleport="body">
                                    <div
                                        x-show="showDeleteModal"
                                        x-cloak
                                        x-transition.opacity.duration.150ms
                                        @click.self="showDeleteModal = false"
                                        class="fixed inset-0 z-[100] flex items-center justify-center
                                               bg-black/70 p-4 backdrop-blur-sm"
                                        role="dialog"
                                        aria-modal="true"
                                        aria-labelledby="delete-status-title-{{ $item->id }}"
                                    >

                                        <div class="w-full max-w-md overflow-hidden rounded-2xl border border-white/10
                                                    bg-gray-900 shadow-2xl shadow-black/50">

                                            {{-- MODAL HEADER --}}
                                            <div class="border-b border-white/5 px-6 py-5">
                                                <div class="flex items-start gap-4">

                                                    <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl
                                                                bg-red-500/10 text-red-400 ring-1 ring-red-500/10">
                                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                                    </div>

                                                    <div class="min-w-0 flex-1">
                                                        <h3 id="delete-status-title-{{ $item->id }}" class="text-lg font-bold text-white">
                                                            Delete ticket status?
                                                        </h3>
                                                        <p class="mt-1 text-sm text-gray-500">
                                                            This action requires confirmation.
                                                        </p>
                                                    </div>

                                                    <button
                                                        type="button"
                                                        @click="showDeleteModal = false"
                                                        aria-label="Close confirmation"
                                                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg
                                                               text-gray-500 transition hover:bg-white/5 hover:text-white"
                                                    >
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>

                                                </div>
                                            </div>

                                            {{-- MODAL BODY --}}
                                            <div class="space-y-4 px-6 py-5">

                                                <p class="text-sm leading-6 text-gray-400">
                                                    Are you sure you want to delete
                                                    <strong class="font-semibold text-white">{{ $item->name }}</strong>?
                                                </p>

                                                <div class="rounded-xl border border-red-500/10 bg-red-500/5 p-4">
                                                    <div class="flex gap-3">
                                                        <i class="fa-solid fa-circle-info mt-0.5 text-red-400"></i>
                                                        <div class="text-sm leading-6 text-gray-400">
                                                            <p class="font-semibold text-gray-200">Before you continue</p>
                                                            <p class="mt-1 text-xs leading-5 text-gray-500">
                                                                This status may be referenced by tickets or workflow rules.
                                                                Make sure it is safe to remove before confirming.
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- STATUS PREVIEW --}}
                                                <div class="flex items-center gap-3 rounded-xl border border-white/5 bg-gray-950/50 p-3">
                                                    <div
                                                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border border-white/5"
                                                        style="color: {{ $statusColor }};"
                                                    >
                                                        <i class="{{ $statusIcon }}"></i>
                                                    </div>

                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-semibold text-white">{{ $item->name }}</p>
                                                        <p class="truncate text-xs text-gray-500">{{ $item->code }}</p>
                                                    </div>
                                                </div>

                                            </div>

                                            {{-- MODAL FOOTER --}}
                                            <div class="flex flex-col-reverse gap-3 border-t border-white/5 bg-gray-950/30 px-6 py-4 sm:flex-row">

                                                <button
                                                    type="button"
                                                    @click="showDeleteModal = false"
                                                    class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5
                                                           text-sm font-semibold text-gray-300 transition
                                                           hover:bg-white/10 hover:text-white"
                                                >
                                                    Cancel
                                                </button>

                                                @if($deleteRoute)
                                                    <form action="{{ route($deleteRoute, $item->id) }}" method="POST" class="w-full">
                                                        @csrf
                                                        @method('DELETE')

                                                        <button
                                                            type="submit"
                                                            class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white
                                                                   shadow-lg shadow-red-600/20 transition hover:bg-red-500
                                                                   active:scale-[0.98]"
                                                        >
                                                            <i class="fa-solid fa-trash-can mr-1.5"></i>
                                                            Yes, delete status
                                                        </button>
                                                    </form>
                                                @else
                                                    <button
                                                        type="button"
                                                        disabled
                                                        title="Add a DELETE route named ticket.status.destroy to enable deletion"
                                                        class="w-full cursor-not-allowed rounded-xl bg-red-600/30 px-4 py-2.5
                                                               text-sm font-bold text-red-200/50"
                                                    >
                                                        <i class="fa-solid fa-lock mr-1.5"></i>
                                                        Delete route not configured
                                                    </button>
                                                @endif

                                            </div>

                                        </div>
                                    </div>
                                </template>
                            @endif

                        </div>

                    @endforeach

                </div>

                {{-- =================================================
                     PAGINATION
                ================================================== --}}
                @if($tickets->hasPages())
                    <div class="mt-6 overflow-hidden rounded-2xl border border-white/5 bg-gray-900/60 p-4">
                        {{ $tickets->withQueryString()->links() }}
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