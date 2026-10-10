@extends('layouts.base')

@section('title', 'Tags - Iris App')

@section('content')

@php
    $sortBy = request('sort_by', 'created_at');
    $sortOrder = request('sort_order', 'desc');

    $activeFilterCount = collect([
        request('name'),
        request('type'),
        request('is_active'),
        request('usage_count_min'),
        request('usage_count_max'),
        request('trashed'),
    ])->filter(fn ($value) => filled($value))->count();
@endphp

<div
    x-data
    class="min-h-full"
>

    {{-- =========================================================
         PAGE HEADER
    ========================================================== --}}
    <div class="mb-8">

        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

            <div>

                <div class="flex items-center gap-2 mb-3">
                    <span
                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl
                               bg-indigo-500/10 text-indigo-400 ring-1 ring-indigo-500/20"
                    >
                        <i class="fa-solid fa-tags"></i>
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
                    Create new tags, control their status and keep your catalog clean.
                </p>

            </div>


            {{-- CREATE BUTTON --}}
            @if(Route::has('tag.create'))
                <a
                    href="{{ route('tag.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl
                           bg-indigo-600 px-5 py-3 text-sm font-bold text-white
                           shadow-lg shadow-indigo-600/20
                           transition duration-200
                           hover:-translate-y-0.5 hover:bg-indigo-500
                           active:translate-y-0"
                >
                    <i class="fa-solid fa-plus"></i>
                    New Tag
                </a>
            @endif

        </div>


        {{-- =====================================================
             SUMMARY
        ====================================================== --}}
        <div class="mt-7 grid grid-cols-1 gap-3 sm:grid-cols-3">

            {{-- Results --}}
            <div
                class="rounded-2xl border border-white/5 bg-gray-900/70 px-5 py-4
                       shadow-xl shadow-black/5 backdrop-blur"
            >
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            Results
                        </p>

                        <p class="mt-1 text-2xl font-black text-white">
                            {{ $tags->total() }}
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>

                </div>
            </div>


            {{-- Current page --}}
            <div
                class="rounded-2xl border border-white/5 bg-gray-900/70 px-5 py-4
                       shadow-xl shadow-black/5 backdrop-blur"
            >
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            On this page
                        </p>

                        <p class="mt-1 text-2xl font-black text-white">
                            {{ $tags->count() }}
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400">
                        <i class="fa-solid fa-list-check"></i>
                    </div>

                </div>
            </div>


            {{-- Active filters --}}
            <div
                class="rounded-2xl border border-white/5 bg-gray-900/70 px-5 py-4
                       shadow-xl shadow-black/5 backdrop-blur"
            >
                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            Active filters
                        </p>

                        <p class="mt-1 text-2xl font-black text-white">
                            {{ $activeFilterCount }}
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                        <i class="fa-solid fa-filter"></i>
                    </div>

                </div>
            </div>

        </div>

    </div>


    {{-- =========================================================
         MAIN CONTENT
    ========================================================== --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">


        {{-- =====================================================
             FILTER SIDEBAR
        ====================================================== --}}
        <aside class="h-fit xl:sticky xl:top-6">

            <details
                open
                class="group overflow-hidden rounded-2xl border border-white/5 bg-gray-900/80 shadow-2xl shadow-black/10"
            >

                {{-- FILTER HEADER --}}
                <summary
                    class="flex cursor-pointer list-none items-center justify-between
                           px-5 py-4 [&::-webkit-details-marker]:hidden"
                >

                    <div class="flex items-center gap-3">

                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/5 text-gray-300">
                            <i class="fa-solid fa-sliders"></i>
                        </div>

                        <div>
                            <h2 class="text-sm font-bold text-white">
                                Filters
                            </h2>

                            <p class="text-xs text-gray-500">
                                Refine your tag results
                            </p>
                        </div>

                    </div>


                    <div class="flex items-center gap-2">

                        @if($activeFilterCount > 0)
                            <span
                                class="inline-flex h-6 min-w-6 items-center justify-center
                                       rounded-full bg-indigo-500/15 px-2
                                       text-[11px] font-bold text-indigo-400"
                            >
                                {{ $activeFilterCount }}
                            </span>
                        @endif

                        <i
                            class="fa-solid fa-chevron-down text-xs text-gray-500
                                   transition-transform duration-200
                                   group-open:rotate-180"
                        ></i>

                    </div>

                </summary>


                <div class="border-t border-white/5 p-5">

                    <form
                        method="GET"
                        action="{{ route('tag.list') }}"
                        class="space-y-5"
                    >

                        {{-- SEARCH --}}
                        <div>

                            <label
                                for="name"
                                class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400"
                            >
                                Search
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-solid fa-magnifying-glass
                                           absolute left-3.5 top-1/2 -translate-y-1/2
                                           text-sm text-gray-500"
                                ></i>

                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    value="{{ request('name') }}"
                                    placeholder="Search tags..."
                                    class="w-full rounded-xl border border-white/10 bg-gray-950/60
                                           py-2.5 pl-10 pr-3 text-sm text-white
                                           placeholder:text-gray-600
                                           outline-none transition
                                           focus:border-indigo-500/50
                                           focus:ring-4 focus:ring-indigo-500/10"
                                />

                            </div>


                            <label class="mt-2.5 inline-flex cursor-pointer items-center gap-2 text-xs text-gray-500">

                                <input
                                    type="checkbox"
                                    name="name_ignore_case"
                                    value="1"
                                    @checked(request('name_ignore_case'))
                                    class="h-3.5 w-3.5 rounded border-gray-600 bg-gray-800
                                           text-indigo-600 focus:ring-indigo-500/20"
                                >

                                <span>
                                    Ignore case
                                </span>

                            </label>

                        </div>


                        {{-- TYPE --}}
                        <div>

                            <label
                                for="type"
                                class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400"
                            >
                                Type
                            </label>

                            <select
                                id="type"
                                name="type"
                                class="w-full rounded-xl border border-white/10 bg-gray-950/60
                                       px-3 py-2.5 text-sm text-gray-200
                                       outline-none transition
                                       focus:border-indigo-500/50
                                       focus:ring-4 focus:ring-indigo-500/10"
                            >
                                <option value="">All types</option>
                                <option value="GENERAL" @selected(request('type') === 'GENERAL')>
                                    General
                                </option>
                                <option value="SYSTEM" @selected(request('type') === 'SYSTEM')>
                                    System
                                </option>
                                <option value="CONTENT" @selected(request('type') === 'CONTENT')>
                                    Content
                                </option>
                                <option value="PRODUCT" @selected(request('type') === 'PRODUCT')>
                                    Product
                                </option>
                                <option value="STATUS" @selected(request('type') === 'STATUS')>
                                    Status
                                </option>
                            </select>

                        </div>


                        {{-- STATUS --}}
                        <div>

                            <label
                                for="is_active"
                                class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400"
                            >
                                Status
                            </label>

                            <select
                                id="is_active"
                                name="is_active"
                                class="w-full rounded-xl border border-white/10 bg-gray-950/60
                                       px-3 py-2.5 text-sm text-gray-200
                                       outline-none transition
                                       focus:border-indigo-500/50
                                       focus:ring-4 focus:ring-indigo-500/10"
                            >
                                <option value="">All statuses</option>

                                <option value="1" @selected(request('is_active') === '1')>
                                    Active
                                </option>

                                <option value="0" @selected(request('is_active') === '0')>
                                    Inactive
                                </option>
                            </select>

                        </div>


                        {{-- USAGE RANGE --}}
                        <div>

                            <label
                                class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400"
                            >
                                Usage count
                            </label>

                            <div class="grid grid-cols-2 gap-2">

                                <input
                                    type="number"
                                    name="usage_count_min"
                                    value="{{ request('usage_count_min') }}"
                                    placeholder="Min"
                                    min="0"
                                    class="w-full rounded-xl border border-white/10 bg-gray-950/60
                                           px-3 py-2.5 text-sm text-white
                                           placeholder:text-gray-600
                                           outline-none transition
                                           focus:border-indigo-500/50
                                           focus:ring-4 focus:ring-indigo-500/10"
                                />

                                <input
                                    type="number"
                                    name="usage_count_max"
                                    value="{{ request('usage_count_max') }}"
                                    placeholder="Max"
                                    min="0"
                                    class="w-full rounded-xl border border-white/10 bg-gray-950/60
                                           px-3 py-2.5 text-sm text-white
                                           placeholder:text-gray-600
                                           outline-none transition
                                           focus:border-indigo-500/50
                                           focus:ring-4 focus:ring-indigo-500/10"
                                />

                            </div>

                        </div>


                        {{-- ARCHIVED --}}
                        <div>

                            <label
                                for="trashed"
                                class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400"
                            >
                                Archived items
                            </label>

                            <select
                                id="trashed"
                                name="trashed"
                                class="w-full rounded-xl border border-white/10 bg-gray-950/60
                                       px-3 py-2.5 text-sm text-gray-200
                                       outline-none transition
                                       focus:border-indigo-500/50
                                       focus:ring-4 focus:ring-indigo-500/10"
                            >
                                <option value="">
                                    Without archived
                                </option>

                                <option value="with" @selected(request('trashed') === 'with')>
                                    Include archived
                                </option>

                                <option value="only" @selected(request('trashed') === 'only')>
                                    Archived only
                                </option>

                            </select>

                        </div>


                        {{-- SORT --}}
                        <div>

                            <label
                                class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400"
                            >
                                Sorting
                            </label>

                            <div class="grid grid-cols-[1fr_auto] gap-2">

                                <select
                                    name="sort_by"
                                    class="min-w-0 rounded-xl border border-white/10 bg-gray-950/60
                                           px-3 py-2.5 text-sm text-gray-200
                                           outline-none transition
                                           focus:border-indigo-500/50
                                           focus:ring-4 focus:ring-indigo-500/10"
                                >
                                    <option value="created_at" @selected($sortBy === 'created_at')>
                                        Created date
                                    </option>

                                    <option value="name" @selected($sortBy === 'name')>
                                        Name
                                    </option>

                                    <option value="usage_count" @selected($sortBy === 'usage_count')>
                                        Usage
                                    </option>

                                    <option value="type" @selected($sortBy === 'type')>
                                        Type
                                    </option>
                                </select>


                                <select
                                    name="sort_order"
                                    class="rounded-xl border border-white/10 bg-gray-950/60
                                           px-3 py-2.5 text-sm text-gray-200
                                           outline-none transition
                                           focus:border-indigo-500/50
                                           focus:ring-4 focus:ring-indigo-500/10"
                                >
                                    <option value="desc" @selected($sortOrder === 'desc')>
                                        ↓
                                    </option>

                                    <option value="asc" @selected($sortOrder === 'asc')>
                                        ↑
                                    </option>
                                </select>

                            </div>

                        </div>


                        {{-- ACTIONS --}}
                        <div class="grid grid-cols-2 gap-2 pt-1">

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl
                                       bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white
                                       shadow-lg shadow-indigo-600/20
                                       transition
                                       hover:bg-indigo-500
                                       active:scale-[0.98]"
                            >
                                <i class="fa-solid fa-filter"></i>
                                Apply
                            </button>


                            <a
                                href="{{ route('tag.list') }}"
                                class="inline-flex items-center justify-center gap-2 rounded-xl
                                       border border-white/10 bg-white/5
                                       px-4 py-2.5 text-sm font-semibold text-gray-300
                                       transition
                                       hover:bg-white/10 hover:text-white
                                       active:scale-[0.98]"
                            >
                                <i class="fa-solid fa-rotate-left"></i>
                                Clear
                            </a>

                        </div>

                    </form>

                </div>

            </details>

        </aside>


        {{-- =====================================================
             TAG RESULTS
        ====================================================== --}}
        <main class="min-w-0">


            {{-- RESULTS TOOLBAR --}}
            <div
                class="mb-4 flex flex-col gap-3 rounded-2xl border border-white/5
                       bg-gray-900/70 p-4 shadow-xl shadow-black/5
                       sm:flex-row sm:items-center sm:justify-between"
            >

                <div>

                    <div class="flex items-center gap-2">

                        <span class="text-sm font-bold text-white">
                            Tag collection
                        </span>

                        @if($tags->total() > 0)
                            <span
                                class="rounded-full bg-white/5 px-2 py-0.5
                                       text-[11px] font-semibold text-gray-400"
                            >
                                {{ $tags->total() }} total
                            </span>
                        @endif

                    </div>

                    <p class="mt-1 text-xs text-gray-500">
                        Manage your available tags and their usage.
                    </p>

                </div>


                @if($tags->total() > 0)
                    <div class="text-xs text-gray-500">
                        Showing
                        <span class="font-semibold text-gray-300">
                            {{ $tags->firstItem() }}–{{ $tags->lastItem() }}
                        </span>
                        of
                        <span class="font-semibold text-gray-300">
                            {{ $tags->total() }}
                        </span>
                    </div>
                @endif

            </div>


            {{-- =================================================
                 EMPTY STATE
            ================================================== --}}
            @if ($tags->isEmpty())

                <div
                    class="rounded-2xl border border-dashed border-white/10
                           bg-gray-900/50 px-6 py-16 text-center"
                >

                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center
                               rounded-2xl bg-indigo-500/10
                               text-2xl text-indigo-400"
                    >
                        <i class="fa-solid fa-tags"></i>
                    </div>

                    <h3 class="mt-5 text-lg font-bold text-white">
                        No tags found
                    </h3>

                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-gray-500">
                        No tags match your current filters.
                        Try changing your search criteria or create a new tag.
                    </p>

                    <div class="mt-6 flex justify-center gap-3">

                        <a
                            href="{{ route('tag.list') }}"
                            class="inline-flex items-center gap-2 rounded-xl
                                   border border-white/10 bg-white/5
                                   px-4 py-2.5 text-sm font-semibold text-gray-300
                                   transition hover:bg-white/10 hover:text-white"
                        >
                            <i class="fa-solid fa-rotate-left"></i>
                            Clear filters
                        </a>

                        @if(Route::has('tag.create'))
                            <a
                                href="{{ route('tag.create') }}"
                                class="inline-flex items-center gap-2 rounded-xl
                                       bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white
                                       transition hover:bg-indigo-500"
                            >
                                <i class="fa-solid fa-plus"></i>
                                Create tag
                            </a>
                        @endif

                    </div>

                </div>

            @else


                {{-- =================================================
                     TAG LIST
                ================================================== --}}
                <div class="space-y-3">

                    @foreach ($tags as $tag)

                        @php
                            $typeLabel = str($tag->type)->lower()->headline();
                        @endphp

                        <div
                            x-data="{ open: false, showDeleteModal: false }"
                            class="group relative overflow-visible rounded-2xl
                                   border border-white/5
                                   bg-gray-900/80
                                   p-5
                                   shadow-xl shadow-black/5
                                   transition duration-200
                                   hover:-translate-y-0.5
                                   hover:border-white/10
                                   hover:bg-gray-900"
                        >

                            <div class="flex items-start gap-4">


                                {{-- TAG COLOR --}}
                                <div class="pt-1">

                                    <div
                                        class="flex h-11 w-11 items-center justify-center
                                               rounded-xl border border-white/5 bg-gray-950/60"
                                    >

                                        @if($tag->color)

                                            <span
                                                class="h-5 w-5 rounded-full ring-4 ring-white/5"
                                                style="background-color: {{ $tag->color }};"
                                            ></span>

                                        @else

                                            <i class="fa-solid fa-tag text-sm text-gray-500"></i>

                                        @endif

                                    </div>

                                </div>


                                {{-- TAG CONTENT --}}
                                <div class="min-w-0 flex-1">

                                    <div class="flex flex-wrap items-center gap-2">

                                        <h3 class="truncate text-base font-bold text-white">
                                            {{ $tag->name }}
                                        </h3>


                                        {{-- TYPE --}}
                                        <span
                                            class="inline-flex items-center rounded-full
                                                   border border-indigo-500/20
                                                   bg-indigo-500/10
                                                   px-2.5 py-1
                                                   text-[10px] font-bold uppercase tracking-wider
                                                   text-indigo-300"
                                        >
                                            {{ $typeLabel }}
                                        </span>


                                        {{-- STATUS --}}
                                        @if($tag->is_active)

                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full
                                                       border border-emerald-500/20
                                                       bg-emerald-500/10
                                                       px-2.5 py-1
                                                       text-[10px] font-bold uppercase tracking-wider
                                                       text-emerald-300"
                                            >
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                                Active
                                            </span>

                                        @else

                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full
                                                       border border-red-500/20
                                                       bg-red-500/10
                                                       px-2.5 py-1
                                                       text-[10px] font-bold uppercase tracking-wider
                                                       text-red-300"
                                            >
                                                <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
                                                Inactive
                                            </span>

                                        @endif

                                    </div>


                                    {{-- DESCRIPTION --}}
                                    @if($tag->description)

                                        <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-400">
                                            {{ $tag->description }}
                                        </p>

                                    @endif


                                    {{-- META --}}
                                    <div
                                        class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2
                                               text-xs text-gray-500"
                                    >

                                        <span class="inline-flex items-center gap-1.5">
                                            <i class="fa-solid fa-link text-[10px] text-gray-600"></i>
                                            <span class="text-gray-400">
                                                {{ $tag->slug }}
                                            </span>
                                        </span>


                                        <span class="h-3 w-px bg-white/10"></span>


                                        <span class="inline-flex items-center gap-1.5">
                                            <i class="fa-solid fa-chart-simple text-[10px] text-gray-600"></i>

                                            <span>
                                                {{ number_format($tag->usage_count) }}
                                                {{ str($tag->usage_count)->value === '1' ? 'use' : 'uses' }}
                                            </span>
                                        </span>


                                        <span class="h-3 w-px bg-white/10"></span>


                                        <span class="inline-flex items-center gap-1.5">
                                            <i class="fa-regular fa-calendar text-[10px] text-gray-600"></i>

                                            <span>
                                                Created {{ $tag->created_at->format('d M Y') }}
                                            </span>
                                        </span>

                                    </div>

                                </div>


                                {{-- ACTION MENU --}}
                                <div class="relative flex-shrink-0">

                                    <button
                                        type="button"
                                        @click="open = !open"
                                        class="flex h-9 w-9 items-center justify-center
                                               rounded-xl border border-transparent
                                               text-gray-500
                                               transition
                                               hover:border-white/10
                                               hover:bg-white/5
                                               hover:text-gray-200"
                                        aria-label="Tag actions"
                                    >
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>


                                    {{-- DROPDOWN --}}
                                    <div
                                        x-show="open"
                                        x-cloak
                                        x-transition
                                        @click.outside="open = false"
                                        class="absolute right-0 z-30 mt-2 w-48
                                               overflow-hidden rounded-xl
                                               border border-white/10
                                               bg-gray-900
                                               p-1
                                               shadow-2xl shadow-black/30"
                                    >

                                        {{-- EDIT --}}
                                        @if(Route::has('tag.edit'))
                                            <a
                                                href="{{ route('tag.edit', $tag->id) }}"
                                                class="flex items-center gap-3 rounded-lg px-3 py-2.5
                                                       text-sm text-gray-300
                                                       transition
                                                       hover:bg-white/5 hover:text-white"
                                            >
                                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-400">
                                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                </span>

                                                Edit tag
                                            </a>
                                        @endif


                                        {{-- TOGGLE STATUS --}}
                                        @if(Route::has('tag.toggle-active'))
                                            <form
                                                action="{{ route('tag.toggle-active', $tag->id) }}"
                                                method="POST"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5
                                                           text-left text-sm text-gray-300
                                                           transition
                                                           hover:bg-white/5 hover:text-white"
                                                >

                                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                                                        <i class="fa-solid {{ $tag->is_active ? 'fa-toggle-off' : 'fa-toggle-on' }} text-xs"></i>
                                                    </span>

                                                    {{ $tag->is_active ? 'Deactivate' : 'Activate' }}

                                                </button>

                                            </form>
                                        @endif


                                        <div class="my-1 border-t border-white/5"></div>


                                        {{-- DELETE --}}
                                        @if(Route::has('tag.destroy'))
                                            <button
                                                type="button"
                                                @click="showDeleteModal = true; open = false"
                                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5
                                                       text-left text-sm text-red-400
                                                       transition
                                                       hover:bg-red-500/10 hover:text-red-300"
                                            >

                                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-500/10 text-red-400">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </span>

                                                Delete tag

                                            </button>
                                        @endif

                                    </div>

                                </div>

                            </div>


                            {{-- =================================================
                                 DELETE MODAL
                            ================================================== --}}
                            <div
                                x-show="showDeleteModal"
                                x-cloak
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="fixed inset-0 z-50 flex items-center justify-center
                                       bg-black/70 p-4 backdrop-blur-sm"
                            >

                                <div
                                    x-show="showDeleteModal"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="scale-95 opacity-0"
                                    x-transition:enter-end="scale-100 opacity-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="scale-100 opacity-100"
                                    x-transition:leave-end="scale-95 opacity-0"
                                    @click.outside="showDeleteModal = false"
                                    class="w-full max-w-md overflow-hidden rounded-2xl
                                           border border-white/10
                                           bg-gray-900
                                           shadow-2xl shadow-black/50"
                                >

                                    {{-- MODAL HEADER --}}
                                    <div class="border-b border-white/5 px-6 py-5">

                                        <div class="flex items-start gap-4">

                                            <div
                                                class="flex h-11 w-11 flex-shrink-0 items-center justify-center
                                                       rounded-xl bg-red-500/10
                                                       text-red-400 ring-1 ring-red-500/10"
                                            >
                                                <i class="fa-solid fa-triangle-exclamation"></i>
                                            </div>

                                            <div>
                                                <h3 class="text-lg font-bold text-white">
                                                    Delete tag
                                                </h3>

                                                <p class="mt-1 text-sm text-gray-500">
                                                    This action requires confirmation.
                                                </p>
                                            </div>

                                        </div>

                                    </div>


                                    {{-- MODAL BODY --}}
                                    <div class="space-y-4 px-6 py-5">

                                        <p class="text-sm leading-6 text-gray-400">
                                            You are about to delete
                                            <strong class="font-semibold text-white">
                                                "{{ $tag->name }}"
                                            </strong>.
                                        </p>


                                        <div
                                            class="rounded-xl border border-red-500/10
                                                   bg-red-500/5 p-4"
                                        >

                                            <div class="flex gap-3">

                                                <i class="fa-solid fa-circle-info mt-0.5 text-red-400"></i>

                                                <div class="text-sm leading-6 text-gray-400">

                                                    <p class="font-semibold text-gray-200">
                                                        What will happen?
                                                    </p>

                                                    <ul class="mt-2 space-y-1.5 text-gray-500">
                                                        <li class="flex gap-2">
                                                            <span class="text-red-400">•</span>
                                                            The tag will be removed from the active list.
                                                        </li>

                                                        <li class="flex gap-2">
                                                            <span class="text-red-400">•</span>
                                                            Existing references to this tag may be affected.
                                                        </li>

                                                        <li class="flex gap-2">
                                                            <span class="text-red-400">•</span>
                                                            You will need to restore it manually if your system supports restoration.
                                                        </li>
                                                    </ul>

                                                </div>

                                            </div>

                                        </div>


                                        {{-- TAG PREVIEW --}}
                                        <div
                                            class="flex items-center gap-3 rounded-xl
                                                   border border-white/5 bg-gray-950/50 p-3"
                                        >

                                            <div
                                                class="flex h-10 w-10 items-center justify-center
                                                       rounded-lg border border-white/5"
                                            >
                                                @if($tag->color)

                                                    <span
                                                        class="h-4 w-4 rounded-full"
                                                        style="background-color: {{ $tag->color }};"
                                                    ></span>

                                                @else

                                                    <i class="fa-solid fa-tag text-xs text-gray-500"></i>

                                                @endif
                                            </div>


                                            <div class="min-w-0">

                                                <p class="truncate text-sm font-semibold text-white">
                                                    {{ $tag->name }}
                                                </p>

                                                <p class="truncate text-xs text-gray-500">
                                                    {{ $tag->slug }}
                                                </p>

                                            </div>

                                        </div>

                                    </div>


                                    {{-- MODAL FOOTER --}}
                                    <div class="flex gap-3 border-t border-white/5 bg-gray-950/30 px-6 py-4">

                                        <button
                                            @click="showDeleteModal = false"
                                            type="button"
                                            class="w-full rounded-xl border border-white/10
                                                   bg-white/5 px-4 py-2.5
                                                   text-sm font-semibold text-gray-300
                                                   transition
                                                   hover:bg-white/10 hover:text-white"
                                        >
                                            Cancel
                                        </button>


                                        <form
                                            action="{{ route('tag.destroy', $tag->id) }}"
                                            method="POST"
                                            class="w-full"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="w-full rounded-xl bg-red-600 px-4 py-2.5
                                                       text-sm font-bold text-white
                                                       shadow-lg shadow-red-600/20
                                                       transition
                                                       hover:bg-red-500
                                                       active:scale-[0.98]"
                                            >
                                                <i class="fa-solid fa-trash-can mr-1.5"></i>
                                                Delete tag
                                            </button>

                                        </form>

                                    </div>

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>


                {{-- =================================================
                     PAGINATION
                ================================================== --}}
                @if($tags->hasPages())

                    <div
                        class="mt-6 overflow-hidden rounded-2xl border border-white/5
                               bg-gray-900/60 p-4"
                    >
                        {{ $tags->withQueryString()->links() }}
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