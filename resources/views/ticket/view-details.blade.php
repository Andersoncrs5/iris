@extends('layouts.base')

@section('title', 'Ticket Details | Iris')

@section('content')

<style>

    [x-cloak] { display: none !important; }

</style>

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

    $currentUser = auth()->user();
    $isActiveTicket = ! $ticket->trashed();

    // Editing actions are authorized through the TicketModel policy.
    $canEditAdmin = $isActiveTicket
        && \Illuminate\Support\Facades\Route::has('ticket.edit')
        && ($currentUser?->can('update', $ticket) ?? false);

    $canEditOwn = $isActiveTicket
        && \Illuminate\Support\Facades\Route::has('ticket.own.edit')
        && ($currentUser?->can('updateOwn', $ticket) ?? false);

    // Administrative deletion takes precedence over owner deletion.
    $canDelete = $isActiveTicket
        && \Illuminate\Support\Facades\Route::has('ticket.delete')
        && ($currentUser?->can('ticket.delete') ?? false);

    $canDeleteOwn = ! $canDelete
        && $isActiveTicket
        && \Illuminate\Support\Facades\Route::has('ticket.own.destroy')
        && ($currentUser?->can('deleteOwn', $ticket) ?? false);

    $deleteUrl = $canDelete
        ? route('ticket.delete', $ticket->id)
        : ($canDeleteOwn ? route('ticket.own.destroy', $ticket->id) : null);

    // State-transition actions are displayed only when applicable to the current timestamps.
    $canClose = $isActiveTicket
        && $ticket->closed_at === null
        && \Illuminate\Support\Facades\Route::has('ticket.close')
        && ($currentUser?->can('ticket.close') ?? false);

    $canResolve = $isActiveTicket
        && $ticket->resolved_at === null
        && $ticket->closed_at === null
        && \Illuminate\Support\Facades\Route::has('ticket.resolve')
        && ($currentUser?->can('ticket.resolve') ?? false);

    $canReopen = $isActiveTicket
        && ($ticket->resolved_at !== null || $ticket->closed_at !== null)
        && \Illuminate\Support\Facades\Route::has('ticket.reopen')
        && ($currentUser?->can('ticket.reopen') ?? false);

    $hasActions = $canEditAdmin
        || $canEditOwn
        || $deleteUrl !== null
        || $canClose
        || $canResolve
        || $canReopen;

    $actionOptions = [
        'deleteTicket' => [
            'url' => $deleteUrl,
            'method' => 'DELETE',
            'title' => 'Delete ticket #' . $ticket->ticket_number . '?',
            'description' => '"' . \Illuminate\Support\Str::limit($ticket->subject, 80) . '" will be removed from the ticket list.',
            'submitLabel' => 'Yes, delete',
            'icon' => 'fa-solid fa-trash-can',
            'danger' => true,
        ],
        'close' => [
            'url' => $canClose ? route('ticket.close', $ticket->id) : null,
            'method' => 'PATCH',
            'title' => 'Close ticket #' . $ticket->ticket_number . '?',
            'description' => 'This will mark the ticket as closed.',
            'submitLabel' => 'Close ticket',
            'icon' => 'fa-solid fa-lock',
            'danger' => false,
        ],
        'resolve' => [
            'url' => $canResolve ? route('ticket.resolve', $ticket->id) : null,
            'method' => 'PATCH',
            'title' => 'Resolve ticket #' . $ticket->ticket_number . '?',
            'description' => 'This will mark the ticket as resolved.',
            'submitLabel' => 'Resolve ticket',
            'icon' => 'fa-solid fa-check',
            'danger' => false,
        ],
        'reopen' => [
            'url' => $canReopen ? route('ticket.reopen', $ticket->id) : null,
            'method' => 'PATCH',
            'title' => 'Reopen ticket #' . $ticket->ticket_number . '?',
            'description' => 'This will return the ticket to an active workflow.',
            'submitLabel' => 'Reopen ticket',
            'icon' => 'fa-solid fa-arrow-rotate-left',
            'danger' => false,
        ],
    ];

    $labelClass = 'text-xs font-medium uppercase tracking-wider text-gray-500';
    $valueClass = 'mt-2 break-words text-sm font-medium text-gray-100';
@endphp

<div class="relative isolate min-h-screen px-4 py-8 text-gray-100 [color-scheme:dark] sm:px-6 lg:px-8" x-data="{
        actionsOpen: false,
        confirmAction: null,
        actionOptions: @js($actionOptions),
        requestAction(key) {
            const action = this.actionOptions[key];
            if (action && action.url) {
                this.confirmAction = action;
                this.actionsOpen = false;
            }
        }
    }"
    @keydown.escape.window="confirmAction = null; actionsOpen = false">

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

        {{-- Feedback --}}

        @if (session('success'))

            <div x-data="{ show: true }" x-show="show" x-transition.opacity role="status" class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">

                <i class="fa-solid fa-circle-check mt-0.5" aria-hidden="true"></i>

                <p class="flex-1">{{ session('success') }}</p>

                <button type="button" @click="show = false" aria-label="Dismiss" class="text-emerald-300/60 transition hover:text-emerald-200"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>

            </div>

        @endif

        @if (session('error'))

            <div x-data="{ show: true }" x-show="show" x-transition.opacity role="alert" class="mb-5 flex items-start gap-3 rounded-xl border border-red-500/25 bg-red-500/10 p-4 text-sm text-red-300">

                <i class="fa-solid fa-circle-exclamation mt-0.5" aria-hidden="true"></i>

                <p class="flex-1">{{ session('error') }}</p>

                <button type="button" @click="show = false" aria-label="Dismiss" class="text-red-300/60 transition hover:text-red-200"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>

            </div>

        @endif

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

            <div class="flex shrink-0 items-center gap-2">
                <a href="{{ route('ticket.list') }}"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-white/10 px-4 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:bg-white/[0.05] hover:text-white">
                    <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                    Back to tickets
                </a>

                @if ($hasActions)
                    <div class="relative" @click.outside="actionsOpen = false">
                        <button type="button"
                            @click="actionsOpen = !actionsOpen"
                            :aria-expanded="actionsOpen.toString()"
                            aria-haspopup="menu"
                            aria-label="Ticket actions"
                            title="Ticket actions"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/[0.03] text-gray-300 transition hover:border-indigo-400/30 hover:bg-indigo-500/10 hover:text-indigo-300 focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/20">
                            <i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i>
                        </button>

                        <div x-show="actionsOpen" x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="absolute right-0 top-full z-40 mt-2 w-64 origin-top-right rounded-xl border border-white/10 bg-[#11111c] p-1.5 shadow-2xl shadow-black/50"
                            role="menu" aria-label="Ticket actions">
                            <div class="border-b border-white/[0.08] px-3 py-2.5">
                                <p class="text-xs font-semibold text-white">Ticket #{{ $ticket->ticket_number }}</p>
                                <p class="mt-1 truncate text-[11px] text-gray-500">{{ $ticket->subject }}</p>
                            </div>

                            @if ($canEditAdmin)
                                <a href="{{ route('ticket.edit', $ticket->id) }}" role="menuitem"
                                    @click="actionsOpen = false"
                                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-300 transition hover:bg-white/[0.06] hover:text-white">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-400"><i class="fa-solid fa-pen-to-square text-xs" aria-hidden="true"></i></span>
                                    <span><span class="block font-medium">Edit ticket</span><span class="mt-0.5 block text-[11px] text-gray-500">Manage support fields</span></span>
                                </a>
                            @endif

                            @if ($canEditOwn)
                                <a href="{{ route('ticket.own.edit', $ticket->id) }}" role="menuitem"
                                    @click="actionsOpen = false"
                                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-300 transition hover:bg-white/[0.06] hover:text-white">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-violet-500/10 text-violet-400"><i class="fa-solid fa-pen text-xs" aria-hidden="true"></i></span>
                                    <span><span class="block font-medium">Edit my ticket</span><span class="mt-0.5 block text-[11px] text-gray-500">Edit your own request</span></span>
                                </a>
                            @endif

                            @if ($canResolve)
                                <button type="button" role="menuitem" @click="requestAction('resolve')"
                                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm text-gray-300 transition hover:bg-emerald-500/10 hover:text-emerald-200">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400"><i class="fa-solid fa-check text-xs" aria-hidden="true"></i></span>
                                    <span><span class="block font-medium">Resolve ticket</span><span class="mt-0.5 block text-[11px] text-gray-500">Mark work as completed</span></span>
                                </button>
                            @endif

                            @if ($canClose)
                                <button type="button" role="menuitem" @click="requestAction('close')"
                                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm text-gray-300 transition hover:bg-amber-500/10 hover:text-amber-200">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-400"><i class="fa-solid fa-lock text-xs" aria-hidden="true"></i></span>
                                    <span><span class="block font-medium">Close ticket</span><span class="mt-0.5 block text-[11px] text-gray-500">Finish the ticket lifecycle</span></span>
                                </button>
                            @endif

                            @if ($canReopen)
                                <button type="button" role="menuitem" @click="requestAction('reopen')"
                                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm text-gray-300 transition hover:bg-sky-500/10 hover:text-sky-200">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-sky-500/10 text-sky-400"><i class="fa-solid fa-arrow-rotate-left text-xs" aria-hidden="true"></i></span>
                                    <span><span class="block font-medium">Reopen ticket</span><span class="mt-0.5 block text-[11px] text-gray-500">Return to active workflow</span></span>
                                </button>
                            @endif
                            
                            @can('ticket.message.create')
                                <a
                                    href="{{ route('ticket.message.create', [
                                        'id' => $ticket->id,
                                        'parentId' => $ticket->parent_id,
                                    ]) }}"
                                    role="menuitem"
                                    class="group flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-gray-300 transition hover:bg-indigo-500/10 hover:text-white"
                                >
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400 transition group-hover:bg-indigo-500/20">
                                        <i class="fa-solid fa-reply text-sm" aria-hidden="true"></i>
                                    </span>

                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-semibold">
                                            Reply to message
                                        </span>

                                        <span class="mt-1 block text-xs text-gray-500 group-hover:text-gray-400">
                                            Respond to this specific comment
                                        </span>
                                    </span>

                                    <i class="fa-solid fa-chevron-right text-[10px] text-gray-600 group-hover:text-indigo-400" aria-hidden="true"></i>
                                </a>
                            @endcan

                            @if ($deleteUrl)
                                <div class="my-1 border-t border-white/[0.08]"></div>
                                <button type="button" role="menuitem" @click="requestAction('deleteTicket')"
                                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm text-red-300 transition hover:bg-red-500/10 hover:text-red-200">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-500/10 text-red-400"><i class="fa-solid fa-trash-can text-xs" aria-hidden="true"></i></span>
                                    <span><span class="block font-medium">{{ $canDeleteOwn ? 'Delete my ticket' : 'Delete ticket' }}</span><span class="mt-0.5 block text-[11px] text-red-300/60">Remove this ticket</span></span>
                                </button>
                            @endif
                            
                        </div>
                    </div>
                @endif
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

    {{-- Confirmation modal for deletion and ticket lifecycle transitions. --}}
    <template x-teleport="body">
        <div x-show="confirmAction !== null" x-cloak
            @keydown.escape.window="confirmAction = null"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog" aria-modal="true" aria-labelledby="ticket-action-title">
            <div x-show="confirmAction !== null" x-transition.opacity
                @click="confirmAction = null"
                class="absolute inset-0 bg-black/70 backdrop-blur-sm" aria-hidden="true"></div>

            <div x-show="confirmAction !== null"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative w-full max-w-md rounded-2xl border border-white/10 bg-[#11111c] p-6 text-gray-100 shadow-2xl shadow-black/60">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl border"
                    :class="confirmAction?.danger ? 'border-red-500/20 bg-red-500/10 text-red-300' : 'border-indigo-500/20 bg-indigo-500/10 text-indigo-300'">
                    <i :class="confirmAction?.icon" aria-hidden="true"></i>
                </div>

                <h2 id="ticket-action-title" class="mt-4 text-lg font-semibold text-white" x-text="confirmAction?.title ?? ''"></h2>
                <p class="mt-2 text-sm leading-6 text-gray-400" x-text="confirmAction?.description ?? ''"></p>

                <form method="POST" :action="confirmAction?.url ?? ''" class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" :value="confirmAction?.method ?? 'POST'">

                    <button type="button" @click="confirmAction = null"
                        class="inline-flex h-10 items-center justify-center rounded-xl border border-white/10 px-4 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:bg-white/[0.05] hover:text-white focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/20">
                        Cancel
                    </button>

                    <button type="submit"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl px-4 text-sm font-semibold text-white transition focus:outline-none focus-visible:ring-4"
                        :class="confirmAction?.danger ? 'bg-red-600 hover:bg-red-500 focus-visible:ring-red-500/40' : 'bg-indigo-600 hover:bg-indigo-500 focus-visible:ring-indigo-500/40'">
                        <i :class="confirmAction?.icon" class="text-xs" aria-hidden="true"></i>
                        <span x-text="confirmAction?.submitLabel ?? 'Confirm'"></span>
                    </button>
                </form>
            </div>
        </div>
    </template>
</div>

@endsection
