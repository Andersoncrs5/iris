@extends('layouts.base')

@section('title', 'Ticket Details | Iris')

@section('content')
@php
    $safeColor = static fn (?string $color): string =>
        preg_match('/^#[0-9a-fA-F]{6}$/', $color ?? '') ? $color : '#818cf8';

    $statusColor = $safeColor($ticket->status?->color);
    $priorityColor = $safeColor($ticket->priority?->color);

    $sourceLabels = [
        'WEB' => 'Web',
        'EMAIL' => 'Email',
        'API' => 'API',
        'CHAT' => 'Chat',
        'WHATSAPP' => 'WhatsApp',
        'PHONE' => 'Phone',
    ];

    $formatDate = static function ($value): string {
        return $value ? $value->format('M d, Y · H:i') : 'Not recorded';
    };

    $formatRating = static function (?int $rating): string {
        return $rating === null ? 'Not rated' : $rating . ' / 5';
    };

    $isOverdue = $ticket->due_at !== null
        && $ticket->due_at->isPast()
        && $ticket->resolved_at === null
        && $ticket->closed_at === null;

    $labelClass = 'text-xs font-medium uppercase tracking-wider text-gray-500';
    $valueClass = 'mt-2 break-words text-sm font-medium text-gray-100';
@endphp

<div class="relative isolate min-h-screen px-4 py-8 text-gray-100 [color-scheme:dark] sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-[radial-gradient(ellipse_at_50%_0%,rgba(99,102,241,.16),transparent_70%)]" aria-hidden="true"></div>

    <div class="mx-auto max-w-7xl">
        {{-- Breadcrumb --}}
        <nav class="mb-4 flex flex-wrap items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="transition hover:text-gray-300" aria-label="Dashboard">
                <i class="fa-solid fa-house text-xs" aria-hidden="true"></i>
            </a>
            <span aria-hidden="true">/</span>
            <span>Workspace</span>
            <span aria-hidden="true">/</span>
            <a href="{{ route('ticket.list') }}" class="transition hover:text-gray-300">Tickets</a>
            <span aria-hidden="true">/</span>
            <span class="text-indigo-400" aria-current="page">#{{ $ticket->ticket_number }}</span>
        </nav>

        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
            <div class="flex min-w-0 items-start gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-indigo-400/20 bg-indigo-500/10 text-indigo-300">
                    <i class="fa-solid fa-ticket text-xl" aria-hidden="true"></i>
                </div>

                <div class="min-w-0">
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <span class="rounded-md border border-white/10 bg-white/[0.04] px-2 py-1 font-mono text-[11px] text-gray-400">
                            TICKET #{{ $ticket->ticket_number }}
                        </span>

                        @if ($ticket->status)
                            <span class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 text-xs font-medium"
                                style="color: {{ $statusColor }}; border-color: {{ $statusColor }}40; background-color: {{ $statusColor }}15;">
                                @if ($ticket->status->icon)
                                    <i class="{{ $ticket->status->icon }} text-[10px]" aria-hidden="true"></i>
                                @else
                                    <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $statusColor }}" aria-hidden="true"></span>
                                @endif
                                {{ $ticket->status->name }}
                            </span>
                        @else
                            <span class="rounded-lg border border-gray-500/20 bg-gray-500/10 px-2.5 py-1 text-xs font-medium text-gray-400">
                                Status unavailable
                            </span>
                        @endif

                        @if ($ticket->is_escalated)
                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-rose-500/20 bg-rose-500/10 px-2.5 py-1 text-xs font-medium text-rose-300">
                                <i class="fa-solid fa-arrow-up-right-dots text-[10px]" aria-hidden="true"></i>
                                Escalated
                            </span>
                        @endif

                        @if ($ticket->trashed())
                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-red-500/20 bg-red-500/10 px-2.5 py-1 text-xs font-medium text-red-300">
                                <i class="fa-solid fa-trash-can text-[10px]" aria-hidden="true"></i>
                                Deleted
                            </span>
                        @endif

                        @if ($isOverdue)
                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-amber-500/20 bg-amber-500/10 px-2.5 py-1 text-xs font-medium text-amber-300">
                                <i class="fa-solid fa-clock text-[10px]" aria-hidden="true"></i>
                                Overdue
                            </span>
                        @endif
                    </div>

                    <h1 class="break-words text-3xl font-bold tracking-tight text-white sm:text-4xl">
                        {{ $ticket->subject }}
                    </h1>

                    <p class="mt-2 max-w-3xl whitespace-pre-line text-sm leading-6 text-gray-400">
                        {{ $ticket->description }}
                    </p>
                </div>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ route('ticket.list') }}"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-white/10 px-4 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:bg-white/[0.05] hover:text-white">
                    <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                    Back to tickets
                </a>

                @can('ticket.update')
                    @if (Route::has('ticket.edit') && ! $ticket->trashed())
                        <a href="{{ route('ticket.edit', $ticket->id) }}"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white shadow-lg shadow-indigo-950/30 transition hover:bg-indigo-500 focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/30">
                            <i class="fa-solid fa-pen text-xs" aria-hidden="true"></i>
                            Edit ticket
                        </a>
                    @endif
                @endcan
            </div>
        </div>

        {{-- Summary cards --}}
        <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ticket summary">
            <div class="rounded-2xl border border-white/10 bg-gray-950/60 p-5 shadow-lg shadow-black/10 backdrop-blur-xl">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm text-gray-400">Priority</p>
                    <i class="fa-solid fa-flag" style="color: {{ $priorityColor }}" aria-hidden="true"></i>
                </div>
                <p class="mt-3 break-words text-xl font-bold text-white">
                    {{ $ticket->priority?->name ?? 'Unavailable' }}
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    @if ($ticket->priority)
                        Level {{ $ticket->priority->level }} · {{ $ticket->priority->code }}
                    @else
                        Priority record unavailable
                    @endif
                </p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-gray-950/60 p-5 shadow-lg shadow-black/10 backdrop-blur-xl">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm text-gray-400">Category</p>
                    <i class="fa-solid fa-layer-group text-indigo-300" aria-hidden="true"></i>
                </div>
                <p class="mt-3 break-words text-xl font-bold text-white">
                    {{ $ticket->category?->name ?? 'Unavailable' }}
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    Category ID: {{ $ticket->category_id }}
                </p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-gray-950/60 p-5 shadow-lg shadow-black/10 backdrop-blur-xl">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm text-gray-400">Assigned agent</p>
                    <i class="fa-solid fa-headset text-sky-300" aria-hidden="true"></i>
                </div>
                <p class="mt-3 break-words text-xl font-bold text-white">
                    {{ $ticket->agent?->name ?? 'Unassigned' }}
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    @if ($ticket->agent)
                        {{ $ticket->agent->email }}
                    @else
                        Waiting for assignment
                    @endif
                </p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-gray-950/60 p-5 shadow-lg shadow-black/10 backdrop-blur-xl">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm text-gray-400">Satisfaction</p>
                    <i class="fa-solid fa-star text-amber-300" aria-hidden="true"></i>
                </div>
                <p class="mt-3 text-xl font-bold text-white">
                    {{ $formatRating($ticket->satisfaction_rating) }}
                </p>
                <p class="mt-1 text-xs text-gray-500">Requester feedback</p>
            </div>
        </section>

        <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-3">
            {{-- Main details --}}
            <div class="space-y-6 xl:col-span-2">
                <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10 backdrop-blur-xl">
                    <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-300">
                            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h2 class="font-semibold text-white">Ticket information</h2>
                            <p class="mt-0.5 text-xs text-gray-500">Identifiers, origin, and record metadata.</p>
                        </div>
                    </div>

                    <dl class="grid grid-cols-1 gap-x-8 gap-y-6 p-5 sm:grid-cols-2 sm:p-6">
                        <div>
                            <dt class="{{ $labelClass }}">Ticket number</dt>
                            <dd class="{{ $valueClass }} font-mono">#{{ $ticket->ticket_number }}</dd>
                        </div>
                        <div>
                            <dt class="{{ $labelClass }}">Source</dt>
                            <dd class="{{ $valueClass }}">
                                <span class="inline-flex items-center gap-2">
                                    <i class="fa-solid fa-globe text-xs text-gray-500" aria-hidden="true"></i>
                                    {{ $sourceLabels[$ticket->source] ?? $ticket->source }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="{{ $labelClass }}">Internal ID</dt>
                            <dd class="{{ $valueClass }} break-all font-mono text-xs">{{ $ticket->id }}</dd>
                        </div>
                        <div>
                            <dt class="{{ $labelClass }}">Record version</dt>
                            <dd class="{{ $valueClass }}">{{ $ticket->version }}</dd>
                        </div>
                        <div>
                            <dt class="{{ $labelClass }}">Team ID</dt>
                            <dd class="{{ $valueClass }}">{{ $ticket->team_id ?? 'No team assigned' }}</dd>
                        </div>
                        <div>
                            <dt class="{{ $labelClass }}">Escalation</dt>
                            <dd class="{{ $valueClass }}">
                                @if ($ticket->is_escalated)
                                    <span class="text-rose-300">Escalated</span>
                                @else
                                    <span class="text-gray-300">Not escalated</span>
                                @endif
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="{{ $labelClass }}">Satisfaction comment</dt>
                            <dd class="{{ $valueClass }} whitespace-pre-line">{{ $ticket->satisfaction_comment ?: 'No satisfaction comment provided.' }}</dd>
                        </div>
                    </dl>
                </section>

                {{-- People and classification --}}
                <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10 backdrop-blur-xl">
                    <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-500/10 text-violet-300">
                            <i class="fa-solid fa-users" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h2 class="font-semibold text-white">Requester and assignment</h2>
                            <p class="mt-0.5 text-xs text-gray-500">People and classification associated with this ticket.</p>
                        </div>
                    </div>

                    <div class="divide-y divide-white/[0.06]">
                        <div class="flex items-start gap-3 p-5 sm:p-6">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-indigo-500/20 bg-indigo-500/10 text-indigo-300">
                                <i class="fa-solid fa-user" aria-hidden="true"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Requester</p>
                                <p class="mt-1 break-words text-sm font-semibold text-white">{{ $ticket->user?->name ?? 'User unavailable' }}</p>
                                @if ($ticket->user?->email)
                                    <p class="mt-1 break-all text-sm text-gray-400">{{ $ticket->user->email }}</p>
                                @endif
                                <p class="mt-1 text-xs text-gray-600">User ID: {{ $ticket->user_id }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-5 sm:p-6">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-sky-500/20 bg-sky-500/10 text-sky-300">
                                <i class="fa-solid fa-headset" aria-hidden="true"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Assigned agent</p>
                                <p class="mt-1 break-words text-sm font-semibold text-white">{{ $ticket->agent?->name ?? 'Unassigned' }}</p>
                                @if ($ticket->agent?->email)
                                    <p class="mt-1 break-all text-sm text-gray-400">{{ $ticket->agent->email }}</p>
                                @else
                                    <p class="mt-1 text-sm text-gray-500">No agent has been assigned to this ticket.</p>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 sm:p-6">
                            <div>
                                <dt class="{{ $labelClass }}">Category</dt>
                                <dd class="{{ $valueClass }}">{{ $ticket->category?->name ?? 'Category unavailable' }}</dd>
                            </div>
                            <div>
                                <dt class="{{ $labelClass }}">Priority</dt>
                                <dd class="{{ $valueClass }}">
                                    <span class="inline-flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full" style="background-color: {{ $priorityColor }}" aria-hidden="true"></span>
                                        {{ $ticket->priority?->name ?? 'Priority unavailable' }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="{{ $labelClass }}">Status</dt>
                                <dd class="{{ $valueClass }}">{{ $ticket->status?->name ?? 'Status unavailable' }}</dd>
                            </div>
                            <div>
                                <dt class="{{ $labelClass }}">Priority code</dt>
                                <dd class="{{ $valueClass }} font-mono">{{ $ticket->priority?->code ?? '—' }}</dd>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Timeline --}}
            <aside class="space-y-6">
                <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10 backdrop-blur-xl">
                    <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500/10 text-sky-300">
                            <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h2 class="font-semibold text-white">Timeline</h2>
                            <p class="mt-0.5 text-xs text-gray-500">Ticket lifecycle timestamps.</p>
                        </div>
                    </div>

                    <div class="p-5">
                        <ol class="relative space-y-5 border-l border-white/10 pl-5">
                            <li class="relative">
                                <span class="absolute -left-[25px] top-1 h-2.5 w-2.5 rounded-full bg-indigo-400 ring-4 ring-[#0d0d17]" aria-hidden="true"></span>
                                <p class="text-sm font-medium text-gray-200">Ticket opened</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $formatDate($ticket->opened_at) }}</p>
                            </li>

                            <li class="relative">
                                <span class="absolute -left-[25px] top-1 h-2.5 w-2.5 rounded-full bg-sky-400 ring-4 ring-[#0d0d17]" aria-hidden="true"></span>
                                <p class="text-sm font-medium text-gray-200">First response</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $formatDate($ticket->first_response_at) }}</p>
                            </li>

                            <li class="relative">
                                <span class="absolute -left-[25px] top-1 h-2.5 w-2.5 rounded-full bg-emerald-400 ring-4 ring-[#0d0d17]" aria-hidden="true"></span>
                                <p class="text-sm font-medium text-gray-200">Resolved</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $formatDate($ticket->resolved_at) }}</p>
                            </li>

                            <li class="relative">
                                <span class="absolute -left-[25px] top-1 h-2.5 w-2.5 rounded-full bg-violet-400 ring-4 ring-[#0d0d17]" aria-hidden="true"></span>
                                <p class="text-sm font-medium text-gray-200">Closed</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $formatDate($ticket->closed_at) }}</p>
                            </li>
                        </ol>
                    </div>
                </section>

                <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10 backdrop-blur-xl">
                    <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500/10 text-amber-300">
                            <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h2 class="font-semibold text-white">Schedule</h2>
                            <p class="mt-0.5 text-xs text-gray-500">Activity and due date.</p>
                        </div>
                    </div>

                    <dl class="space-y-5 p-5">
                        <div>
                            <dt class="{{ $labelClass }}">Due date</dt>
                            <dd class="mt-2 text-sm font-medium {{ $isOverdue ? 'text-amber-300' : 'text-gray-100' }}">
                                {{ $formatDate($ticket->due_at) }}
                            </dd>
                            @if ($isOverdue)
                                <p class="mt-1 text-xs text-amber-300/80">This ticket is past its due date.</p>
                            @endif
                        </div>
                        <div>
                            <dt class="{{ $labelClass }}">Last activity</dt>
                            <dd class="{{ $valueClass }}">{{ $formatDate($ticket->last_activity_at) }}</dd>
                        </div>
                        <div>
                            <dt class="{{ $labelClass }}">Created at</dt>
                            <dd class="{{ $valueClass }}">{{ $formatDate($ticket->created_at) }}</dd>
                        </div>
                        <div>
                            <dt class="{{ $labelClass }}">Last updated</dt>
                            <dd class="{{ $valueClass }}">{{ $formatDate($ticket->updated_at) }}</dd>
                        </div>
                        @if ($ticket->trashed())
                            <div>
                                <dt class="{{ $labelClass }}">Deleted at</dt>
                                <dd class="mt-2 text-sm font-medium text-red-300">{{ $formatDate($ticket->deleted_at) }}</dd>
                            </div>
                        @endif
                    </dl>
                </section>

                <div class="rounded-2xl border border-white/10 bg-white/[0.02] p-4">
                    <p class="flex items-center gap-2 text-xs font-medium text-gray-400">
                        <i class="fa-solid fa-shield-halved text-indigo-300" aria-hidden="true"></i>
                        Ticket record
                    </p>
                    <p class="mt-2 text-xs leading-5 text-gray-500">
                        Internal ID and version are shown for support and troubleshooting purposes.
                    </p>
                    <p class="mt-3 break-all font-mono text-[11px] text-gray-400">ID: {{ $ticket->id }}</p>
                    <p class="mt-1 font-mono text-[11px] text-gray-400">Version: {{ $ticket->version }}</p>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
