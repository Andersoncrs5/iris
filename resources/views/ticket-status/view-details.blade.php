@extends('layouts.base')

@section('title', 'Ticket Status Details - Iris App')

@section('content')

@php
    $statusColor = $ticket->color ?: '#6366f1';
    $statusIcon = $ticket->icon ?: 'fa-solid fa-circle-half-stroke';
    $isArchived = $ticket->trashed();
@endphp

<div class="mx-auto w-full max-w-7xl space-y-6">

    {{-- =========================================================
         PAGE HEADER
    ========================================================== --}}
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

        <div>
            <div class="mb-3 flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400 ring-1 ring-indigo-500/20">
                    <i class="fa-solid fa-circle-half-stroke"></i>
                </span>
                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-400">
                    Ticket Management
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                    Status Details
                </h1>

                @if($isArchived)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-red-500/20 bg-red-500/10 px-3 py-1 text-xs font-bold text-red-300">
                        <i class="fa-solid fa-box-archive"></i>
                        Archived
                    </span>
                @elseif($ticket->is_active)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        Active
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-500/20 bg-gray-500/10 px-3 py-1 text-xs font-bold text-gray-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-gray-500"></span>
                        Inactive
                    </span>
                @endif
            </div>

            <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                Inspect the configuration, workflow behavior and audit information for this ticket status.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ route('ticket.status.list') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-gray-300 transition hover:bg-white/10 hover:text-white active:scale-[0.98]"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Back to statuses
            </a>

            @if(Route::has('ticket.status.edit'))
                <a
                    href="{{ route('ticket.status.edit', $ticket->id) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:-translate-y-0.5 hover:bg-indigo-500 active:translate-y-0"
                >
                    <i class="fa-solid fa-pen-to-square"></i>
                    Edit status
                </a>
            @endif
        </div>

    </div>


    {{-- =========================================================
         HERO / STATUS PREVIEW
    ========================================================== --}}
    <section class="relative overflow-hidden rounded-3xl border border-white/5 bg-gray-900/80 shadow-2xl shadow-black/10">
        <div class="absolute inset-x-0 top-0 h-1" style="background-color: {{ $statusColor }};"></div>
        <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full opacity-[0.08] blur-3xl" style="background-color: {{ $statusColor }};"></div>

        <div class="relative grid grid-cols-1 gap-8 p-6 sm:p-8 lg:grid-cols-[minmax(0,1fr)_280px] lg:items-center">

            <div class="flex min-w-0 items-start gap-5">
                <div
                    class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-gray-950/70 shadow-lg"
                    style="color: {{ $statusColor }};"
                >
                    <i class="{{ $statusIcon }} text-2xl"></i>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">
                        Status name
                    </p>
                    <h2 class="break-words text-2xl font-black tracking-tight text-white sm:text-3xl">
                        {{ $ticket->name }}
                    </h2>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <code class="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-gray-950/70 px-3 py-1.5 font-mono text-xs font-semibold text-gray-300">
                            <i class="fa-solid fa-code text-gray-500"></i>
                            {{ $ticket->code }}
                        </code>

                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-white/5 bg-white/[0.03] px-3 py-1.5 text-xs text-gray-400">
                            <i class="fa-solid fa-fingerprint text-gray-500"></i>
                            ID {{ $ticket->id }}
                        </span>
                    </div>

                    <p class="mt-4 max-w-3xl text-sm leading-6 text-gray-400">
                        {{ $ticket->description ?: 'No description has been provided for this status.' }}
                    </p>
                </div>
            </div>

            <div class="rounded-2xl border border-white/5 bg-gray-950/50 p-5">
                <div class="mb-4 flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Appearance</span>
                    <i class="fa-solid fa-palette text-gray-600"></i>
                </div>

                <div class="flex items-center gap-3">
                    <span class="h-10 w-10 rounded-xl border border-white/10 shadow-inner" style="background-color: {{ $statusColor }};"></span>
                    <div class="min-w-0">
                        <p class="font-mono text-sm font-bold text-white">{{ $ticket->color ?: 'Default color' }}</p>
                        <p class="mt-1 truncate text-xs text-gray-500">{{ $statusIcon }}</p>
                    </div>
                </div>

                <div class="mt-5 h-1.5 overflow-hidden rounded-full bg-white/5">
                    <div class="h-full w-full rounded-full" style="background-color: {{ $statusColor }};"></div>
                </div>
            </div>

        </div>
    </section>


    {{-- =========================================================
         QUICK OVERVIEW
    ========================================================== --}}
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-2xl border border-white/5 bg-gray-900/70 p-5 shadow-xl shadow-black/5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Display order</p>
                    <p class="mt-2 text-2xl font-black text-white">{{ $ticket->display_order }}</p>
                    <p class="mt-1 text-xs text-gray-600">Position in the workflow</p>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400">
                    <i class="fa-solid fa-list-ol"></i>
                </span>
            </div>
        </div>

        <div class="rounded-2xl border border-white/5 bg-gray-900/70 p-5 shadow-xl shadow-black/5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Lifecycle role</p>
                    <p class="mt-2 text-xl font-black text-white">
                        @if($ticket->is_initial && $ticket->is_final)
                            Initial / Final
                        @elseif($ticket->is_initial)
                            Initial
                        @elseif($ticket->is_final)
                            Final
                        @else
                            Intermediate
                        @endif
                    </p>
                    <p class="mt-1 text-xs text-gray-600">Position in ticket lifecycle</p>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-500/10 text-violet-400">
                    <i class="fa-solid fa-route"></i>
                </span>
            </div>
        </div>

        <div class="rounded-2xl border border-white/5 bg-gray-900/70 p-5 shadow-xl shadow-black/5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">SLA timer</p>
                    <p class="mt-2 text-xl font-black {{ $ticket->stops_sla ? 'text-amber-300' : 'text-white' }}">
                        {{ $ticket->stops_sla ? 'Paused' : 'Continues' }}
                    </p>
                    <p class="mt-1 text-xs text-gray-600">While in this status</p>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                    <i class="fa-solid fa-stopwatch"></i>
                </span>
            </div>
        </div>

        <div class="rounded-2xl border border-white/5 bg-gray-900/70 p-5 shadow-xl shadow-black/5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Version</p>
                    <p class="mt-2 text-2xl font-black text-white">v{{ $ticket->version }}</p>
                    <p class="mt-1 text-xs text-gray-600">Concurrency revision</p>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-500/10 text-sky-400">
                    <i class="fa-solid fa-code-branch"></i>
                </span>
            </div>
        </div>

    </section>


    {{-- =========================================================
         DETAILS GRID
    ========================================================== --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)]">

        {{-- WORKFLOW CONFIGURATION --}}
        <section class="overflow-hidden rounded-2xl border border-white/5 bg-gray-900/80 shadow-xl shadow-black/5">
            <div class="border-b border-white/5 px-6 py-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-500/10 text-violet-400">
                        <i class="fa-solid fa-sliders"></i>
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-white">Workflow configuration</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Rules that affect ticket transitions and handling.</p>
                    </div>
                </div>
            </div>

            <div class="space-y-3 p-6">

                {{-- INITIAL STATUS --}}
                <div class="flex items-start gap-3 rounded-xl border border-white/5 bg-gray-950/40 p-4">
                    <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg {{ $ticket->is_initial ? 'bg-indigo-500/10 text-indigo-400' : 'bg-white/5 text-gray-600' }}">
                        <i class="fa-solid fa-play"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-white">Initial status</p>
                        <p class="mt-1 text-xs leading-5 text-gray-500">Determines whether new tickets can begin their lifecycle in this state.</p>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $ticket->is_initial ? 'border border-indigo-500/20 bg-indigo-500/10 text-indigo-300' : 'border border-white/5 bg-white/[0.03] text-gray-500' }}">
                        {{ $ticket->is_initial ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>

                {{-- FINAL STATUS --}}
                <div class="flex items-start gap-3 rounded-xl border border-white/5 bg-gray-950/40 p-4">
                    <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg {{ $ticket->is_final ? 'bg-emerald-500/10 text-emerald-400' : 'bg-white/5 text-gray-600' }}">
                        <i class="fa-solid fa-flag-checkered"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-white">Final status</p>
                        <p class="mt-1 text-xs leading-5 text-gray-500">Indicates whether a ticket is considered to have reached the end of its lifecycle.</p>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $ticket->is_final ? 'border border-emerald-500/20 bg-emerald-500/10 text-emerald-300' : 'border border-white/5 bg-white/[0.03] text-gray-500' }}">
                        {{ $ticket->is_final ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>

                {{-- SLA --}}
                <div class="flex items-start gap-3 rounded-xl border border-white/5 bg-gray-950/40 p-4">
                    <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg {{ $ticket->stops_sla ? 'bg-amber-500/10 text-amber-400' : 'bg-white/5 text-gray-600' }}">
                        <i class="fa-solid fa-stopwatch"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-white">Stop SLA timer</p>
                        <p class="mt-1 text-xs leading-5 text-gray-500">Controls whether SLA time is paused while the ticket remains in this state.</p>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $ticket->stops_sla ? 'border border-amber-500/20 bg-amber-500/10 text-amber-300' : 'border border-white/5 bg-white/[0.03] text-gray-500' }}">
                        {{ $ticket->stops_sla ? 'Paused' : 'Running' }}
                    </span>
                </div>

                {{-- COMMENT REQUIRED --}}
                <div class="flex items-start gap-3 rounded-xl border border-white/5 bg-gray-950/40 p-4">
                    <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg {{ $ticket->requires_comment ? 'bg-sky-500/10 text-sky-400' : 'bg-white/5 text-gray-600' }}">
                        <i class="fa-solid fa-comment-dots"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-white">Comment requirement</p>
                        <p class="mt-1 text-xs leading-5 text-gray-500">Indicates whether entering this state should require a comment.</p>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $ticket->requires_comment ? 'border border-sky-500/20 bg-sky-500/10 text-sky-300' : 'border border-white/5 bg-white/[0.03] text-gray-500' }}">
                        {{ $ticket->requires_comment ? 'Required' : 'Optional' }}
                    </span>
                </div>

                {{-- ACTIVE --}}
                <div class="flex items-start gap-3 rounded-xl border border-white/5 bg-gray-950/40 p-4">
                    <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg {{ $ticket->is_active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-gray-500/10 text-gray-500' }}">
                        <i class="fa-solid fa-power-off"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-white">Availability</p>
                        <p class="mt-1 text-xs leading-5 text-gray-500">Inactive statuses are not intended to be assigned to new tickets.</p>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $ticket->is_active ? 'border border-emerald-500/20 bg-emerald-500/10 text-emerald-300' : 'border border-gray-500/20 bg-gray-500/10 text-gray-400' }}">
                        {{ $ticket->is_active ? 'Available' : 'Unavailable' }}
                    </span>
                </div>

            </div>
        </section>


        {{-- RECORD INFORMATION --}}
        <div class="space-y-6">

            <section class="overflow-hidden rounded-2xl border border-white/5 bg-gray-900/80 shadow-xl shadow-black/5">
                <div class="border-b border-white/5 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400">
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
                        <dd class="break-all text-right font-mono text-xs font-semibold text-gray-300">{{ $ticket->id }}</dd>
                    </div>

                    <div class="flex items-start justify-between gap-4 py-4">
                        <dt class="text-xs text-gray-500">Version</dt>
                        <dd class="text-right text-xs font-semibold text-gray-300">{{ $ticket->version }}</dd>
                    </div>

                    <div class="flex items-start justify-between gap-4 py-4">
                        <dt class="text-xs text-gray-500">Created at</dt>
                        <dd class="text-right text-xs font-semibold text-gray-300">
                            {{ $ticket->created_at?->format('d/m/Y H:i:s') ?? '—' }}
                        </dd>
                    </div>

                    <div class="flex items-start justify-between gap-4 py-4">
                        <dt class="text-xs text-gray-500">Last updated</dt>
                        <dd class="text-right text-xs font-semibold text-gray-300">
                            {{ $ticket->updated_at?->format('d/m/Y H:i:s') ?? '—' }}
                        </dd>
                    </div>

                    @if($ticket->deleted_at)
                        <div class="flex items-start justify-between gap-4 py-4">
                            <dt class="text-xs text-gray-500">Archived at</dt>
                            <dd class="text-right text-xs font-semibold text-red-300">
                                {{ $ticket->deleted_at->format('d/m/Y H:i:s') }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </section>


            {{-- WORKFLOW SUMMARY --}}
            <section class="rounded-2xl border border-white/5 bg-gray-900/80 p-6 shadow-xl shadow-black/5">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-violet-500/10 text-violet-400">
                        <i class="fa-solid fa-diagram-project"></i>
                    </span>
                    <div>
                        <h2 class="text-sm font-bold text-white">Workflow summary</h2>
                        <p class="mt-1 text-xs leading-5 text-gray-500">
                            This status is configured as
                            @if($ticket->is_initial && $ticket->is_final)
                                both an initial and a final state.
                            @elseif($ticket->is_initial)
                                an initial state in the ticket lifecycle.
                            @elseif($ticket->is_final)
                                a final state in the ticket lifecycle.
                            @else
                                an intermediate state in the ticket lifecycle.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="mt-5 rounded-xl border border-white/5 bg-gray-950/50 p-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg" style="background-color: {{ $statusColor }}20; color: {{ $statusColor }};">
                            <i class="{{ $statusIcon }}"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-white">{{ $ticket->name }}</p>
                            <p class="mt-1 truncate font-mono text-[11px] text-gray-500">{{ $ticket->code }}</p>
                        </div>
                        <i class="fa-solid fa-circle-check {{ $ticket->is_active ? 'text-emerald-400' : 'text-gray-600' }}"></i>
                    </div>
                </div>

                <p class="mt-4 text-xs leading-5 text-gray-600">
                    Changes to status configuration may affect the ticket workflow. Review dependent business rules before changing lifecycle settings.
                </p>
            </section>

        </div>

    </div>


    {{-- FOOTER --}}
    <div class="flex flex-col-reverse gap-3 border-t border-white/5 pt-5 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-gray-600">
            <i class="fa-solid fa-shield-halved mr-1.5"></i>
            Ticket status configuration details
        </p>

        <div class="flex flex-wrap justify-end gap-3">
            <a
                href="{{ route('ticket.status.list') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-gray-300 transition hover:bg-white/10 hover:text-white"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Back to list
            </a>

            @if(Route::has('ticket.status.update'))
                <a
                    href="{{ route('ticket.status.update', $ticket->id) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500"
                >
                    <i class="fa-solid fa-pen-to-square"></i>
                    Edit status
                </a>
            @endif
        </div>
    </div>

</div>

@endsection
