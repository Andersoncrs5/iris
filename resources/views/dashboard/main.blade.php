@extends('layouts.base')

@section('title', 'Dashboard | Iris')

@section('content')
@php
    $safeColor = static fn (?string $color): string =>
        preg_match('/^#[0-9a-fA-F]{6}$/', $color ?? '') ? $color : '#818cf8';

    $cardStyles = [
        'indigo' => ['icon' => 'border-indigo-500/20 bg-indigo-500/10 text-indigo-300', 'value' => 'text-indigo-200'],
        'sky' => ['icon' => 'border-sky-500/20 bg-sky-500/10 text-sky-300', 'value' => 'text-sky-200'],
        'amber' => ['icon' => 'border-amber-500/20 bg-amber-500/10 text-amber-300', 'value' => 'text-amber-200'],
        'emerald' => ['icon' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300', 'value' => 'text-emerald-200'],
    ];

    $sourceLabels = [
        'WEB' => 'Web',
        'EMAIL' => 'Email',
        'API' => 'API',
        'CHAT' => 'Chat',
        'WHATSAPP' => 'WhatsApp',
        'PHONE' => 'Phone',
    ];

    $filterKeys = [
        'ticket_number', 'subject', 'description', 'agent_id', 'category_id',
        'status_id', 'priority_id', 'source', 'is_escalated',
        'satisfaction_rating_min', 'satisfaction_rating_max', 'satisfaction_comment',
        'opened_at_from', 'opened_at_to', 'last_activity_at_from', 'last_activity_at_to',
        'first_response_at_from', 'first_response_at_to', 'resolved_at_from', 'resolved_at_to',
        'closed_at_from', 'closed_at_to', 'due_at_from', 'due_at_to',
        'created_at_from', 'created_at_to', 'updated_at_from', 'updated_at_to',
    ];

    $activeFilterCount = collect($filterKeys)
        ->filter(fn (string $key) => request()->filled($key))
        ->count();

    $fieldClass = 'h-10 w-full rounded-xl border border-white/10 bg-gray-900/80 px-3 text-sm text-gray-200 placeholder:text-gray-600 transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/10';
    $labelClass = 'mb-1.5 block text-xs font-medium text-gray-400';
    $selectClass = $fieldClass . ' appearance-none [&>option]:bg-gray-900';
@endphp

<style>
    [x-cloak] { display: none !important; }
</style>

<div class="relative isolate min-h-screen w-full px-4 py-8 text-gray-100 [color-scheme:dark] sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-[radial-gradient(ellipse_at_50%_0%,rgba(99,102,241,.16),transparent_70%)]" aria-hidden="true"></div>

    {{-- Content uses approximately 85% of the desktop viewport. --}}
    <div class="mx-auto w-full space-y-7 lg:w-[85%] lg:max-w-[1800px]">
        {{-- Header --}}
        <section class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="min-w-0">
                <nav class="mb-3 flex items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
                    <a href="{{ route('dashboard') }}" class="transition hover:text-gray-300" aria-label="Dashboard">
                        <i class="fa-solid fa-house text-xs" aria-hidden="true"></i>
                    </a>
                    <span aria-hidden="true">/</span>
                    <span class="text-indigo-300" aria-current="page">Dashboard</span>
                </nav>

                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-300">Your workspace</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    Welcome back, {{ auth()->user()->name }}
                </h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                    Monitor your requests, filter your tickets, and find the information you need in one place.
                </p>
            </div>

            @can('ticket.create')
                <a href="{{ route('ticket.create') }}"
                   class="group inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 text-sm font-semibold text-white shadow-lg shadow-indigo-950/40 transition hover:from-indigo-500 hover:to-violet-500 focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/40 active:scale-[0.99]">
                    <i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>
                    Create ticket
                </a>
            @endcan
        </section>

        {{-- Feedback --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition.opacity role="status" class="flex items-start gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">
                <i class="fa-solid fa-circle-check mt-0.5" aria-hidden="true"></i>
                <p class="flex-1">{{ session('success') }}</p>
                <button type="button" @click="show = false" aria-label="Dismiss" class="text-emerald-300/60 transition hover:text-emerald-200">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" x-transition.opacity role="alert" class="flex items-start gap-3 rounded-xl border border-red-500/25 bg-red-500/10 p-4 text-sm text-red-300">
                <i class="fa-solid fa-circle-exclamation mt-0.5" aria-hidden="true"></i>
                <p class="flex-1">{{ session('error') }}</p>
                <button type="button" @click="show = false" aria-label="Dismiss" class="text-red-300/60 transition hover:text-red-200">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
        @endif

        {{-- MyTicketDashboardDTO metrics stay above the filter/results workspace. --}}
        <section aria-label="My ticket summary" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($dashboard->cards() as $card)
                @php $palette = $cardStyles[$card['color']] ?? $cardStyles['indigo']; @endphp
                <article class="rounded-2xl border border-white/10 bg-gray-950/60 p-5 shadow-xl shadow-black/10 backdrop-blur-xl transition hover:border-white/[0.16]">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-400">{{ $card['label'] }}</p>
                            <p class="mt-3 text-3xl font-bold tracking-tight tabular-nums {{ $palette['value'] }}">
                                {{ number_format($card['value']) }}
                            </p>
                        </div>
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border {{ $palette['icon'] }}">
                            <i class="{{ $card['icon'] }} text-base" aria-hidden="true"></i>
                        </div>
                    </div>
                    <p class="mt-3 text-xs leading-5 text-gray-500">{{ $card['description'] }}</p>
                </article>
            @endforeach
        </section>

        {{-- Compact attention indicators from MyTicketDashboardDTO. --}}
        @if ($dashboard->hasTicketsRequiringAttention())
            <section class="flex flex-col gap-3 rounded-2xl border border-amber-500/20 bg-amber-500/[0.045] p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5" aria-label="Ticket attention summary">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10 text-amber-300">
                        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-white">Some tickets need your attention</h2>
                        <p class="mt-1 text-xs text-gray-400">Review overdue or escalated requests below.</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 text-xs font-semibold">
                    @if ($dashboard->overdueTickets > 0)
                        <span class="inline-flex items-center gap-2 rounded-lg border border-amber-500/20 bg-amber-500/10 px-3 py-2 text-amber-200">
                            <i class="fa-solid fa-clock" aria-hidden="true"></i>
                            {{ $dashboard->overdueTickets }} overdue
                        </span>
                    @endif
                    @if ($dashboard->escalatedTickets > 0)
                        <span class="inline-flex items-center gap-2 rounded-lg border border-rose-500/20 bg-rose-500/10 px-3 py-2 text-rose-200">
                            <i class="fa-solid fa-arrow-up-right-dots" aria-hidden="true"></i>
                            {{ $dashboard->escalatedTickets }} escalated
                        </span>
                    @endif
                </div>
            </section>
        @endif

        {{-- Workspace: filters take 30%, ticket results take 70%. --}}
        <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-10 lg:gap-6">
            {{-- Left column: filters (30%) --}}
            <aside class="lg:col-span-3">
                <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/70 shadow-xl shadow-black/20 lg:sticky lg:top-5">
                    <div class="flex items-center justify-between gap-3 border-b border-white/[0.08] px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg border border-indigo-500/20 bg-indigo-500/10 text-indigo-300">
                                <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h2 class="font-semibold text-white">Filter tickets</h2>
                                <p class="mt-0.5 text-xs text-gray-500">Narrow down your requests.</p>
                            </div>
                        </div>
                        @if ($activeFilterCount > 0)
                            <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-indigo-500/20 px-2 text-xs font-semibold text-indigo-200">
                                {{ $activeFilterCount }}
                            </span>
                        @endif
                    </div>

                    <form action="{{ route('dashboard') }}" method="GET" class="space-y-5 p-5">
                        {{-- Preserve the current page size and expose sorting as part of the filter system. --}}

                        <div>
                            <label for="ticket_number" class="{{ $labelClass }}">Ticket number</label>
                            <div class="relative">
                                <i class="fa-solid fa-hashtag pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-600" aria-hidden="true"></i>
                                <input type="number" min="1" step="1" name="ticket_number" id="ticket_number" value="{{ request('ticket_number') }}" placeholder="e.g. 1042" class="{{ $fieldClass }} pl-8">
                            </div>
                        </div>

                        <div>
                            <label for="subject" class="{{ $labelClass }}">Subject</label>
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-600" aria-hidden="true"></i>
                                <input type="text" name="subject" id="subject" value="{{ request('subject') }}" placeholder="Search subject..." class="{{ $fieldClass }} pl-8">
                            </div>
                            <label class="mt-2 flex items-center gap-2 text-xs text-gray-500">
                                <input type="hidden" name="subject_ignore_case" value="0">
                                <input type="checkbox" name="subject_ignore_case" value="1" @checked(request('subject_ignore_case', '1') === '1') class="rounded border-gray-600 bg-gray-800 text-indigo-500 focus:ring-indigo-500/30">
                                Ignore letter case
                            </label>
                        </div>

                        <div>
                            <label for="category_id" class="{{ $labelClass }}">Category</label>
                            <select name="category_id" id="category_id" class="{{ $selectClass }}">
                                <option value="">All categories</option>
                                @foreach ($filterCategories as $category)
                                    <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="status_id" class="{{ $labelClass }}">Status</label>
                            <select name="status_id" id="status_id" class="{{ $selectClass }}">
                                <option value="">All statuses</option>
                                @foreach ($filterStatuses as $status)
                                    <option value="{{ $status->id }}" @selected((string) request('status_id') === (string) $status->id)>{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="priority_id" class="{{ $labelClass }}">Priority</label>
                            <select name="priority_id" id="priority_id" class="{{ $selectClass }}">
                                <option value="">All priorities</option>
                                @foreach ($filterPriorities as $priority)
                                    <option value="{{ $priority->id }}" @selected((string) request('priority_id') === (string) $priority->id)>{{ $priority->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="agent_id" class="{{ $labelClass }}">Assigned agent ID</label>
                            <input type="number" min="1" step="1" name="agent_id" id="agent_id" value="{{ request('agent_id') }}" placeholder="Any agent" class="{{ $fieldClass }}">
                        </div>

                        <div>
                            <label for="source" class="{{ $labelClass }}">Source</label>
                            <select name="source" id="source" class="{{ $selectClass }}">
                                <option value="">All sources</option>
                                @foreach ($sourceLabels as $sourceValue => $sourceLabel)
                                    <option value="{{ $sourceValue }}" @selected(request('source') === $sourceValue)>{{ $sourceLabel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="is_escalated" class="{{ $labelClass }}">Escalation</label>
                            <select name="is_escalated" id="is_escalated" class="{{ $selectClass }}">
                                <option value="">All tickets</option>
                                <option value="1" @selected(request('is_escalated') === '1')>Escalated only</option>
                                <option value="0" @selected(request('is_escalated') === '0')>Not escalated</option>
                            </select>
                        </div>

                        <div class="border-t border-white/[0.08] pt-4">
                            <h3 class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                <i class="fa-regular fa-calendar text-indigo-300" aria-hidden="true"></i>
                                Created date
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <label for="created_at_from" class="{{ $labelClass }}">From</label>
                                    <input type="date" name="created_at_from" id="created_at_from" value="{{ request('created_at_from') }}" class="{{ $fieldClass }}">
                                </div>
                                <div>
                                    <label for="created_at_to" class="{{ $labelClass }}">To</label>
                                    <input type="date" name="created_at_to" id="created_at_to" value="{{ request('created_at_to') }}" class="{{ $fieldClass }}">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="last_activity_at_from" class="{{ $labelClass }}">Last activity from</label>
                            <input type="date" name="last_activity_at_from" id="last_activity_at_from" value="{{ request('last_activity_at_from') }}" class="{{ $fieldClass }}">
                        </div>
                        <div>
                            <label for="last_activity_at_to" class="{{ $labelClass }}">Last activity to</label>
                            <input type="date" name="last_activity_at_to" id="last_activity_at_to" value="{{ request('last_activity_at_to') }}" class="{{ $fieldClass }}">
                        </div>

                        <details class="group rounded-xl border border-white/[0.08] bg-white/[0.02]" @if (collect($filterKeys)->filter(fn ($key) => request()->filled($key))->intersect(['description', 'satisfaction_rating_min', 'satisfaction_rating_max', 'satisfaction_comment', 'opened_at_from', 'opened_at_to', 'first_response_at_from', 'first_response_at_to', 'resolved_at_from', 'resolved_at_to', 'closed_at_from', 'closed_at_to', 'due_at_from', 'due_at_to', 'updated_at_from', 'updated_at_to'])->isNotEmpty()) open @endif>
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-3 py-3 text-sm font-medium text-gray-300 hover:text-white">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-filter-circle-dollar text-indigo-300" aria-hidden="true"></i> Advanced filters</span>
                                <i class="fa-solid fa-chevron-down text-xs text-gray-500 transition group-open:rotate-180" aria-hidden="true"></i>
                            </summary>
                            <div class="space-y-4 border-t border-white/[0.08] p-3">
                                <div>
                                    <label for="description" class="{{ $labelClass }}">Description contains</label>
                                    <input type="text" name="description" id="description" value="{{ request('description') }}" placeholder="Search description..." class="{{ $fieldClass }}">
                                    <label class="mt-2 flex items-center gap-2 text-xs text-gray-500">
                                        <input type="hidden" name="description_ignore_case" value="0">
                                        <input type="checkbox" name="description_ignore_case" value="1" @checked(request('description_ignore_case', '1') === '1') class="rounded border-gray-600 bg-gray-800 text-indigo-500 focus:ring-indigo-500/30">
                                        Ignore letter case
                                    </label>
                                </div>

                                <div>
                                    <label for="satisfaction_comment" class="{{ $labelClass }}">Satisfaction comment</label>
                                    <input type="text" name="satisfaction_comment" id="satisfaction_comment" value="{{ request('satisfaction_comment') }}" placeholder="Search feedback..." class="{{ $fieldClass }}">
                                    <label class="mt-2 flex items-center gap-2 text-xs text-gray-500">
                                        <input type="hidden" name="satisfaction_comment_ignore_case" value="0">
                                        <input type="checkbox" name="satisfaction_comment_ignore_case" value="1" @checked(request('satisfaction_comment_ignore_case', '1') === '1') class="rounded border-gray-600 bg-gray-800 text-indigo-500 focus:ring-indigo-500/30">
                                        Ignore letter case
                                    </label>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label for="satisfaction_rating_min" class="{{ $labelClass }}">Rating min.</label>
                                        <input type="number" min="0" max="65535" step="1" name="satisfaction_rating_min" id="satisfaction_rating_min" value="{{ request('satisfaction_rating_min') }}" class="{{ $fieldClass }}">
                                    </div>
                                    <div>
                                        <label for="satisfaction_rating_max" class="{{ $labelClass }}">Rating max.</label>
                                        <input type="number" min="0" max="65535" step="1" name="satisfaction_rating_max" id="satisfaction_rating_max" value="{{ request('satisfaction_rating_max') }}" class="{{ $fieldClass }}">
                                    </div>
                                </div>

                                <div class="border-t border-white/[0.08] pt-4">
                                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Additional date ranges</h3>
                                    <div class="space-y-4">
                                        @foreach ([
                                            'opened_at' => 'Opened',
                                            'first_response_at' => 'First response',
                                            'resolved_at' => 'Resolved',
                                            'closed_at' => 'Closed',
                                            'due_at' => 'Due date',
                                            'updated_at' => 'Last updated',
                                        ] as $dateColumn => $dateLabel)
                                            <fieldset class="grid grid-cols-2 gap-2">
                                                <legend class="mb-2 col-span-2 text-xs font-medium text-gray-400">{{ $dateLabel }}</legend>
                                                <div>
                                                    <label for="{{ $dateColumn }}_from" class="mb-1 block text-[11px] text-gray-500">From</label>
                                                    <input type="date" name="{{ $dateColumn }}_from" id="{{ $dateColumn }}_from" value="{{ request($dateColumn . '_from') }}" class="{{ $fieldClass }} px-2 text-xs">
                                                </div>
                                                <div>
                                                    <label for="{{ $dateColumn }}_to" class="mb-1 block text-[11px] text-gray-500">To</label>
                                                    <input type="date" name="{{ $dateColumn }}_to" id="{{ $dateColumn }}_to" value="{{ request($dateColumn . '_to') }}" class="{{ $fieldClass }} px-2 text-xs">
                                                </div>
                                            </fieldset>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </details>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="sort_by" class="{{ $labelClass }}">Sort by</label>
                                <select name="sort_by" id="sort_by" class="{{ $selectClass }}">
                                    @foreach ([
                                        'last_activity_at' => 'Last activity',
                                        'ticket_number' => 'Ticket number',
                                        'subject' => 'Subject',
                                        'created_at' => 'Created date',
                                        'updated_at' => 'Updated date',
                                        'due_at' => 'Due date',
                                    ] as $sortValue => $sortLabel)
                                        <option value="{{ $sortValue }}" @selected(request('sort_by', 'last_activity_at') === $sortValue)>{{ $sortLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="sort_order" class="{{ $labelClass }}">Direction</label>
                                <select name="sort_order" id="sort_order" class="{{ $selectClass }}">
                                    <option value="desc" @selected(request('sort_order', 'desc') === 'desc')>Newest first</option>
                                    <option value="asc" @selected(request('sort_order') === 'asc')>Oldest first</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="per_page" class="{{ $labelClass }}">Results per page</label>
                            <select name="per_page" id="per_page" class="{{ $selectClass }}">
                                @foreach ([5, 10, 15, 25, 50] as $size)
                                    <option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }} tickets</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex flex-col gap-2 border-t border-white/[0.08] pt-4">
                            <button type="submit" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/30">
                                <i class="fa-solid fa-filter text-xs" aria-hidden="true"></i>
                                Apply filters
                            </button>
                            <a href="{{ route('dashboard') }}" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl border border-white/10 px-4 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:bg-white/[0.04] hover:text-white">
                                <i class="fa-solid fa-rotate-left text-xs" aria-hidden="true"></i>
                                Clear filters
                            </a>
                        </div>
                    </form>
                </section>
            </aside>

            {{-- Right column: ticket results (70%) --}}
            <section class="min-w-0 lg:col-span-7">
                <div class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/20 backdrop-blur-xl">
                    <div class="flex flex-col gap-3 border-b border-white/[0.08] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-indigo-500/20 bg-indigo-500/10 text-indigo-300">
                                <i class="fa-solid fa-ticket" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h2 class="font-semibold text-white">My tickets</h2>
                                <p class="mt-0.5 text-xs text-gray-500">
                                    {{ $tickets->total() }} {{ \Illuminate\Support\Str::plural('ticket', $tickets->total()) }} found
                                </p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-500">Page {{ $tickets->currentPage() }} of {{ max(1, $tickets->lastPage()) }}</span>
                    </div>

                    @if ($tickets->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[760px] text-left text-sm">
                                <thead class="border-b border-white/[0.07] bg-white/[0.02] text-xs text-gray-500">
                                    <tr>
                                        <th scope="col" class="px-5 py-3.5 font-medium sm:px-6">Ticket</th>
                                        <th scope="col" class="px-5 py-3.5 font-medium">Status</th>
                                        <th scope="col" class="px-5 py-3.5 font-medium">Priority</th>
                                        <th scope="col" class="px-5 py-3.5 font-medium">Category</th>
                                        <th scope="col" class="px-5 py-3.5 font-medium">Last activity</th>
                                        <th scope="col" class="px-5 py-3.5 text-right font-medium">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/[0.06]">
                                    @foreach ($tickets as $ticket)
                                        @php
                                            $statusColor = $safeColor($ticket->status?->color);
                                            $priorityColor = $safeColor($ticket->priority?->color);
                                        @endphp
                                        <tr class="transition hover:bg-white/[0.025]">
                                            <td class="max-w-[300px] px-5 py-4 sm:px-6">
                                                <p class="font-mono text-xs font-semibold text-indigo-300">#{{ $ticket->ticket_number }}</p>
                                                <p class="mt-1 truncate font-medium text-white" title="{{ $ticket->subject }}">{{ $ticket->subject }}</p>
                                                <p class="mt-1 line-clamp-1 text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($ticket->description, 90) }}</p>
                                                @if ($ticket->is_escalated)
                                                    <span class="mt-2 inline-flex items-center gap-1 rounded-md border border-rose-500/20 bg-rose-500/10 px-1.5 py-1 text-[10px] font-semibold uppercase text-rose-300">
                                                        <i class="fa-solid fa-arrow-up-right-dots" aria-hidden="true"></i> Escalated
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4">
                                                @if ($ticket->status)
                                                    <span class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs font-medium" style="color: {{ $statusColor }}; border-color: {{ $statusColor }}40; background-color: {{ $statusColor }}12;">
                                                        @if ($ticket->status->icon)
                                                            <i class="{{ $ticket->status->icon }} text-[10px]" aria-hidden="true"></i>
                                                        @endif
                                                        {{ $ticket->status->name }}
                                                    </span>
                                                @else
                                                    <span class="text-xs text-gray-500">Unavailable</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4">
                                                @if ($ticket->priority)
                                                    <span class="inline-flex items-center gap-2 text-xs font-medium text-gray-300">
                                                        <span class="h-2 w-2 rounded-full" style="background-color: {{ $priorityColor }}" aria-hidden="true"></span>
                                                        {{ $ticket->priority->name }}
                                                    </span>
                                                @else
                                                    <span class="text-xs text-gray-500">Unavailable</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 text-xs text-gray-300">{{ $ticket->category?->name ?? 'Unavailable' }}</td>
                                            <td class="whitespace-nowrap px-5 py-4">
                                                <p class="text-xs text-gray-300">{{ $ticket->last_activity_at?->format('M d, Y') ?? '—' }}</p>
                                                <p class="mt-1 text-[11px] text-gray-500">{{ $ticket->last_activity_at?->format('H:i') ?? '' }}</p>
                                            </td>
                                            <td class="px-5 py-4 text-right">
                                                @if (\Illuminate\Support\Facades\Route::has('ticket.show-details'))
                                                    <a href="{{ route('ticket.show-details', $ticket->id) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 text-gray-400 transition hover:border-indigo-500/30 hover:bg-indigo-500/10 hover:text-indigo-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400/60" title="View ticket" aria-label="View ticket #{{ $ticket->ticket_number }}">
                                                        <i class="fa-regular fa-eye" aria-hidden="true"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex flex-col gap-3 border-t border-white/[0.07] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <p class="text-xs text-gray-500">
                                Showing <span class="font-medium text-gray-300">{{ $tickets->firstItem() }}–{{ $tickets->lastItem() }}</span>
                                of <span class="font-medium text-gray-300">{{ $tickets->total() }}</span>
                            </p>
                            @if ($tickets->hasPages())
                                <div class="text-sm [&_a]:rounded-lg [&_a]:border [&_a]:border-white/10 [&_a]:bg-white/[0.03] [&_a]:px-3 [&_a]:py-2 [&_a]:text-gray-300 [&_a:hover]:bg-white/[0.08] [&_span[aria-current]]:rounded-lg [&_span[aria-current]]:bg-indigo-600 [&_span[aria-current]]:px-3 [&_span[aria-current]]:py-2 [&_span[aria-current]]:text-white">
                                    {{ $tickets->onEachSide(1)->withQueryString()->links() }}
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="px-6 py-16 text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-white/10 bg-white/[0.03] text-gray-500">
                                <i class="fa-solid fa-filter-circle-xmark text-xl" aria-hidden="true"></i>
                            </div>
                            <h3 class="mt-4 font-semibold text-white">No tickets found</h3>
                            <p class="mx-auto mt-1 max-w-sm text-sm leading-6 text-gray-500">
                                No tickets match the selected filters. Try changing your search criteria or clearing the filters.
                            </p>
                            <a href="{{ route('dashboard') }}" class="mt-5 inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-white/10 px-4 text-sm font-semibold text-gray-200 transition hover:bg-white/[0.05] hover:text-white">
                                <i class="fa-solid fa-rotate-left text-xs" aria-hidden="true"></i>
                                Clear filters
                            </a>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <p class="pb-2 text-center text-xs text-gray-600">
            <i class="fa-solid fa-shield-halved mr-1" aria-hidden="true"></i>
            Your dashboard only displays tickets associated with your account.
        </p>
    </div>
</div>
@endsection
