@extends('layouts.base')

@section('title', 'Tickets | Iris')

@section('content')
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=bricolage-grotesque:600,700" rel="stylesheet">

<style>
    [x-cloak] { display: none !important; }
</style>

@php
    $safeColor = static fn (?string $color): string =>
        preg_match('/^#[0-9a-fA-F]{6}$/', $color ?? '')
            ? $color
            : '#818cf8';

    $sourceLabels = [
        'WEB' => 'Web',
        'EMAIL' => 'Email',
        'API' => 'API',
        'CHAT' => 'Chat',
        'WHATSAPP' => 'WhatsApp',
        'PHONE' => 'Phone',
    ];

    $selectedSources = request()->input('source', []);

    $selectedSources = is_array($selectedSources)
        ? $selectedSources
        : ($selectedSources !== '' ? [$selectedSources] : []);

    $filterLabels = [
        'ticket_number' => fn ($v) => 'Ticket #: ' . $v,

        'user_id' => fn ($v) => 'Requester ID: ' . $v,
        'user_name' => fn ($v) => 'Requester: ' . $v,
        'user_email' => fn ($v) => 'Requester email: ' . $v,

        'agent_id' => fn ($v) => 'Agent ID: ' . $v,
        'agent_name' => fn ($v) => 'Agent: ' . $v,
        'agent_email' => fn ($v) => 'Agent email: ' . $v,

        'team_id' => fn ($v) => 'Team ID: ' . $v,

        'category_id' => fn ($v) => 'Category ID: ' . $v,
        'category_name' => fn ($v) => 'Category: ' . $v,
        'category_slug' => fn ($v) => 'Category slug: ' . $v,

        'status_id' => fn ($v) => 'Status ID: ' . $v,
        'status_name' => fn ($v) => 'Status: ' . $v,

        'priority_id' => fn ($v) => 'Priority ID: ' . $v,
        'priority_name' => fn ($v) => 'Priority: ' . $v,
        'code_name' => fn ($v) => 'Priority code: ' . $v,

        'subject' => fn ($v) => 'Subject: ' . $v,
        'description' => fn ($v) => 'Description: ' . $v,

        'satisfaction_rating_min' => fn ($v) => 'Rating ≥ ' . $v,
        'satisfaction_rating_max' => fn ($v) => 'Rating ≤ ' . $v,
        'satisfaction_comment' => fn ($v) => 'Feedback: ' . $v,

        'is_escalated' => fn ($v) => $v === '1'
            ? 'Escalated'
            : 'Not escalated',

        'opened_at_from' => fn ($v) => 'Opened from: ' . $v,
        'opened_at_to' => fn ($v) => 'Opened until: ' . $v,

        'last_activity_at_from' => fn ($v) => 'Activity from: ' . $v,
        'last_activity_at_to' => fn ($v) => 'Activity until: ' . $v,

        'first_response_at_from' => fn ($v) => 'First response from: ' . $v,
        'first_response_at_to' => fn ($v) => 'First response until: ' . $v,

        'resolved_at_from' => fn ($v) => 'Resolved from: ' . $v,
        'resolved_at_to' => fn ($v) => 'Resolved until: ' . $v,

        'closed_at_from' => fn ($v) => 'Closed from: ' . $v,
        'closed_at_to' => fn ($v) => 'Closed until: ' . $v,

        'due_at_from' => fn ($v) => 'Due from: ' . $v,
        'due_at_to' => fn ($v) => 'Due until: ' . $v,

        'created_at_from' => fn ($v) => 'Created from: ' . $v,
        'created_at_to' => fn ($v) => 'Created until: ' . $v,

        'updated_at_from' => fn ($v) => 'Updated from: ' . $v,
        'updated_at_to' => fn ($v) => 'Updated until: ' . $v,
    ];

    $chips = [];

    foreach ($filterLabels as $key => $labelResolver) {
        $value = request()->input($key);

        if (
            request()->filled($key)
            && !is_array($value)
        ) {
            $chips[$key] = $labelResolver((string) $value);
        }
    }

    if ($selectedSources !== []) {
        $sourceNames = collect($selectedSources)
            ->map(fn ($source) => $sourceLabels[$source] ?? $source)
            ->implode(', ');

        $chips['source'] = 'Source: ' . $sourceNames;
    }

    if (in_array(request('trashed'), ['with', 'only'], true)) {
        $chips['trashed'] = request('trashed') === 'with'
            ? 'All records'
            : 'Deleted only';
    }

    $advancedKeys = [
        'user_id',
        'user_name',
        'user_email',
        'agent_id',
        'agent_name',
        'agent_email',
        'team_id',

        'category_id',
        'category_name',
        'category_slug',

        'status_id',
        'status_name',

        'priority_id',
        'priority_name',
        'code_name',

        'description',
        'source',
        'satisfaction_rating_min',
        'satisfaction_rating_max',
        'satisfaction_comment',
        'is_escalated',

        'opened_at_from',
        'opened_at_to',
        'last_activity_at_from',
        'last_activity_at_to',
        'first_response_at_from',
        'first_response_at_to',
        'resolved_at_from',
        'resolved_at_to',
        'closed_at_from',
        'closed_at_to',
        'due_at_from',
        'due_at_to',
        'created_at_from',
        'created_at_to',
        'updated_at_from',
        'updated_at_to',

        'trashed',
    ];

    $advancedCount = collect($advancedKeys)
        ->filter(fn ($key) => request()->filled($key))
        ->count();

    $sortBy = request('sort_by', 'id');
    $sortOrder = strtolower((string) request('sort_order', 'desc'));

    $sortUrl = static fn (string $column) => route(
        'ticket.list',
        array_merge(
            request()->except(['page']),
            [
                'sort_by' => $column,
                'sort_order' => (
                    $sortBy === $column && $sortOrder === 'asc'
                ) ? 'desc' : 'asc',
            ]
        )
    );

    $ariaSort = static fn (string $column) => $sortBy === $column
        ? ($sortOrder === 'asc' ? 'ascending' : 'descending')
        : 'none';

    $field = 'h-11 w-full rounded-xl border border-white/10 bg-white/[0.04] px-3.5 text-sm text-white placeholder:text-gray-500 transition hover:border-white/20 focus:border-indigo-400 focus:bg-white/[0.06] focus:outline-none focus:ring-4 focus:ring-indigo-500/15';

    $label = 'mb-1.5 block text-xs font-medium text-gray-400';

    $select = $field . ' appearance-none pr-9 [&>option]:bg-gray-900';

    $caseOptions = [
        '1' => 'Ignore case',
        '0' => 'Match case',
    ];
@endphp

<div
    class="relative isolate min-h-screen px-4 py-8 text-gray-100 [color-scheme:dark] sm:px-6 lg:px-8"
    x-data="{ advanced: @js($advancedCount > 0) }"
>
    <div
        class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-[radial-gradient(ellipse_at_50%_0%,rgba(99,102,241,.16),transparent_70%)]"
        aria-hidden="true"
    ></div>

    <div class="mx-auto max-w-7xl">

        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <nav class="mb-3 flex items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
                    <a
                        href="{{ route('dashboard') }}"
                        class="transition-colors hover:text-gray-300"
                        aria-label="Dashboard"
                    >
                        <i class="fa-solid fa-house text-xs" aria-hidden="true"></i>
                    </a>

                    <span aria-hidden="true">/</span>
                    <span>Workspace</span>
                    <span aria-hidden="true">/</span>

                    <span class="text-indigo-400" aria-current="page">Tickets</span>
                </nav>

                <h1
                    class="text-3xl font-bold tracking-tight text-white sm:text-4xl"
                    style="font-family: 'Bricolage Grotesque', ui-sans-serif, system-ui, sans-serif;"
                >
                    Tickets
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                    Track requests, review their status, and search tickets with detailed filters.
                </p>
            </div>

            @can('ticket.create')
                <a
                    href="{{ route('ticket.create') }}"
                    class="group relative inline-flex h-11 items-center justify-center gap-2 overflow-hidden rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 text-sm font-semibold text-white shadow-lg shadow-indigo-950/40 transition hover:shadow-indigo-500/25 focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/40 active:scale-[0.99]"
                >
                    <i class="fa-solid fa-plus relative text-xs" aria-hidden="true"></i>
                    <span class="relative">Create ticket</span>
                </a>
            @endcan
        </div>

        {{-- Feedback --}}
        @if (session('success'))
            <div
                x-data="{ show: true }"
                x-show="show"
                x-transition.opacity
                role="status"
                class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300"
            >
                <i class="fa-solid fa-circle-check mt-0.5" aria-hidden="true"></i>
                <p class="flex-1">{{ session('success') }}</p>
                <button type="button" @click="show = false" aria-label="Dismiss">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div
                x-data="{ show: true }"
                x-show="show"
                x-transition.opacity
                role="alert"
                class="mb-5 flex items-start gap-3 rounded-xl border border-red-500/25 bg-red-500/10 p-4 text-sm text-red-300"
            >
                <i class="fa-solid fa-circle-exclamation mt-0.5" aria-hidden="true"></i>
                <p class="flex-1">{{ session('error') }}</p>
                <button type="button" @click="show = false" aria-label="Dismiss">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
        @endif

        {{-- Filters --}}
        <section class="mb-4 rounded-2xl border border-white/10 bg-gray-950/60 p-4 shadow-xl shadow-black/20 backdrop-blur-xl sm:p-5">

            <form id="ticket-filters" action="{{ route('ticket.list') }}" method="GET">

                {{-- Primary filters --}}
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

                    <div>
                        <label for="ticket_number" class="{{ $label }}">Ticket number</label>
                        <input
                            type="number"
                            min="1"
                            step="1"
                            name="ticket_number"
                            id="ticket_number"
                            value="{{ request('ticket_number') }}"
                            placeholder="e.g. 10042"
                            class="{{ $field }}"
                        >
                    </div>

                    <div class="sm:col-span-2">
                        <label for="subject" class="{{ $label }}">Subject</label>

                        <input
                            type="text"
                            name="subject"
                            id="subject"
                            value="{{ request('subject') }}"
                            placeholder="Search ticket subject..."
                            class="{{ $field }}"
                        >

                        <select name="subject_ignore_case" class="{{ $select }} mt-2">
                            @foreach ($caseOptions as $value => $text)
                                <option
                                    value="{{ $value }}"
                                    @selected(request('subject_ignore_case', '1') === $value)
                                >
                                    {{ $text }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button
                            type="button"
                            @click="advanced = !advanced"
                            :aria-expanded="advanced.toString()"
                            aria-controls="advanced-filters"
                            class="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-xl border border-white/10 px-4 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:bg-white/[0.05] hover:text-white focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/20"
                        >
                            <i class="fa-solid fa-sliders text-xs" aria-hidden="true"></i>
                            Filters

                            @if ($advancedCount > 0)
                                <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-indigo-500 px-1.5 text-[11px] font-semibold text-white">
                                    {{ $advancedCount }}
                                </span>
                            @endif
                        </button>

                        <button
                            type="submit"
                            class="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/40"
                        >
                            Apply
                        </button>

                        <a
                            href="{{ route('ticket.list') }}"
                            class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white/10 text-gray-400 transition hover:bg-white/[0.05] hover:text-white"
                            title="Clear all filters"
                            aria-label="Clear all filters"
                        >
                            <i class="fa-solid fa-rotate-left text-xs" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>

                {{-- Advanced filters --}}
                <div
                    id="advanced-filters"
                    x-show="advanced"
                    x-cloak
                    x-transition
                    class="mt-5 border-t border-white/[0.08] pt-5"
                >
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-sm font-semibold text-white">Advanced filters</h2>
                        <p class="text-xs text-gray-500">Combine any number of filters.</p>
                    </div>

                    {{-- Requester filters --}}
                    <div class="mb-6">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Requester
                        </h3>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <label for="user_id" class="{{ $label }}">Requester ID</label>
                                <input
                                    type="number"
                                    min="1"
                                    name="user_id"
                                    id="user_id"
                                    value="{{ request('user_id') }}"
                                    placeholder="User ID"
                                    class="{{ $field }}"
                                >
                            </div>

                            <div>
                                <label for="user_name" class="{{ $label }}">Requester name</label>
                                <input
                                    type="text"
                                    name="user_name"
                                    id="user_name"
                                    value="{{ request('user_name') }}"
                                    placeholder="Name contains..."
                                    class="{{ $field }}"
                                >
                                <select name="user_name_ignore_case" class="{{ $select }} mt-2">
                                    @foreach ($caseOptions as $value => $text)
                                        <option value="{{ $value }}" @selected(request('user_name_ignore_case', '1') === $value)>{{ $text }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="user_email" class="{{ $label }}">Requester email</label>
                                <input
                                    type="text"
                                    name="user_email"
                                    id="user_email"
                                    value="{{ request('user_email') }}"
                                    placeholder="Email contains..."
                                    class="{{ $field }}"
                                >
                                <select name="user_email_ignore_case" class="{{ $select }} mt-2">
                                    @foreach ($caseOptions as $value => $text)
                                        <option value="{{ $value }}" @selected(request('user_email_ignore_case', '1') === $value)>{{ $text }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Agent and team --}}
                    <div class="mb-6 border-t border-white/[0.08] pt-5">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Assignment
                        </h3>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <label for="agent_id" class="{{ $label }}">Agent ID</label>
                                <input type="number" min="1" name="agent_id" id="agent_id" value="{{ request('agent_id') }}" placeholder="Agent ID" class="{{ $field }}">
                            </div>

                            <div>
                                <label for="agent_name" class="{{ $label }}">Agent name</label>
                                <input type="text" name="agent_name" id="agent_name" value="{{ request('agent_name') }}" placeholder="Agent name contains..." class="{{ $field }}">
                                <select name="agent_name_ignore_case" class="{{ $select }} mt-2">
                                    @foreach ($caseOptions as $value => $text)
                                        <option value="{{ $value }}" @selected(request('agent_name_ignore_case', '1') === $value)>{{ $text }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="agent_email" class="{{ $label }}">Agent email</label>
                                <input type="text" name="agent_email" id="agent_email" value="{{ request('agent_email') }}" placeholder="Agent email contains..." class="{{ $field }}">
                                <select name="agent_email_ignore_case" class="{{ $select }} mt-2">
                                    @foreach ($caseOptions as $value => $text)
                                        <option value="{{ $value }}" @selected(request('agent_email_ignore_case', '1') === $value)>{{ $text }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="team_id" class="{{ $label }}">Team ID</label>
                                <input type="number" min="1" name="team_id" id="team_id" value="{{ request('team_id') }}" placeholder="Team ID" class="{{ $field }}">
                            </div>
                        </div>
                    </div>

                    {{-- Classification --}}
                    <div class="mb-6 border-t border-white/[0.08] pt-5">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Classification
                        </h3>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                            <div>
                                <label for="category_id" class="{{ $label }}">Category ID</label>
                                <input type="number" min="1" name="category_id" id="category_id" value="{{ request('category_id') }}" placeholder="Category ID" class="{{ $field }}">
                            </div>

                            <div>
                                <label for="category_name" class="{{ $label }}">Category name</label>
                                <input type="text" name="category_name" id="category_name" value="{{ request('category_name') }}" placeholder="Category name contains..." class="{{ $field }}">
                                <select name="category_name_ignore_case" class="{{ $select }} mt-2">
                                    @foreach ($caseOptions as $value => $text)
                                        <option value="{{ $value }}" @selected(request('category_name_ignore_case', '1') === $value)>{{ $text }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="category_slug" class="{{ $label }}">Category slug</label>
                                <input type="text" name="category_slug" id="category_slug" value="{{ request('category_slug') }}" placeholder="category-slug" class="{{ $field }}">
                                <select name="category_slug_ignore_case" class="{{ $select }} mt-2">
                                    @foreach ($caseOptions as $value => $text)
                                        <option value="{{ $value }}" @selected(request('category_slug_ignore_case', '1') === $value)>{{ $text }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="status_id" class="{{ $label }}">Status ID</label>
                                <input type="number" min="1" name="status_id" id="status_id" value="{{ request('status_id') }}" placeholder="Status ID" class="{{ $field }}">
                            </div>

                            <div>
                                <label for="status_name" class="{{ $label }}">Status name</label>
                                <input type="text" name="status_name" id="status_name" value="{{ request('status_name') }}" placeholder="Status name contains..." class="{{ $field }}">
                                <select name="status_name_ignore_case" class="{{ $select }} mt-2">
                                    @foreach ($caseOptions as $value => $text)
                                        <option value="{{ $value }}" @selected(request('status_name_ignore_case', '1') === $value)>{{ $text }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="priority_id" class="{{ $label }}">Priority ID</label>
                                <input type="number" min="1" name="priority_id" id="priority_id" value="{{ request('priority_id') }}" placeholder="Priority ID" class="{{ $field }}">
                            </div>

                            <div>
                                <label for="priority_name" class="{{ $label }}">Priority name</label>
                                <input type="text" name="priority_name" id="priority_name" value="{{ request('priority_name') }}" placeholder="Priority name contains..." class="{{ $field }}">
                                <select name="priority_name_ignore_case" class="{{ $select }} mt-2">
                                    @foreach ($caseOptions as $value => $text)
                                        <option value="{{ $value }}" @selected(request('priority_name_ignore_case', '1') === $value)>{{ $text }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="code_name" class="{{ $label }}">Priority code</label>
                                <input type="text" name="code_name" id="code_name" value="{{ request('code_name') }}" placeholder="Priority code contains..." class="{{ $field }}">
                                <select name="code_name_ignore_case" class="{{ $select }} mt-2">
                                    @foreach ($caseOptions as $value => $text)
                                        <option value="{{ $value }}" @selected(request('code_name_ignore_case', '1') === $value)>{{ $text }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Description, source and operational fields --}}
                    <div class="mb-6 border-t border-white/[0.08] pt-5">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Content and operations
                        </h3>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <label for="description" class="{{ $label }}">Description</label>
                                <input type="text" name="description" id="description" value="{{ request('description') }}" placeholder="Description contains..." class="{{ $field }}">
                                <select name="description_ignore_case" class="{{ $select }} mt-2">
                                    @foreach ($caseOptions as $value => $text)
                                        <option value="{{ $value }}" @selected(request('description_ignore_case', '1') === $value)>{{ $text }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="source" class="{{ $label }}">Ticket source</label>
                                <select
                                    name="source[]"
                                    id="source"
                                    multiple
                                    size="4"
                                    class="{{ $field }} h-auto min-h-28"
                                >
                                    @foreach ($sourceLabels as $value => $sourceLabel)
                                        <option value="{{ $value }}" @selected(in_array($value, $selectedSources, true))>
                                            {{ $sourceLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1.5 text-xs text-gray-500">
                                    Hold Ctrl/Cmd to select multiple sources.
                                </p>
                            </div>

                            <div>
                                <label for="is_escalated" class="{{ $label }}">Escalation</label>
                                <select name="is_escalated" id="is_escalated" class="{{ $select }}">
                                    <option value="">All tickets</option>
                                    <option value="1" @selected(request('is_escalated') === '1')>Escalated</option>
                                    <option value="0" @selected(request('is_escalated') === '0')>Not escalated</option>
                                </select>
                            </div>

                            <div>
                                <label for="satisfaction_rating_min" class="{{ $label }}">Rating minimum</label>
                                <input type="number" min="0" max="65535" step="1" name="satisfaction_rating_min" id="satisfaction_rating_min" value="{{ request('satisfaction_rating_min') }}" placeholder="Minimum rating" class="{{ $field }}">
                            </div>

                            <div>
                                <label for="satisfaction_rating_max" class="{{ $label }}">Rating maximum</label>
                                <input type="number" min="0" max="65535" step="1" name="satisfaction_rating_max" id="satisfaction_rating_max" value="{{ request('satisfaction_rating_max') }}" placeholder="Maximum rating" class="{{ $field }}">
                            </div>

                            <div>
                                <label for="satisfaction_comment" class="{{ $label }}">Satisfaction comment</label>
                                <input type="text" name="satisfaction_comment" id="satisfaction_comment" value="{{ request('satisfaction_comment') }}" placeholder="Feedback contains..." class="{{ $field }}">
                                <select name="satisfaction_comment_ignore_case" class="{{ $select }} mt-2">
                                    @foreach ($caseOptions as $value => $text)
                                        <option value="{{ $value }}" @selected(request('satisfaction_comment_ignore_case', '1') === $value)>{{ $text }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="trashed" class="{{ $label }}">Record status</label>
                                <select name="trashed" id="trashed" class="{{ $select }}">
                                    <option value="" @selected(request('trashed', '') === '')>Active records only</option>
                                    <option value="with" @selected(request('trashed') === 'with')>All records</option>
                                    <option value="only" @selected(request('trashed') === 'only')>Deleted records only</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Date ranges --}}
                    <div class="border-t border-white/[0.08] pt-5">
                        <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Date ranges
                        </h3>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ([
                                'opened_at' => 'Opened',
                                'last_activity_at' => 'Last activity',
                                'first_response_at' => 'First response',
                                'resolved_at' => 'Resolved',
                                'closed_at' => 'Closed',
                                'due_at' => 'Due date',
                                'created_at' => 'Created',
                                'updated_at' => 'Updated',
                            ] as $column => $dateLabel)
                                <fieldset class="rounded-xl border border-white/[0.07] bg-white/[0.015] p-3">
                                    <legend class="px-1 text-xs font-medium text-gray-400">
                                        {{ $dateLabel }}
                                    </legend>

                                    <div class="space-y-2">
                                        <div>
                                            <label for="{{ $column }}_from" class="mb-1 block text-[11px] text-gray-500">
                                                From
                                            </label>
                                            <input
                                                type="date"
                                                name="{{ $column }}_from"
                                                id="{{ $column }}_from"
                                                value="{{ request($column . '_from') }}"
                                                class="{{ $field }}"
                                            >
                                        </div>

                                        <div>
                                            <label for="{{ $column }}_to" class="mb-1 block text-[11px] text-gray-500">
                                                To
                                            </label>
                                            <input
                                                type="date"
                                                name="{{ $column }}_to"
                                                id="{{ $column }}_to"
                                                value="{{ request($column . '_to') }}"
                                                class="{{ $field }}"
                                            >
                                        </div>
                                    </div>
                                </fieldset>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Sorting --}}
                <div class="mt-5 grid grid-cols-1 gap-3 border-t border-white/[0.08] pt-5 sm:grid-cols-3">
                    <div>
                        <label for="sort_by" class="{{ $label }}">Sort by</label>
                        <select name="sort_by" id="sort_by" class="{{ $select }}">
                            @foreach (\App\Utils\Enums\Ticket\TicketColumnEnum::cases() as $column)
                                <option
                                    value="{{ $column->value }}"
                                    @selected($sortBy === $column->value)
                                >
                                    {{ ucwords(str_replace('_', ' ', $column->value)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="sort_order" class="{{ $label }}">Sort order</label>
                        <select name="sort_order" id="sort_order" class="{{ $select }}">
                            <option value="desc" @selected($sortOrder === 'desc')>Descending</option>
                            <option value="asc" @selected($sortOrder === 'asc')>Ascending</option>
                        </select>
                    </div>

                    <div>
                        <label for="per_page" class="{{ $label }}">Results per page</label>
                        <select name="per_page" id="per_page" class="{{ $select }}">
                            @foreach ([15, 25, 50, 100] as $size)
                                <option value="{{ $size }}" @selected((int) request('per_page', 15) === $size)>
                                    {{ $size }} results
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Filter actions --}}
                <div class="mt-5 flex flex-col-reverse gap-3 border-t border-white/[0.08] pt-5 sm:flex-row sm:items-center sm:justify-end">
                    <a
                        href="{{ route('ticket.list') }}"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-white/10 px-5 text-sm font-medium text-gray-300 transition hover:bg-white/[0.05] hover:text-white"
                    >
                        <i class="fa-solid fa-rotate-left text-xs" aria-hidden="true"></i>
                        Clear filters
                    </a>

                    <button
                        type="submit"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 text-sm font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/40"
                    >
                        <i class="fa-solid fa-magnifying-glass text-xs" aria-hidden="true"></i>
                        Apply filters
                    </button>
                </div>
            </form>
        </section>

        {{-- Active filter chips --}}
        @if ($chips)
            <div class="mb-4 flex flex-wrap items-center gap-2" aria-label="Active filters">
                @foreach ($chips as $key => $chipLabel)
                    <a
                        href="{{ route('ticket.list', request()->except([$key, 'page'])) }}"
                        class="group inline-flex items-center gap-2 rounded-full border border-indigo-400/20 bg-indigo-500/10 py-1 pl-3 pr-2.5 text-xs font-medium text-indigo-200 transition hover:border-indigo-400/40 hover:bg-indigo-500/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400/60"
                        title="Remove this filter"
                    >
                        <span>{{ $chipLabel }}</span>
                        <i class="fa-solid fa-xmark text-[10px] text-indigo-300/70 group-hover:text-white" aria-hidden="true"></i>
                    </a>
                @endforeach

                <a
                    href="{{ route('ticket.list') }}"
                    class="ml-1 text-xs font-medium text-gray-400 transition hover:text-white"
                >
                    Clear all
                </a>
            </div>
        @endif

        {{-- Results --}}
        <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/20 backdrop-blur-xl">
            <div class="flex items-center justify-between gap-4 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-300">
                        <i class="fa-solid fa-ticket" aria-hidden="true"></i>
                    </div>

                    <div>
                        <h2 class="font-semibold text-white">Ticket directory</h2>
                        <p class="mt-0.5 text-xs text-gray-500">
                            {{ $tickets->total() }}
                            {{ \Illuminate\Support\Str::plural('ticket', $tickets->total()) }}
                            found
                        </p>
                    </div>
                </div>

                <span class="hidden text-xs text-gray-500 sm:block">
                    Page {{ $tickets->currentPage() }}
                    of {{ max(1, $tickets->lastPage()) }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1120px] text-left text-sm">
                    <thead class="border-b border-white/[0.08] bg-white/[0.02] text-xs text-gray-500">
                        <tr>
                            <th scope="col" class="px-5 py-3.5 font-medium" aria-sort="{{ $ariaSort('ticket_number') }}">
                                <a href="{{ $sortUrl('ticket_number') }}" class="inline-flex items-center gap-1.5 transition hover:text-gray-200">
                                    Ticket
                                    <i @class([
                                        'fa-solid text-[10px]',
                                        'fa-sort' => $sortBy !== 'ticket_number',
                                        'fa-sort-up text-indigo-300' => $sortBy === 'ticket_number' && $sortOrder === 'asc',
                                        'fa-sort-down text-indigo-300' => $sortBy === 'ticket_number' && $sortOrder !== 'asc',
                                    ]) aria-hidden="true"></i>
                                </a>
                            </th>

                            <th scope="col" class="px-5 py-3.5 font-medium" aria-sort="{{ $ariaSort('subject') }}">
                                <a href="{{ $sortUrl('subject') }}" class="inline-flex items-center gap-1.5 transition hover:text-gray-200">
                                    Subject
                                    <i @class([
                                        'fa-solid text-[10px]',
                                        'fa-sort' => $sortBy !== 'subject',
                                        'fa-sort-up text-indigo-300' => $sortBy === 'subject' && $sortOrder === 'asc',
                                        'fa-sort-down text-indigo-300' => $sortBy === 'subject' && $sortOrder !== 'asc',
                                    ]) aria-hidden="true"></i>
                                </a>
                            </th>

                            <th scope="col" class="px-5 py-3.5 font-medium">Requester / Agent</th>
                            <th scope="col" class="px-5 py-3.5 font-medium">Category</th>
                            <th scope="col" class="px-5 py-3.5 font-medium">Status</th>
                            <th scope="col" class="px-5 py-3.5 font-medium">Priority</th>
                            <th scope="col" class="px-5 py-3.5 font-medium">Last activity</th>
                            <th scope="col" class="px-5 py-3.5 text-right font-medium">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-white/[0.06]">
                        @forelse ($tickets as $ticket)
                            @php
                                $priorityColor = $safeColor($ticket->priority?->color);
                                $statusColor = $safeColor($ticket->status?->color);
                            @endphp

                            <tr @class([
                                'transition hover:bg-white/[0.03]',
                                'opacity-60' => $ticket->trashed(),
                            ])>
                                {{-- Ticket number --}}
                                <td class="px-5 py-4 align-top">
                                    <p class="font-mono text-xs font-semibold text-indigo-300">
                                        #{{ $ticket->ticket_number }}
                                    </p>

                                    <p class="mt-1 text-[11px] text-gray-600">
                                        ID {{ $ticket->id }}
                                    </p>

                                    @if ($ticket->is_escalated)
                                        <span class="mt-2 inline-flex items-center gap-1 rounded-md border border-amber-500/20 bg-amber-500/10 px-1.5 py-1 text-[10px] font-semibold text-amber-300">
                                            <i class="fa-solid fa-arrow-up-right-dots" aria-hidden="true"></i>
                                            Escalated
                                        </span>
                                    @endif

                                    @if ($ticket->trashed())
                                        <span class="mt-2 inline-flex items-center gap-1 rounded-md border border-red-500/20 bg-red-500/10 px-1.5 py-1 text-[10px] font-semibold text-red-300">
                                            <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                            Deleted
                                        </span>
                                    @endif
                                </td>

                                {{-- Subject --}}
                                <td class="max-w-[260px] px-5 py-4 align-top">
                                    <p class="line-clamp-2 font-semibold text-white">
                                        {{ $ticket->subject }}
                                    </p>

                                    <p class="mt-1 line-clamp-2 text-xs leading-5 text-gray-500">
                                        {{ $ticket->description }}
                                    </p>

                                    <span class="mt-2 inline-flex rounded-md bg-white/[0.05] px-1.5 py-1 text-[10px] font-medium text-gray-400">
                                        {{ $sourceLabels[$ticket->source] ?? $ticket->source }}
                                    </span>
                                </td>

                                {{-- Requester and agent --}}
                                <td class="px-5 py-4 align-top">
                                    <p class="max-w-[170px] truncate text-sm text-gray-200" title="{{ $ticket->user?->name }}">
                                        {{ $ticket->user?->name ?? 'Unknown requester' }}
                                    </p>

                                    <p class="mt-1 max-w-[170px] truncate text-xs text-gray-500" title="{{ $ticket->agent?->name ?? 'Unassigned' }}">
                                        <i class="fa-solid fa-headset mr-1 text-[10px] text-gray-600" aria-hidden="true"></i>
                                        {{ $ticket->agent?->name ?? 'Unassigned' }}
                                    </p>
                                </td>

                                {{-- Category --}}
                                <td class="px-5 py-4 align-top">
                                    @if ($ticket->category)
                                        <span class="inline-flex max-w-[150px] items-center gap-2 text-gray-300">
                                            @if ($ticket->category->icon)
                                                <i class="{{ $ticket->category->icon }} text-xs text-gray-500" aria-hidden="true"></i>
                                            @endif

                                            <span class="truncate">{{ $ticket->category->name }}</span>
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-600">Unavailable</span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-4 align-top">
                                    @if ($ticket->status)
                                        <span
                                            class="inline-flex max-w-[150px] items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs font-medium"
                                            style="color: {{ $statusColor }}; border-color: {{ $statusColor }}40; background-color: {{ $statusColor }}12;"
                                        >
                                            @if ($ticket->status->icon)
                                                <i class="{{ $ticket->status->icon }} text-[10px]" aria-hidden="true"></i>
                                            @endif

                                            <span class="truncate">{{ $ticket->status->name }}</span>
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-600">Unavailable</span>
                                    @endif
                                </td>

                                {{-- Priority --}}
                                <td class="px-5 py-4 align-top">
                                    @if ($ticket->priority)
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border"
                                                style="color: {{ $priorityColor }}; border-color: {{ $priorityColor }}40; background-color: {{ $priorityColor }}12;"
                                            >
                                                <i class="{{ $ticket->priority->icon ?: 'fa-solid fa-flag' }} text-xs" aria-hidden="true"></i>
                                            </span>

                                            <div class="min-w-0">
                                                <p class="max-w-[130px] truncate text-xs font-semibold text-gray-200">
                                                    {{ $ticket->priority->name }}
                                                </p>

                                                <p class="mt-1 text-[10px] text-gray-500">
                                                    Level {{ $ticket->priority->level }}
                                                </p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-600">Unavailable</span>
                                    @endif
                                </td>

                                {{-- Last activity --}}
                                <td class="whitespace-nowrap px-5 py-4 align-top">
                                    <p class="text-xs text-gray-300">
                                        {{ $ticket->last_activity_at?->format('M d, Y') ?? '—' }}
                                    </p>

                                    <p class="mt-1 text-[11px] text-gray-500">
                                        {{ $ticket->last_activity_at?->format('H:i') ?? '' }}
                                    </p>
                                </td>

                                {{-- Actions --}}
                                <td class="px-5 py-4 align-top">
                                    <div
                                        x-data="{
                                            open: false,
                                            top: 0,
                                            left: 0,
                                            toggle() {
                                                this.open = !this.open;
                                                if (this.open) this.$nextTick(() => this.place());
                                            },
                                            place() {
                                                const btn = this.$refs.trigger.getBoundingClientRect();
                                                const menu = this.$refs.menu;
                                                const gap = 8;

                                                let left = btn.left - menu.offsetWidth - gap;
                                                if (left < gap) left = btn.right + gap;

                                                let top = btn.top;
                                                const maxTop = window.innerHeight - menu.offsetHeight - gap;

                                                if (top > maxTop) top = Math.max(gap, maxTop);

                                                this.left = left;
                                                this.top = top;
                                            },
                                        }"
                                        @keydown.escape.window="open = false"
                                        @scroll.window.capture="open = false"
                                        @resize.window="open = false"
                                        class="flex justify-end"
                                    >
                                        <button
                                            type="button"
                                            x-ref="trigger"
                                            @click="toggle()"
                                            :aria-expanded="open.toString()"
                                            aria-haspopup="true"
                                            aria-label="Ticket actions"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 bg-white/[0.02] text-gray-400 transition hover:border-indigo-500/30 hover:bg-indigo-500/10 hover:text-indigo-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400/60"
                                        >
                                            <i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i>
                                        </button>

                                        <template x-teleport="body">
                                            <div
                                                x-ref="menu"
                                                x-show="open"
                                                x-cloak
                                                @click.outside="if (! $refs.trigger.contains($event.target)) open = false"
                                                x-transition
                                                :style="`top: ${top}px; left: ${left}px`"
                                                class="fixed z-50 w-56 origin-top-right rounded-xl border border-white/10 bg-[#11111c] p-1.5 text-gray-100 shadow-2xl shadow-black/50"
                                                role="menu"
                                                aria-label="Ticket actions"
                                            >
                                                <div class="border-b border-white/[0.08] px-3 py-2.5">
                                                    <p class="text-xs font-semibold text-white">
                                                        Ticket #{{ $ticket->ticket_number }}
                                                    </p>

                                                    <p class="mt-1 truncate text-[11px] text-gray-500">
                                                        {{ $ticket->subject }}
                                                    </p>
                                                </div>

                                                @if (\Illuminate\Support\Facades\Route::has('ticket.show-details') && auth()->user()?->can('ticket.read'))
                                                    <a
                                                        href="{{ route('ticket.show-details', ['id' => $ticket->id]) }}"
                                                        role="menuitem"
                                                        @click="open = false"
                                                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-300 transition hover:bg-white/[0.06] hover:text-white"
                                                    >
                                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-sky-500/10 text-sky-400">
                                                            <i class="fa-regular fa-eye text-xs" aria-hidden="true"></i>
                                                        </span>

                                                        <span>
                                                            <span class="block font-medium">View details</span>
                                                            <span class="mt-0.5 block text-[11px] text-gray-500">Inspect ticket information</span>
                                                        </span>
                                                    </a>
                                                @endif

                                                @can('update', $ticket)
                                                    @if (\Illuminate\Support\Facades\Route::has('ticket.edit'))
                                                        <a
                                                            href="{{ route('ticket.edit', ['id' => $ticket->id]) }}"
                                                            role="menuitem"
                                                            @click="open = false"
                                                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-300 transition hover:bg-white/[0.06] hover:text-white"
                                                        >
                                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-400">
                                                                <i class="fa-solid fa-pen-to-square text-xs" aria-hidden="true"></i>
                                                            </span>

                                                            <span>
                                                                <span class="block font-medium">Edit ticket</span>
                                                                <span class="mt-0.5 block text-[11px] text-gray-500">Manage ticket information</span>
                                                            </span>
                                                        </a>
                                                    @endif
                                                @endcan

                                                @can('updateOwn', $ticket)
                                                    @if (\Illuminate\Support\Facades\Route::has('ticket.own.edit'))
                                                        <a
                                                            href="{{ route('ticket.own.edit', ['id' => $ticket->id]) }}"
                                                            role="menuitem"
                                                            @click="open = false"
                                                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-300 transition hover:bg-white/[0.06] hover:text-white"
                                                        >
                                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-violet-500/10 text-violet-400">
                                                                <i class="fa-solid fa-pen text-xs" aria-hidden="true"></i>
                                                            </span>

                                                            <span>
                                                                <span class="block font-medium">Edit my ticket</span>
                                                                <span class="mt-0.5 block text-[11px] text-gray-500">Edit your own request</span>
                                                            </span>
                                                        </a>
                                                    @endif
                                                @endcan

                                                @if (
                                                    !auth()->user()?->can('ticket.read')
                                                    && !auth()->user()?->can('update', $ticket)
                                                    && !auth()->user()?->can('updateOwn', $ticket)
                                                )
                                                    <p class="px-3 py-4 text-center text-xs text-gray-500">
                                                        No actions available.
                                                    </p>
                                                @endif
                                            </div>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-20 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-white/[0.03] text-gray-500">
                                        <i class="fa-solid fa-ticket text-2xl" aria-hidden="true"></i>
                                    </div>

                                    @if ($chips)
                                        <h3 class="mt-5 font-semibold text-white">
                                            No tickets match these filters
                                        </h3>

                                        <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500">
                                            Try removing a filter or searching for something broader.
                                        </p>

                                        <a href="{{ route('ticket.list') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-medium text-indigo-400 transition hover:text-indigo-300">
                                            <i class="fa-solid fa-rotate-left text-xs" aria-hidden="true"></i>
                                            Clear filters
                                        </a>
                                    @else
                                        <h3 class="mt-5 font-semibold text-white">No tickets yet</h3>

                                        <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500">
                                            There are no tickets to display right now.
                                        </p>

                                        @can('ticket.create')
                                            <a href="{{ route('ticket.create') }}" class="mt-5 inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-500">
                                                <i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>
                                                Create ticket
                                            </a>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($tickets->total() > 0)
                <div class="flex flex-col gap-4 border-t border-white/[0.08] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-gray-500">
                        <p>
                            Showing
                            <span class="font-medium text-gray-300">
                                {{ $tickets->firstItem() }}–{{ $tickets->lastItem() }}
                            </span>
                            of
                            <span class="font-medium text-gray-300">{{ $tickets->total() }}</span>
                        </p>

                        <label class="flex items-center gap-2">
                            Rows per page

                            <select
                                form="ticket-filters"
                                name="per_page"
                                onchange="this.form.requestSubmit()"
                                class="h-8 rounded-lg border border-white/10 bg-white/[0.04] px-2 text-xs text-gray-200 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 [&>option]:bg-gray-900"
                            >
                                @foreach ([15, 25, 50, 100] as $size)
                                    <option
                                        value="{{ $size }}"
                                        @selected((int) request('per_page', 15) === $size)
                                    >
                                        {{ $size }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    {{ $tickets->withQueryString()->links() }}
                </div>
            @endif
        </section>
    </div>
</div>
@endsection