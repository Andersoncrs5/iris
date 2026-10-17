@extends('layouts.base')

@section('title', 'Tags - Iris App')

@section('content')

@php
    $sortBy = request('sort_by', 'created_at');
    $sortOrder = request('sort_order', 'desc');

    $selectedTypes = request()->query('type', []);
    $selectedTypes = is_array($selectedTypes)
        ? array_values(array_filter($selectedTypes, 'is_string'))
        : (is_string($selectedTypes) && $selectedTypes !== ''
            ? [$selectedTypes]
            : []);

    $activeFilterKeys = [
        'name',
        'slug',
        'color',
        'description',
        'usage_count_min',
        'usage_count_max',
        'is_active',
        'trashed',
    ];

    $activeFilterCount = collect($activeFilterKeys)
        ->filter(fn ($key) => request()->filled($key))
        ->count();

    if ($selectedTypes !== []) {
        $activeFilterCount++;
    }

    $typeLabels = [
        'GENERAL' => 'General',
        'SYSTEM' => 'System',
        'CONTENT' => 'Content',
        'PRODUCT' => 'Product',
        'STATUS' => 'Status',
    ];

    $filterLabels = [
        'name' => fn ($value) => 'Name: ' . $value,
        'slug' => fn ($value) => 'Slug: ' . $value,
        'color' => fn ($value) => 'Color: ' . $value,
        'description' => fn ($value) => 'Description: ' . $value,
        'usage_count_min' => fn ($value) => 'Usage ≥ ' . $value,
        'usage_count_max' => fn ($value) => 'Usage ≤ ' . $value,
        'is_active' => fn ($value) => $value === '1' ? 'Active' : 'Inactive',
        'trashed' => fn ($value) => $value === 'with'
            ? 'Include archived'
            : 'Archived only',
    ];

    $chips = [];

    foreach ($filterLabels as $key => $resolver) {
        $value = request()->query($key);

        if (request()->filled($key) && !is_array($value)) {
            $chips[$key] = $resolver((string) $value);
        }
    }

    if ($selectedTypes !== []) {
        $chips['type'] = 'Type: ' . collect($selectedTypes)
            ->map(fn ($type) => $typeLabels[$type] ?? $type)
            ->implode(', ');
    }

    $field = 'w-full rounded-xl border border-white/10 bg-gray-950/60 px-3.5 py-2.5 text-sm text-white placeholder:text-gray-600 outline-none transition hover:border-white/20 focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10';

    $label = 'mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400';

    $select = $field . ' [&>option]:bg-gray-900';

    $caseOptions = [
        '0' => 'Match case',
        '1' => 'Ignore case',
    ];
@endphp

<div x-data class="min-h-full">

    {{-- PAGE HEADER --}}
    <div class="mb-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="mb-3 flex items-center gap-2">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400 ring-1 ring-indigo-500/20">
                        <i class="fa-solid fa-tags" aria-hidden="true"></i>
                    </span>

                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-400">
                        Catalog
                    </span>
                </div>

                <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                    Tags
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                    Organize and manage the tags used across your support environment.
                    Search, filter and maintain your tag catalog.
                </p>
            </div>

            @if (Route::has('tag.create'))
                <a
                    href="{{ route('tag.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition duration-200 hover:-translate-y-0.5 hover:bg-indigo-500 active:translate-y-0"
                >
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    New Tag
                </a>
            @endif
        </div>

        {{-- SUMMARY --}}
        <div class="mt-7 grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-white/5 bg-gray-900/70 px-5 py-4 shadow-xl shadow-black/5 backdrop-blur">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Results</p>
                        <p class="mt-1 text-2xl font-black text-white">{{ $tags->total() }}</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400">
                        <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-white/5 bg-gray-900/70 px-5 py-4 shadow-xl shadow-black/5 backdrop-blur">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">On this page</p>
                        <p class="mt-1 text-2xl font-black text-white">{{ $tags->count() }}</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400">
                        <i class="fa-solid fa-list-check" aria-hidden="true"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-white/5 bg-gray-900/70 px-5 py-4 shadow-xl shadow-black/5 backdrop-blur">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Active filters</p>
                        <p class="mt-1 text-2xl font-black text-white">{{ $activeFilterCount }}</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                        <i class="fa-solid fa-filter" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">

        {{-- FILTER SIDEBAR --}}
        <aside class="h-fit xl:sticky xl:top-6">
            <details open class="group overflow-hidden rounded-2xl border border-white/5 bg-gray-900/80 shadow-2xl shadow-black/10">

                <summary class="flex cursor-pointer list-none items-center justify-between px-5 py-4 [&::-webkit-details-marker]:hidden">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/5 text-gray-300">
                            <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                        </div>

                        <div>
                            <h2 class="text-sm font-bold text-white">Filters</h2>
                            <p class="text-xs text-gray-500">Refine your tag results</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if ($activeFilterCount > 0)
                            <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-indigo-500/15 px-2 text-[11px] font-bold text-indigo-400">
                                {{ $activeFilterCount }}
                            </span>
                        @endif

                        <i class="fa-solid fa-chevron-down text-xs text-gray-500 transition-transform duration-200 group-open:rotate-180" aria-hidden="true"></i>
                    </div>
                </summary>

                <div class="border-t border-white/5 p-5">
                    <form method="GET" action="{{ route('tag.list') }}" class="space-y-5">

                        {{-- NAME --}}
                        <div>
                            <label for="name" class="{{ $label }}">Name</label>

                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-500" aria-hidden="true"></i>

                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    value="{{ request('name') }}"
                                    placeholder="Search tag names..."
                                    class="{{ $field }} pl-10"
                                >
                            </div>

                            <select name="name_ignore_case" class="{{ $select }} mt-2">
                                @foreach ($caseOptions as $value => $text)
                                    <option value="{{ $value }}" @selected(request('name_ignore_case', '0') === $value)>
                                        {{ $text }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- SLUG --}}
                        <div>
                            <label for="slug" class="{{ $label }}">Slug</label>

                            <input
                                type="text"
                                id="slug"
                                name="slug"
                                value="{{ request('slug') }}"
                                placeholder="e.g. customer-feedback"
                                class="{{ $field }}"
                            >

                            <select name="slug_ignore_case" class="{{ $select }} mt-2">
                                @foreach ($caseOptions as $value => $text)
                                    <option value="{{ $value }}" @selected(request('slug_ignore_case', '0') === $value)>
                                        {{ $text }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- COLOR --}}
                        <div>
                            <label for="color" class="{{ $label }}">Color</label>

                            <input
                                type="text"
                                id="color"
                                name="color"
                                value="{{ request('color') }}"
                                placeholder="e.g. #818cf8"
                                class="{{ $field }}"
                            >

                            <select name="color_ignore_case" class="{{ $select }} mt-2">
                                @foreach ($caseOptions as $value => $text)
                                    <option value="{{ $value }}" @selected(request('color_ignore_case', '0') === $value)>
                                        {{ $text }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- TYPE --}}
                        <div>
                            <label for="type" class="{{ $label }}">Type</label>

                            <select
                                id="type"
                                name="type[]"
                                multiple
                                size="5"
                                class="{{ $field }} h-auto min-h-32"
                            >
                                @foreach ($typeLabels as $value => $text)
                                    <option
                                        value="{{ $value }}"
                                        @selected(in_array($value, $selectedTypes, true))
                                    >
                                        {{ $text }}
                                    </option>
                                @endforeach
                            </select>

                            <p class="mt-1.5 text-xs leading-5 text-gray-500">
                                Select multiple types with Ctrl or Cmd. Leave empty to include all types.
                            </p>
                        </div>

                        {{-- DESCRIPTION --}}
                        <div>
                            <label for="description" class="{{ $label }}">Description</label>

                            <input
                                type="text"
                                id="description"
                                name="description"
                                value="{{ request('description') }}"
                                placeholder="Search descriptions..."
                                class="{{ $field }}"
                            >

                            <select name="description_ignore_case" class="{{ $select }} mt-2">
                                @foreach ($caseOptions as $value => $text)
                                    <option value="{{ $value }}" @selected(request('description_ignore_case', '0') === $value)>
                                        {{ $text }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- ACTIVE STATUS --}}
                        <div>
                            <label for="is_active" class="{{ $label }}">Status</label>

                            <select id="is_active" name="is_active" class="{{ $select }}">
                                <option value="" @selected(request('is_active') === null || request('is_active') === '')>
                                    All statuses
                                </option>

                                <option value="1" @selected(request('is_active') === '1')>
                                    Active
                                </option>

                                <option value="0" @selected(request('is_active') === '0')>
                                    Inactive
                                </option>
                            </select>
                        </div>

                        {{-- USAGE COUNT --}}
                        <div>
                            <label class="{{ $label }}">Usage count range</label>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label for="usage_count_min" class="mb-1 block text-[11px] text-gray-500">Minimum</label>

                                    <input
                                        type="number"
                                        id="usage_count_min"
                                        name="usage_count_min"
                                        value="{{ request('usage_count_min') }}"
                                        placeholder="Min"
                                        min="0"
                                        step="1"
                                        class="{{ $field }}"
                                    >
                                </div>

                                <div>
                                    <label for="usage_count_max" class="mb-1 block text-[11px] text-gray-500">Maximum</label>

                                    <input
                                        type="number"
                                        id="usage_count_max"
                                        name="usage_count_max"
                                        value="{{ request('usage_count_max') }}"
                                        placeholder="Max"
                                        min="0"
                                        step="1"
                                        class="{{ $field }}"
                                    >
                                </div>
                            </div>

                            <p class="mt-1.5 text-xs text-gray-500">
                                Both limits are inclusive.
                            </p>
                        </div>

                        {{-- SOFT DELETES --}}
                        <div>
                            <label for="trashed" class="{{ $label }}">Archived items</label>

                            <select id="trashed" name="trashed" class="{{ $select }}">
                                <option value="" @selected(request('trashed', '') === '')>
                                    Active records only
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
                            <label class="{{ $label }}">Sorting</label>

                            <div class="grid grid-cols-[1fr_auto] gap-2">
                                <select name="sort_by" class="{{ $select }}">
                                    @foreach (\App\Utils\Enums\Tag\TagColumnEnum::cases() as $column)
                                        <option
                                            value="{{ $column->value }}"
                                            @selected($sortBy === $column->value)
                                        >
                                            {{ ucwords(str_replace('_', ' ', $column->value)) }}
                                        </option>
                                    @endforeach
                                </select>

                                <select name="sort_order" class="{{ $select }} w-24">
                                    <option value="desc" @selected($sortOrder === 'desc')>
                                        Desc ↓
                                    </option>

                                    <option value="asc" @selected($sortOrder === 'asc')>
                                        Asc ↑
                                    </option>
                                </select>
                            </div>
                        </div>

                        {{-- PER PAGE --}}
                        <div>
                            <label for="per_page" class="{{ $label }}">Results per page</label>

                            <select id="per_page" name="per_page" class="{{ $select }}">
                                @foreach ([15, 25, 50, 100] as $size)
                                    <option
                                        value="{{ $size }}"
                                        @selected((int) request('per_page', 15) === $size)
                                    >
                                        {{ $size }} results
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- ACTIONS --}}
                        <div class="grid grid-cols-2 gap-2 border-t border-white/5 pt-4">
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500 active:scale-[0.98]"
                            >
                                <i class="fa-solid fa-filter" aria-hidden="true"></i>
                                Apply
                            </button>

                            <a
                                href="{{ route('tag.list') }}"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-gray-300 transition hover:bg-white/10 hover:text-white active:scale-[0.98]"
                            >
                                <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                                Clear
                            </a>
                        </div>
                    </form>
                </div>
            </details>
        </aside>

        {{-- TAG RESULTS --}}
        <main class="min-w-0">

            {{-- RESULTS TOOLBAR --}}
            <div class="mb-4 flex flex-col gap-3 rounded-2xl border border-white/5 bg-gray-900/70 p-4 shadow-xl shadow-black/5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-white">Tag collection</span>

                        @if ($tags->total() > 0)
                            <span class="rounded-full bg-white/5 px-2 py-0.5 text-[11px] font-semibold text-gray-400">
                                {{ $tags->total() }} total
                            </span>
                        @endif
                    </div>

                    <p class="mt-1 text-xs text-gray-500">
                        Manage your available tags and their usage.
                    </p>
                </div>

                @if ($tags->total() > 0)
                    <div class="text-xs text-gray-500">
                        Showing
                        <span class="font-semibold text-gray-300">{{ $tags->firstItem() }}–{{ $tags->lastItem() }}</span>
                        of
                        <span class="font-semibold text-gray-300">{{ $tags->total() }}</span>
                    </div>
                @endif
            </div>

            {{-- ACTIVE FILTER CHIPS --}}
            @if ($chips)
                <div class="mb-4 flex flex-wrap items-center gap-2" aria-label="Active filters">
                    @foreach ($chips as $key => $chipLabel)
                        <a
                            href="{{ route('tag.list', request()->except([$key, 'page'])) }}"
                            class="group inline-flex items-center gap-2 rounded-full border border-indigo-400/20 bg-indigo-500/10 py-1.5 pl-3 pr-2.5 text-xs font-medium text-indigo-200 transition hover:border-indigo-400/40 hover:bg-indigo-500/20"
                            title="Remove this filter"
                        >
                            <span>{{ $chipLabel }}</span>
                            <i class="fa-solid fa-xmark text-[10px] text-indigo-300/70 group-hover:text-white" aria-hidden="true"></i>
                        </a>
                    @endforeach

                    <a
                        href="{{ route('tag.list') }}"
                        class="ml-1 text-xs font-medium text-gray-400 transition hover:text-white"
                    >
                        Clear all
                    </a>
                </div>
            @endif

            {{-- EMPTY STATE --}}
            @if ($tags->isEmpty())
                <div class="rounded-2xl border border-dashed border-white/10 bg-gray-900/50 px-6 py-16 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-500/10 text-2xl text-indigo-400">
                        <i class="fa-solid fa-tags" aria-hidden="true"></i>
                    </div>

                    <h3 class="mt-5 text-lg font-bold text-white">No tags found</h3>

                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-gray-500">
                        No tags match your current filters. Try changing your search criteria or create a new tag.
                    </p>

                    <div class="mt-6 flex justify-center gap-3">
                        <a
                            href="{{ route('tag.list') }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-gray-300 transition hover:bg-white/10 hover:text-white"
                        >
                            <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                            Clear filters
                        </a>

                        @if (Route::has('tag.create'))
                            <a
                                href="{{ route('tag.create') }}"
                                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-500"
                            >
                                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                Create tag
                            </a>
                        @endif
                    </div>
                </div>
            @else

                {{-- TAG CARDS --}}
                <div class="space-y-3">
                    @foreach ($tags as $tag)
                        @php
                            $typeLabel = $typeLabels[$tag->type]
                                ?? str($tag->type)->lower()->headline();
                        @endphp

                        <div
                            x-data="{ open: false, showDeleteModal: false }"
                            class="group relative overflow-visible rounded-2xl border border-white/5 bg-gray-900/80 p-5 shadow-xl shadow-black/5 transition duration-200 hover:-translate-y-0.5 hover:border-white/10 hover:bg-gray-900"
                        >
                            <div class="flex items-start gap-4">

                                {{-- TAG COLOR --}}
                                <div class="pt-1">
                                    <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-white/5 bg-gray-950/60">
                                        @if ($tag->color)
                                            <span
                                                class="h-5 w-5 rounded-full ring-4 ring-white/5"
                                                style="background-color: {{ $tag->color }};"
                                            ></span>
                                        @else
                                            <i class="fa-solid fa-tag text-sm text-gray-500" aria-hidden="true"></i>
                                        @endif
                                    </div>
                                </div>

                                {{-- TAG CONTENT --}}
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="truncate text-base font-bold text-white">
                                            {{ $tag->name }}
                                        </h3>

                                        <span class="inline-flex items-center rounded-full border border-indigo-500/20 bg-indigo-500/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-indigo-300">
                                            {{ $typeLabel }}
                                        </span>

                                        @if ($tag->is_active)
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-300">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-red-500/20 bg-red-500/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-red-300">
                                                <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
                                                Inactive
                                            </span>
                                        @endif

                                        @if (method_exists($tag, 'trashed') && $tag->trashed())
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-red-500/20 bg-red-500/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-red-300">
                                                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                                Archived
                                            </span>
                                        @endif
                                    </div>

                                    @if ($tag->description)
                                        <p class="mt-2 max-w-3xl whitespace-pre-line break-words text-sm leading-6 text-gray-400">
                                            {{ $tag->description }}
                                        </p>
                                    @endif

                                    {{-- META --}}
                                    <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-gray-500">
                                        <span class="inline-flex items-center gap-1.5">
                                            <i class="fa-solid fa-link text-[10px] text-gray-600" aria-hidden="true"></i>
                                            <span class="text-gray-400">{{ $tag->slug }}</span>
                                        </span>

                                        <span class="h-3 w-px bg-white/10"></span>

                                        <span class="inline-flex items-center gap-1.5">
                                            <i class="fa-solid fa-chart-simple text-[10px] text-gray-600" aria-hidden="true"></i>
                                            {{ number_format($tag->usage_count) }}
                                            {{ $tag->usage_count === 1 ? 'use' : 'uses' }}
                                        </span>

                                        <span class="h-3 w-px bg-white/10"></span>

                                        <span class="inline-flex items-center gap-1.5">
                                            <i class="fa-regular fa-calendar text-[10px] text-gray-600" aria-hidden="true"></i>
                                            Created {{ $tag->created_at?->format('d M Y') ?? '—' }}
                                        </span>
                                    </div>
                                </div>

                                {{-- ACTION MENU --}}
                                <div class="relative flex-shrink-0">
                                    <button
                                        type="button"
                                        @click="open = !open"
                                        :aria-expanded="open.toString()"
                                        aria-haspopup="true"
                                        aria-label="Tag actions"
                                        class="flex h-9 w-9 items-center justify-center rounded-xl border border-transparent text-gray-500 transition hover:border-white/10 hover:bg-white/5 hover:text-gray-200"
                                    >
                                        <i class="fa-solid fa-ellipsis-vertical" aria-hidden="true"></i>
                                    </button>

                                    <div
                                        x-show="open"
                                        x-cloak
                                        x-transition
                                        @click.outside="open = false"
                                        class="absolute right-0 z-30 mt-2 w-48 overflow-hidden rounded-xl border border-white/10 bg-gray-900 p-1 shadow-2xl shadow-black/30"
                                    >
                                        @if (Route::has('tag.edit'))
                                            <a
                                                href="{{ route('tag.edit', $tag->id) }}"
                                                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-300 transition hover:bg-white/5 hover:text-white"
                                            >
                                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-400">
                                                    <i class="fa-solid fa-pen-to-square text-xs" aria-hidden="true"></i>
                                                </span>
                                                Edit tag
                                            </a>
                                        @endif

                                        @if (Route::has('tag.toggle-active'))
                                            <form action="{{ route('tag.toggle-active', $tag->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm text-gray-300 transition hover:bg-white/5 hover:text-white"
                                                >
                                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                                                        <i class="fa-solid {{ $tag->is_active ? 'fa-toggle-off' : 'fa-toggle-on' }} text-xs" aria-hidden="true"></i>
                                                    </span>

                                                    {{ $tag->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        @endif

                                        <div class="my-1 border-t border-white/5"></div>

                                        @if (Route::has('tag.destroy'))
                                            <button
                                                type="button"
                                                @click="showDeleteModal = true; open = false"
                                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm text-red-400 transition hover:bg-red-500/10 hover:text-red-300"
                                            >
                                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-500/10 text-red-400">
                                                    <i class="fa-solid fa-trash-can text-xs" aria-hidden="true"></i>
                                                </span>
                                                Delete tag
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- DELETE MODAL --}}
                            @if (Route::has('tag.destroy'))
                                <div
                                    x-show="showDeleteModal"
                                    x-cloak
                                    x-transition.opacity
                                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4 backdrop-blur-sm"
                                >
                                    <div
                                        x-show="showDeleteModal"
                                        x-transition
                                        @click.outside="showDeleteModal = false"
                                        @keydown.escape.window="showDeleteModal = false"
                                        class="w-full max-w-md overflow-hidden rounded-2xl border border-white/10 bg-gray-900 shadow-2xl shadow-black/50"
                                    >
                                        <div class="border-b border-white/5 px-6 py-5">
                                            <div class="flex items-start gap-4">
                                                <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-red-500/10 text-red-400 ring-1 ring-red-500/10">
                                                    <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                                                </div>

                                                <div>
                                                    <h3 class="text-lg font-bold text-white">Delete tag</h3>
                                                    <p class="mt-1 text-sm text-gray-500">This action requires confirmation.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="space-y-4 px-6 py-5">
                                            <p class="text-sm leading-6 text-gray-400">
                                                You are about to delete
                                                <strong class="font-semibold text-white">"{{ $tag->name }}"</strong>.
                                            </p>

                                            <div class="rounded-xl border border-red-500/10 bg-red-500/5 p-4">
                                                <div class="flex gap-3">
                                                    <i class="fa-solid fa-circle-info mt-0.5 text-red-400" aria-hidden="true"></i>

                                                    <div class="text-sm leading-6 text-gray-400">
                                                        <p class="font-semibold text-gray-200">What will happen?</p>

                                                        <ul class="mt-2 space-y-1.5 text-gray-500">
                                                            <li>• The tag will be removed from the active list.</li>
                                                            <li>• Existing references may be affected, depending on your deletion rules.</li>
                                                            <li>• Restoration depends on your application's support for restoring deleted tags.</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-3 rounded-xl border border-white/5 bg-gray-950/50 p-3">
                                                <div class="flex h-10 w-10 items-center justify-center rounded-lg border border-white/5">
                                                    @if ($tag->color)
                                                        <span class="h-4 w-4 rounded-full" style="background-color: {{ $tag->color }};"></span>
                                                    @else
                                                        <i class="fa-solid fa-tag text-xs text-gray-500" aria-hidden="true"></i>
                                                    @endif
                                                </div>

                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-semibold text-white">{{ $tag->name }}</p>
                                                    <p class="truncate text-xs text-gray-500">{{ $tag->slug }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex gap-3 border-t border-white/5 bg-gray-950/30 px-6 py-4">
                                            <button
                                                @click="showDeleteModal = false"
                                                type="button"
                                                class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-gray-300 transition hover:bg-white/10 hover:text-white"
                                            >
                                                Cancel
                                            </button>

                                            <form action="{{ route('tag.destroy', $tag->id) }}" method="POST" class="w-full">
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-red-600/20 transition hover:bg-red-500 active:scale-[0.98]"
                                                >
                                                    <i class="fa-solid fa-trash-can mr-1.5" aria-hidden="true"></i>
                                                    Delete tag
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- PAGINATION --}}
                @if ($tags->hasPages())
                    <div class="mt-6 overflow-hidden rounded-2xl border border-white/5 bg-gray-900/60 p-4">
                        {{ $tags->withQueryString()->links() }}
                    </div>
                @endif
            @endif
        </main>
    </div>
</div>
@endsection