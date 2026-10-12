@extends('layouts.base')

@section('title', 'Ticket Priorities | Iris')

@section('content')

{{-- Fonte do título (opcional: se o layout tiver @stack('head'), mova estas linhas para lá) --}}

<link rel="preconnect" href="https://fonts.bunny.net">

<link href="https://fonts.bunny.net/css?family=bricolage-grotesque:600,700" rel="stylesheet" />

<style>

    [x-cloak] { display: none !important; }

    .iris-display { font-family: 'Bricolage Grotesque', ui-sans-serif, system-ui, sans-serif; }

</style>

@php

    $priorities->withQueryString();

    // Cor segura (hex) para cada prioridade

    $safeColor = fn (?string $c) => preg_match('/^#[0-9a-fA-F]{6}$/', $c ?? '') ? $c : '#818cf8';

    // 90 -> "1 h 30 min" | 1500 -> "1 d 1 h"

    $minutes = function (?int $m) {

        if ($m === null) {

            return null;

        }

        if ($m < 60) {

            return $m . ' min';

        }

        $h = intdiv($m, 60);

        $r = $m % 60;

        if ($h < 24) {

            return $h . ' h' . ($r ? ' ' . $r . ' min' : '');

        }

        $d = intdiv($h, 24);

        $h = $h % 24;

        return $d . ' d' . ($h ? ' ' . $h . ' h' : '');

    };

    // Filtros ativos (viram "chips" removíveis)
    $hasFilter = fn (string $key): bool => request()->has($key)
        && request($key) !== null
        && request($key) !== '';

    $chips = [];

    if ($hasFilter('name'))        $chips['name'] = 'Name: ' . request('name');
    if ($hasFilter('code'))        $chips['code'] = 'Code: ' . request('code');
    if ($hasFilter('level'))       $chips['level'] = 'Level: ' . request('level');
    if ($hasFilter('description')) $chips['description'] = 'Description: ' . request('description');
    if ($hasFilter('color'))       $chips['color'] = 'Color: ' . request('color');
    if ($hasFilter('icon'))        $chips['icon'] = 'Icon: ' . request('icon');

    // SLA range chips
    foreach ([
        'response_time_minutes_min' => 'Response min: ',
        'response_time_minutes_max' => 'Response max: ',
        'resolution_time_minutes_min' => 'Resolution min: ',
        'resolution_time_minutes_max' => 'Resolution max: ',
        'escalation_time_minutes_min' => 'Escalation min: ',
        'escalation_time_minutes_max' => 'Escalation max: ',
    ] as $key => $labelText) {
        if ($hasFilter($key)) {
            $chips[$key] = $labelText . request($key) . ' min';
        }
    }

    if ($hasFilter('is_active')) {
        $chips['is_active'] = request('is_active') === '1' ? 'Active' : 'Inactive';
    }

    if ($hasFilter('is_default')) {
        $chips['is_default'] = request('is_default') === '1' ? 'Default only' : 'Not default';
    }

    if (in_array(request('trashed'), ['with', 'only'], true)) {
        $chips['trashed'] = request('trashed') === 'with' ? 'All records' : 'Deleted only';
    }

    // Case-sensitive overrides are shown only when their text filter is active.
    foreach ([
        'name' => 'Name',
        'code' => 'Code',
        'description' => 'Description',
        'color' => 'Color',
        'icon' => 'Icon',
    ] as $key => $labelText) {
        $caseKey = $key . '_ignore_case';

        if ($hasFilter($key) && request($caseKey) === '0') {
            $chips[$caseKey] = $labelText . ': case-sensitive';
        }
    }

    $advancedKeys = [
        'code', 'level', 'description', 'color', 'icon',
        'response_time_minutes_min', 'response_time_minutes_max',
        'resolution_time_minutes_min', 'resolution_time_minutes_max',
        'escalation_time_minutes_min', 'escalation_time_minutes_max',
        'is_active', 'is_default', 'trashed',
    ];

    $advancedCount = collect($advancedKeys)
        ->filter($hasFilter)
        ->count();

    foreach ([
        'name' => 'name_ignore_case',
        'code' => 'code_ignore_case',
        'description' => 'description_ignore_case',
        'color' => 'color_ignore_case',
        'icon' => 'icon_ignore_case',
    ] as $fieldKey => $caseKey) {
        if ($hasFilter($fieldKey) && request($caseKey) === '0') {
            $advancedCount++;
        }
    }

    // Ordenação por cabeçalho

    $sortBy    = request('sort_by');

    $sortOrder = strtolower((string) request('sort_order', 'desc'));

    $sortUrl   = fn (string $col) => route('ticket.priority.list', array_merge(

        request()->except(['page']),

        ['sort_by' => $col, 'sort_order' => ($sortBy === $col && $sortOrder === 'asc') ? 'desc' : 'asc']

    ));

    $ariaSort  = fn (string $col) => $sortBy === $col ? ($sortOrder === 'asc' ? 'ascending' : 'descending') : 'none';

    $field  = 'h-11 w-full rounded-xl border border-white/10 bg-white/[0.04] px-3.5 text-sm text-white placeholder:text-gray-500 transition hover:border-white/20 focus:border-indigo-400 focus:bg-white/[0.06] focus:outline-none focus:ring-4 focus:ring-indigo-500/15';

    $label  = 'mb-1.5 block text-xs font-medium text-gray-400';

@endphp

<div

    class="relative isolate min-h-screen px-4 py-8 text-gray-100 [color-scheme:dark] sm:px-6 lg:px-8"

    x-data="{ advanced: @js($advancedCount > 0), del: null }"

>

    <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-[radial-gradient(ellipse_at_50%_0%,rgba(99,102,241,.16),transparent_70%)]" aria-hidden="true"></div>

    <div class="mx-auto max-w-7xl">

        {{-- ============================== HEADER ============================== --}}

        <div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

            <div>

                <nav class="mb-3 flex items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">

                    <a

                        href="{{ route('dashboard') }}"

                        class="transition-colors hover:text-gray-300 focus:outline-none focus-visible:text-gray-300"

                        aria-label="Dashboard"

                    >

                        <i class="fa-solid fa-house text-xs" aria-hidden="true"></i>

                    </a>

                    <span aria-hidden="true">/</span>

                    <span>Ticket management</span>

                    <span aria-hidden="true">/</span>

                    <span class="text-indigo-400" aria-current="page">Priorities</span>

                </nav>

                <h1 class="iris-display text-3xl font-bold tracking-tight text-white sm:text-4xl">

                    Ticket priorities

                </h1>

                <p class="mt-2 max-w-xl text-sm leading-6 text-gray-400">

                    Set how urgent each ticket is, and the response, resolution and escalation targets that go with it.

                </p>

            </div>

            @can('ticket.priority.create')

                <a

                    href="{{ route('ticket.priority.create') }}"

                    class="group relative inline-flex h-11 items-center justify-center gap-2 overflow-hidden rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 text-sm font-semibold text-white shadow-lg shadow-indigo-950/40 transition hover:shadow-indigo-500/25 focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/40 active:scale-[0.99]"

                >

                    <span

                        class="pointer-events-none absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-700 group-hover:translate-x-full"

                        aria-hidden="true"

                    ></span>

                    <i class="fa-solid fa-plus relative text-xs" aria-hidden="true"></i>

                    <span class="relative">Create priority</span>

                </a>

            @endcan

        </div>

        {{-- ============================== FEEDBACK ============================== --}}

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

                <button type="button" @click="show = false" aria-label="Dismiss" class="text-emerald-300/60 transition hover:text-emerald-200">

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

                <button type="button" @click="show = false" aria-label="Dismiss" class="text-red-300/60 transition hover:text-red-200">

                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>

                </button>

            </div>

        @endif

        {{-- ============================== FILTERS ============================== --}}

        <section class="mb-4 rounded-2xl border border-white/10 bg-gray-950/60 p-4 shadow-xl shadow-black/20 backdrop-blur-xl sm:p-5">

            <form id="priority-filters" action="{{ route('ticket.priority.list') }}" method="GET">

                @if (request()->filled('sort_by'))

                    <input type="hidden" name="sort_by" value="{{ $sortBy }}">

                    <input type="hidden" name="sort_order" value="{{ $sortOrder }}">

                @endif

                <div class="flex flex-col gap-3 sm:flex-row">

                    <div class="group relative flex-1">

                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">

                            <i class="fa-solid fa-magnifying-glass text-sm text-gray-500 transition-colors group-focus-within:text-indigo-400" aria-hidden="true"></i>

                        </div>

                        <label for="name" class="sr-only">Priority name</label>

                        <input

                            type="text"

                            name="name"

                            id="name"

                            value="{{ request('name') }}"

                            placeholder="Search by priority name"

                            class="{{ $field }} pl-10"

                        />

                    </div>

                    <div class="flex gap-2">

                        <button

                            type="button"

                            @click="advanced = !advanced"

                            :aria-expanded="advanced.toString()"

                            aria-controls="advanced-filters"

                            class="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-xl border border-white/10 px-4 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:bg-white/[0.05] hover:text-white focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/20 sm:flex-none"

                        >

                            <i class="fa-solid fa-sliders text-xs" aria-hidden="true"></i>

                            Filters

                            @if ($advancedCount)

                                <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-indigo-500 px-1.5 text-[11px] font-semibold text-white">

                                    {{ $advancedCount }}

                                </span>

                            @endif

                        </button>

                        <button

                            type="submit"

                            class="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 text-sm font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/40 active:scale-[0.99] sm:flex-none"

                        >

                            Apply

                        </button>

                    </div>

                </div>

                {{-- Advanced filters --}}
                <div
                    id="advanced-filters"
                    x-show="advanced"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="mt-4 grid grid-cols-1 gap-4 border-t border-white/[0.08] pt-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    {{-- Name matching --}}
                    <div>
                        <label for="name_ignore_case" class="{{ $label }}">Name matching</label>
                        <select name="name_ignore_case" id="name_ignore_case" class="{{ $field }} [&>option]:bg-gray-900">
                            <option value="1" @selected(request('name_ignore_case', '1') === '1')>Ignore case</option>
                            <option value="0" @selected(request('name_ignore_case') === '0')>Case-sensitive</option>
                        </select>
                    </div>

                    {{-- Code --}}
                    <div>
                        <label for="code" class="{{ $label }}">Priority code</label>
                        <input
                            type="text"
                            name="code"
                            id="code"
                            value="{{ request('code') }}"
                            placeholder="e.g. HIGH"
                            class="{{ $field }} font-mono"
                        />
                        <select name="code_ignore_case" aria-label="Priority code matching" class="mt-2 h-9 w-full rounded-lg border border-white/10 bg-white/[0.04] px-3 text-xs text-gray-300 focus:border-indigo-400 focus:outline-none [&>option]:bg-gray-900">
                            <option value="1" @selected(request('code_ignore_case', '1') === '1')>Ignore case</option>
                            <option value="0" @selected(request('code_ignore_case') === '0')>Case-sensitive</option>
                        </select>
                    </div>

                    {{-- Level --}}
                    <div>
                        <label for="level" class="{{ $label }}">Level</label>
                        <input
                            type="number"
                            name="level"
                            id="level"
                            value="{{ request('level') }}"
                            min="1"
                            max="32767"
                            step="1"
                            placeholder="e.g. 3"
                            class="{{ $field }}"
                        />
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="{{ $label }}">Description</label>
                        <input
                            type="text"
                            name="description"
                            id="description"
                            value="{{ request('description') }}"
                            placeholder="Search description..."
                            class="{{ $field }}"
                        />
                        <select name="description_ignore_case" aria-label="Description matching" class="mt-2 h-9 w-full rounded-lg border border-white/10 bg-white/[0.04] px-3 text-xs text-gray-300 focus:border-indigo-400 focus:outline-none [&>option]:bg-gray-900">
                            <option value="1" @selected(request('description_ignore_case', '1') === '1')>Ignore case</option>
                            <option value="0" @selected(request('description_ignore_case') === '0')>Case-sensitive</option>
                        </select>
                    </div>

                    {{-- Color --}}
                    <div>
                        <label for="color" class="{{ $label }}">Color</label>
                        <input
                            type="text"
                            name="color"
                            id="color"
                            value="{{ request('color') }}"
                            placeholder="e.g. #6366f1"
                            maxlength="30"
                            class="{{ $field }} font-mono"
                        />
                        <select name="color_ignore_case" aria-label="Color matching" class="mt-2 h-9 w-full rounded-lg border border-white/10 bg-white/[0.04] px-3 text-xs text-gray-300 focus:border-indigo-400 focus:outline-none [&>option]:bg-gray-900">
                            <option value="1" @selected(request('color_ignore_case', '1') === '1')>Ignore case</option>
                            <option value="0" @selected(request('color_ignore_case') === '0')>Case-sensitive</option>
                        </select>
                    </div>

                    {{-- Icon --}}
                    <div>
                        <label for="icon" class="{{ $label }}">Icon class</label>
                        <input
                            type="text"
                            name="icon"
                            id="icon"
                            value="{{ request('icon') }}"
                            placeholder="fa-solid fa-flag"
                            maxlength="100"
                            class="{{ $field }} font-mono"
                        />
                        <select name="icon_ignore_case" aria-label="Icon matching" class="mt-2 h-9 w-full rounded-lg border border-white/10 bg-white/[0.04] px-3 text-xs text-gray-300 focus:border-indigo-400 focus:outline-none [&>option]:bg-gray-900">
                            <option value="1" @selected(request('icon_ignore_case', '1') === '1')>Ignore case</option>
                            <option value="0" @selected(request('icon_ignore_case') === '0')>Case-sensitive</option>
                        </select>
                    </div>

                    {{-- Response time range --}}
                    <div>
                        <label class="{{ $label }}">Response time (minutes)</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input
                                type="number"
                                name="response_time_minutes_min"
                                id="response_time_minutes_min"
                                value="{{ request('response_time_minutes_min') }}"
                                min="1"
                                placeholder="Min"
                                aria-label="Minimum response time in minutes"
                                class="{{ $field }} min-w-0 px-2"
                            />
                            <input
                                type="number"
                                name="response_time_minutes_max"
                                id="response_time_minutes_max"
                                value="{{ request('response_time_minutes_max') }}"
                                min="1"
                                placeholder="Max"
                                aria-label="Maximum response time in minutes"
                                class="{{ $field }} min-w-0 px-2"
                            />
                        </div>
                    </div>

                    {{-- Resolution time range --}}
                    <div>
                        <label class="{{ $label }}">Resolution time (minutes)</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input
                                type="number"
                                name="resolution_time_minutes_min"
                                id="resolution_time_minutes_min"
                                value="{{ request('resolution_time_minutes_min') }}"
                                min="1"
                                placeholder="Min"
                                aria-label="Minimum resolution time in minutes"
                                class="{{ $field }} min-w-0 px-2"
                            />
                            <input
                                type="number"
                                name="resolution_time_minutes_max"
                                id="resolution_time_minutes_max"
                                value="{{ request('resolution_time_minutes_max') }}"
                                min="1"
                                placeholder="Max"
                                aria-label="Maximum resolution time in minutes"
                                class="{{ $field }} min-w-0 px-2"
                            />
                        </div>
                    </div>

                    {{-- Escalation time range --}}
                    <div>
                        <label class="{{ $label }}">Escalation time (minutes)</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input
                                type="number"
                                name="escalation_time_minutes_min"
                                id="escalation_time_minutes_min"
                                value="{{ request('escalation_time_minutes_min') }}"
                                min="1"
                                placeholder="Min"
                                aria-label="Minimum escalation time in minutes"
                                class="{{ $field }} min-w-0 px-2"
                            />
                            <input
                                type="number"
                                name="escalation_time_minutes_max"
                                id="escalation_time_minutes_max"
                                value="{{ request('escalation_time_minutes_max') }}"
                                min="1"
                                placeholder="Max"
                                aria-label="Maximum escalation time in minutes"
                                class="{{ $field }} min-w-0 px-2"
                            />
                        </div>
                    </div>

                    {{-- Availability --}}
                    <div>
                        <label for="is_active" class="{{ $label }}">Availability</label>
                        <select name="is_active" id="is_active" class="{{ $field }} [&>option]:bg-gray-900">
                            <option value="" @selected(request('is_active', '') === '')>Any</option>
                            <option value="1" @selected(request('is_active') === '1')>Active</option>
                            <option value="0" @selected(request('is_active') === '0')>Inactive</option>
                        </select>
                    </div>

                    {{-- Default --}}
                    <div>
                        <label for="is_default" class="{{ $label }}">Default priority</label>
                        <select name="is_default" id="is_default" class="{{ $field }} [&>option]:bg-gray-900">
                            <option value="" @selected(request('is_default', '') === '')>Any</option>
                            <option value="1" @selected(request('is_default') === '1')>Default only</option>
                            <option value="0" @selected(request('is_default') === '0')>Not default</option>
                        </select>
                    </div>

                    {{-- Soft Deletes --}}
                    <div>
                        <label for="trashed" class="{{ $label }}">Records</label>
                        <select name="trashed" id="trashed" class="{{ $field }} [&>option]:bg-gray-900">
                            <option value="" @selected(request('trashed', '') === '')>Active records only</option>
                            <option value="with" @selected(request('trashed') === 'with')>All records</option>
                            <option value="only" @selected(request('trashed') === 'only')>Deleted records only</option>
                        </select>
                    </div>
                </div>
            </form>

        </section>

        {{-- Filtros ativos --}}

        @if ($chips)

            <div class="mb-4 flex flex-wrap items-center gap-2" aria-label="Active filters">

                @foreach ($chips as $key => $chipLabel)

                    <a

                        href="{{ route('ticket.priority.list', request()->except([$key, 'page'])) }}"

                        class="group inline-flex items-center gap-2 rounded-full border border-indigo-400/20 bg-indigo-500/10 py-1 pl-3 pr-2.5 text-xs font-medium text-indigo-200 transition hover:border-indigo-400/40 hover:bg-indigo-500/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400/60"

                        title="Remove this filter"

                    >

                        {{ $chipLabel }}

                        <i class="fa-solid fa-xmark text-[10px] text-indigo-300/70 group-hover:text-white" aria-hidden="true"></i>

                        <span class="sr-only">Remove filter</span>

                    </a>

                @endforeach

                <a

                    href="{{ route('ticket.priority.list') }}"

                    class="ml-1 text-xs font-medium text-gray-400 transition hover:text-white focus:outline-none focus-visible:underline"

                >

                    Clear all

                </a>

            </div>

        @endif

        {{-- ============================== RESULTS ============================== --}}

        <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/20 backdrop-blur-xl">

            <div class="flex items-center justify-between gap-4 border-b border-white/[0.08] px-5 py-4 sm:px-6">

                <div class="flex items-center gap-3">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-300">

                        <i class="fa-solid fa-flag" aria-hidden="true"></i>

                    </div>

                    <div>

                        <h2 class="font-semibold text-white">Priority directory</h2>

                        <p class="mt-0.5 text-xs text-gray-500">

                            {{ $priorities->total() }} {{ \Illuminate\Support\Str::plural('priority', $priorities->total()) }} found

                        </p>

                    </div>

                </div>

                <span class="hidden text-xs text-gray-500 sm:block">

                    Page {{ $priorities->currentPage() }} of {{ max(1, $priorities->lastPage()) }}

                </span>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full min-w-[880px] text-left text-sm">

                    <thead class="border-b border-white/[0.08] bg-white/[0.02] text-xs text-gray-500">

                        <tr>

                            <th scope="col" class="px-6 py-3.5 font-medium" aria-sort="{{ $ariaSort('name') }}">

                                <a href="{{ $sortUrl('name') }}" class="inline-flex items-center gap-1.5 transition-colors hover:text-gray-200 focus:outline-none focus-visible:text-gray-200">

                                    Priority

                                    <i @class(['fa-solid text-[10px]', 'fa-sort' => $sortBy !== 'name', 'fa-sort-up text-indigo-300' => $sortBy === 'name' && $sortOrder === 'asc', 'fa-sort-down text-indigo-300' => $sortBy === 'name' && $sortOrder !== 'asc']) aria-hidden="true"></i>

                                </a>

                            </th>

                            <th scope="col" class="px-6 py-3.5 font-medium" aria-sort="{{ $ariaSort('level') }}">

                                <a href="{{ $sortUrl('level') }}" class="inline-flex items-center gap-1.5 transition-colors hover:text-gray-200 focus:outline-none focus-visible:text-gray-200">

                                    Level

                                    <i @class(['fa-solid text-[10px]', 'fa-sort' => $sortBy !== 'level', 'fa-sort-up text-indigo-300' => $sortBy === 'level' && $sortOrder === 'asc', 'fa-sort-down text-indigo-300' => $sortBy === 'level' && $sortOrder !== 'asc']) aria-hidden="true"></i>

                                </a>

                            </th>

                            <th scope="col" class="px-6 py-3.5 font-medium">SLA targets</th>

                            <th scope="col" class="px-6 py-3.5 font-medium">Status</th>

                            <th scope="col" class="px-6 py-3.5 text-right font-medium">Actions</th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-white/[0.06]">

                        @forelse ($priorities as $priority)

                            @php $color = $safeColor($priority->color); @endphp

                            <tr @class(['transition hover:bg-white/[0.03]', 'opacity-60' => $priority->trashed()])>

                                {{-- Priority --}}

                                <td class="px-6 py-4" style="box-shadow: inset 3px 0 0 {{ $color }}">

                                    <div class="flex items-center gap-3.5">

                                        <div

                                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border text-base"

                                            style="color: {{ $color }}; border-color: {{ $color }}40; background-color: {{ $color }}18;"

                                        >

                                            <i class="{{ $priority->icon ?: 'fa-solid fa-flag' }}" aria-hidden="true"></i>

                                        </div>

                                        <div class="min-w-0">

                                            <p class="truncate font-semibold text-white">{{ $priority->name }}</p>

                                            <div class="mt-1 flex items-center gap-2">

                                                <span

                                                    class="rounded-md bg-white/[0.06] px-1.5 py-0.5 font-mono text-[11px] text-gray-400"

                                                    title="ID {{ $priority->id }}"

                                                >

                                                    {{ $priority->code }}

                                                </span>

                                                @if ($priority->description)

                                                    <span class="hidden max-w-[16rem] truncate text-xs text-gray-500 lg:inline">

                                                        {{ $priority->description }}

                                                    </span>

                                                @endif

                                            </div>

                                        </div>

                                    </div>

                                </td>

                                {{-- Level --}}

                                <td class="px-6 py-4">

                                    <span

                                        class="inline-flex items-center rounded-lg border px-3 py-1.5 text-xs font-semibold"

                                        style="color: {{ $color }}; border-color: {{ $color }}55; background-color: {{ $color }}15;"

                                    >

                                        Level {{ $priority->level }}

                                    </span>

                                </td>

                                {{-- SLA --}}

                                <td class="px-6 py-4">

                                    <dl class="grid grid-cols-3 gap-5 text-xs">

                                        @foreach ([

                                            'Response'   => $priority->response_time_minutes,

                                            'Resolution' => $priority->resolution_time_minutes,

                                            'Escalation' => $priority->escalation_time_minutes,

                                        ] as $slaLabel => $slaValue)

                                            <div>

                                                <dt class="text-gray-600">{{ $slaLabel }}</dt>

                                                <dd class="mt-1 font-medium {{ $slaValue === null ? 'text-gray-600' : 'text-gray-200' }}">

                                                    {{ $minutes($slaValue) ?? '—' }}

                                                </dd>

                                            </div>

                                        @endforeach

                                    </dl>

                                </td>

                                {{-- Status --}}

                                <td class="px-6 py-4">

                                    <div class="flex flex-col items-start gap-1.5">

                                        @if ($priority->trashed())

                                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-red-500/20 bg-red-500/10 px-2.5 py-1 text-xs font-medium text-red-300">

                                                <i class="fa-solid fa-trash-can text-[10px]" aria-hidden="true"></i>

                                                Deleted

                                            </span>

                                        @elseif ($priority->is_active)

                                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-300">

                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400" aria-hidden="true"></span>

                                                Active

                                            </span>

                                        @else

                                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-500/20 bg-gray-500/10 px-2.5 py-1 text-xs font-medium text-gray-400">

                                                <span class="h-1.5 w-1.5 rounded-full bg-gray-500" aria-hidden="true"></span>

                                                Inactive

                                            </span>

                                        @endif

                                        @if ($priority->is_default)

                                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-300">

                                                <i class="fa-solid fa-star text-[10px]" aria-hidden="true"></i>

                                                Default

                                            </span>

                                        @endif

                                    </div>

                                </td>

                                {{-- Actions --}}

                                <td class="px-6 py-4">

                                    <div class="flex items-center justify-end gap-2">

                                        @if (Route::has('ticket.priority.show-details'))

                                            <a

                                                href="{{ route('ticket.priority.show-details', $priority->id) }}"

                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 text-gray-400 transition hover:border-indigo-500/30 hover:bg-indigo-500/10 hover:text-indigo-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400/60"

                                                title="View details"

                                                aria-label="View details for {{ $priority->name }}"

                                            >

                                                <i class="fa-regular fa-eye" aria-hidden="true"></i>

                                            </a>

                                        @endif

                                        @if (! $priority->trashed())

                                            @can('ticket.priority.update')

                                                <a

                                                    href="{{ route('ticket.priority.edit', $priority->id) }}"

                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 text-gray-400 transition hover:border-amber-500/30 hover:bg-amber-500/10 hover:text-amber-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400/60"

                                                    title="Edit priority"

                                                    aria-label="Edit {{ $priority->name }}"

                                                >

                                                    <i class="fa-solid fa-pen" aria-hidden="true"></i>

                                                </a>

                                            @endcan

                                            @can('ticket.priority.delete')

                                                <button

                                                    type="button"

                                                    @click="del = { url: @js(route('ticket.priority.destroy', $priority->id)), name: @js($priority->name) }"

                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 text-gray-400 transition hover:border-red-500/30 hover:bg-red-500/10 hover:text-red-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-400/60"

                                                    title="Delete priority"

                                                    aria-label="Delete {{ $priority->name }}"

                                                >

                                                    <i class="fa-regular fa-trash-can" aria-hidden="true"></i>

                                                </button>

                                            @endcan

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5" class="px-6 py-20 text-center">

                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-white/[0.03] text-gray-500">

                                        <i class="fa-solid fa-flag text-2xl" aria-hidden="true"></i>

                                    </div>

                                    @if ($chips)

                                        <h3 class="mt-5 font-semibold text-white">No priorities match these filters</h3>

                                        <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500">

                                            Try removing a filter or searching for something broader.

                                        </p>

                                        <a

                                            href="{{ route('ticket.priority.list') }}"

                                            class="mt-5 inline-flex items-center gap-2 text-sm font-medium text-indigo-400 transition hover:text-indigo-300"

                                        >

                                            <i class="fa-solid fa-rotate-left text-xs" aria-hidden="true"></i>

                                            Clear filters

                                        </a>

                                    @else

                                        <h3 class="mt-5 font-semibold text-white">No priorities yet</h3>

                                        <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500">

                                            Create your first priority to start ranking tickets by urgency.

                                        </p>

                                        @can('ticket.priority.create')

                                            <a

                                                href="{{ route('ticket.priority.create') }}"

                                                class="mt-5 inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-500"

                                            >

                                                <i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>

                                                Create priority

                                            </a>

                                        @endcan

                                    @endif

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{-- Footer: intervalo, itens por página e paginação --}}

            @if ($priorities->total() > 0)

                <div class="flex flex-col gap-4 border-t border-white/[0.08] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">

                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-gray-500">

                        <p>

                            Showing

                            <span class="font-medium text-gray-300">{{ $priorities->firstItem() }}–{{ $priorities->lastItem() }}</span>

                            of

                            <span class="font-medium text-gray-300">{{ $priorities->total() }}</span>

                        </p>

                        <label class="flex items-center gap-2">

                            Rows per page

                            <select

                                form="priority-filters"

                                name="per_page"

                                @change="$el.form.submit()"

                                class="h-8 rounded-lg border border-white/10 bg-white/[0.04] px-2 text-xs text-gray-200 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 [&>option]:bg-gray-900"

                            >

                                @foreach ([15, 25, 50, 100] as $size)

                                    <option value="{{ $size }}" @selected((int) request('per_page', 15) === $size)>{{ $size }}</option>

                                @endforeach

                            </select>

                        </label>

                    </div>

                    @if ($priorities->hasPages())

                        @php

                            $current = $priorities->currentPage();

                            $last    = $priorities->lastPage();

                            $start   = max(1, $current - 2);

                            $end     = min($last, $current + 2);

                            $pageBtn = 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-2.5 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400/60';

                        @endphp

                        <nav class="flex flex-wrap items-center gap-1.5" aria-label="Pagination">

                            @if ($priorities->onFirstPage())

                                <span class="{{ $pageBtn }} cursor-not-allowed border-white/[0.06] text-gray-700" aria-disabled="true">

                                    <i class="fa-solid fa-chevron-left text-xs" aria-hidden="true"></i>

                                    <span class="sr-only">Previous page</span>

                                </span>

                            @else

                                <a href="{{ $priorities->previousPageUrl() }}" rel="prev" class="{{ $pageBtn }} border-white/10 text-gray-300 hover:bg-white/[0.06] hover:text-white">

                                    <i class="fa-solid fa-chevron-left text-xs" aria-hidden="true"></i>

                                    <span class="sr-only">Previous page</span>

                                </a>

                            @endif

                            @if ($start > 1)

                                <a href="{{ $priorities->url(1) }}" class="{{ $pageBtn }} border-white/10 text-gray-300 hover:bg-white/[0.06] hover:text-white">1</a>

                                @if ($start > 2)

                                    <span class="px-1 text-gray-600" aria-hidden="true">…</span>

                                @endif

                            @endif

                            @foreach ($priorities->getUrlRange($start, $end) as $page => $url)

                                @if ($page === $current)

                                    <span class="{{ $pageBtn }} border-indigo-500/40 bg-indigo-500/20 text-white" aria-current="page">{{ $page }}</span>

                                @else

                                    <a href="{{ $url }}" class="{{ $pageBtn }} border-white/10 text-gray-300 hover:bg-white/[0.06] hover:text-white" aria-label="Go to page {{ $page }}">{{ $page }}</a>

                                @endif

                            @endforeach

                            @if ($end < $last)

                                @if ($end < $last - 1)

                                    <span class="px-1 text-gray-600" aria-hidden="true">…</span>

                                @endif

                                <a href="{{ $priorities->url($last) }}" class="{{ $pageBtn }} border-white/10 text-gray-300 hover:bg-white/[0.06] hover:text-white">{{ $last }}</a>

                            @endif

                            @if ($priorities->hasMorePages())

                                <a href="{{ $priorities->nextPageUrl() }}" rel="next" class="{{ $pageBtn }} border-white/10 text-gray-300 hover:bg-white/[0.06] hover:text-white">

                                    <i class="fa-solid fa-chevron-right text-xs" aria-hidden="true"></i>

                                    <span class="sr-only">Next page</span>

                                </a>

                            @else

                                <span class="{{ $pageBtn }} cursor-not-allowed border-white/[0.06] text-gray-700" aria-disabled="true">

                                    <i class="fa-solid fa-chevron-right text-xs" aria-hidden="true"></i>

                                    <span class="sr-only">Next page</span>

                                </span>

                            @endif

                        </nav>

                    @endif

                </div>

            @endif

        </section>

    </div>

    {{-- ============================== DELETE MODAL ============================== --}}

    <div

        x-show="del"

        x-cloak

        @keydown.escape.window="del = null"

        class="fixed inset-0 z-[60] flex items-center justify-center p-4"

        role="dialog"

        aria-modal="true"

        aria-labelledby="delete-title"

    >

        <div

            x-show="del"

            x-transition.opacity

            @click="del = null"

            class="absolute inset-0 bg-black/70 backdrop-blur-sm"

            aria-hidden="true"

        ></div>

        <div

            x-show="del"

            x-transition:enter="transition ease-out duration-200"

            x-transition:enter-start="opacity-0 scale-95"

            x-transition:enter-end="opacity-100 scale-100"

            class="relative w-full max-w-md rounded-2xl border border-white/10 bg-[#0d0d17] p-6 shadow-2xl shadow-black/60"

        >

            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-500/10 text-red-300">

                <i class="fa-regular fa-trash-can text-lg" aria-hidden="true"></i>

            </div>

            <h3 id="delete-title" class="mt-4 text-lg font-semibold text-white">Delete this priority?</h3>

            <p class="mt-2 text-sm leading-6 text-gray-400">

                <span class="font-medium text-gray-200" x-text="del ? del.name : ''"></span>

                will be moved to deleted records.

            </p>

            <form :action="del ? del.url : '#'" method="POST" class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                @csrf

                @method('DELETE')

                <button

                    type="button"

                    @click="del = null"

                    class="inline-flex h-11 items-center justify-center rounded-xl border border-white/10 px-5 text-sm font-medium text-gray-300 transition hover:bg-white/[0.06] hover:text-white focus:outline-none focus-visible:ring-4 focus-visible:ring-white/10"

                >

                    Cancel

                </button>

                <button

                    type="submit"

                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-red-600 px-5 text-sm font-semibold text-white transition hover:bg-red-500 focus:outline-none focus-visible:ring-4 focus-visible:ring-red-500/40"

                >

                    <i class="fa-regular fa-trash-can text-xs" aria-hidden="true"></i>

                    Delete priority

                </button>

            </form>

        </div>

    </div>

</div>

@endsection