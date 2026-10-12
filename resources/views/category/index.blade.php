@extends('layouts.base')

@section('title', 'Categories - Iris App')

@section('content')

@php
    $sortBy = request('sort_by', 'created_at');
    $sortOrder = request('sort_order', 'desc');

    $activeFilterCount = collect([
        request('name'),
        request('parent_id'),
        request('is_active'),
    ])->filter(fn ($value) => filled($value))->count();
@endphp

<div class="min-h-full">

    {{-- =========================================================
         PAGE HEADER
    ========================================================== --}}
    <div class="mb-8">

        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

            <div>

                <div class="mb-3 flex items-center gap-2">

                    <span
                        class="inline-flex h-9 w-9 items-center justify-center
                               rounded-xl bg-indigo-500/10 text-indigo-400
                               ring-1 ring-indigo-500/20"
                    >
                        <i class="fa-solid fa-folder-tree"></i>
                    </span>

                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-400">
                        Catalog
                    </span>

                </div>

                <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                    Categories
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                    Organize your support catalog through categories and hierarchical
                    relationships.
                </p>

            </div>


            {{-- CREATE --}}
            @if(Route::has('category.create'))

                <a
                    href="{{ route('category.create') }}"
                    class="inline-flex items-center justify-center gap-2
                           rounded-xl bg-indigo-600 px-5 py-3
                           text-sm font-bold text-white
                           shadow-lg shadow-indigo-600/20
                           transition duration-200
                           hover:-translate-y-0.5 hover:bg-indigo-500
                           active:translate-y-0"
                >
                    <i class="fa-solid fa-plus"></i>
                    New Category
                </a>

            @endif

        </div>


        {{-- =====================================================
             SUMMARY
        ====================================================== --}}
        <div class="mt-7 grid grid-cols-1 gap-3 sm:grid-cols-3">

            {{-- TOTAL --}}
            <div
                class="rounded-2xl border border-white/5
                       bg-gray-900/70 px-5 py-4
                       shadow-xl shadow-black/5 backdrop-blur"
            >

                <div class="flex items-center justify-between">

                    <div>

                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            Categories
                        </p>

                        <p class="mt-1 text-2xl font-black text-white">
                            {{ $categories->total() }}
                        </p>

                    </div>

                    <div
                        class="flex h-10 w-10 items-center justify-center
                               rounded-xl bg-indigo-500/10 text-indigo-400"
                    >
                        <i class="fa-solid fa-folder"></i>
                    </div>

                </div>

            </div>


            {{-- CURRENT PAGE --}}
            <div
                class="rounded-2xl border border-white/5
                       bg-gray-900/70 px-5 py-4
                       shadow-xl shadow-black/5 backdrop-blur"
            >

                <div class="flex items-center justify-between">

                    <div>

                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            On this page
                        </p>

                        <p class="mt-1 text-2xl font-black text-white">
                            {{ $categories->count() }}
                        </p>

                    </div>

                    <div
                        class="flex h-10 w-10 items-center justify-center
                               rounded-xl bg-emerald-500/10 text-emerald-400"
                    >
                        <i class="fa-solid fa-list"></i>
                    </div>

                </div>

            </div>


            {{-- FILTERS --}}
            <div
                class="rounded-2xl border border-white/5
                       bg-gray-900/70 px-5 py-4
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

                    <div
                        class="flex h-10 w-10 items-center justify-center
                               rounded-xl bg-amber-500/10 text-amber-400"
                    >
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
             FILTERS
        ====================================================== --}}
        <aside class="h-fit xl:sticky xl:top-6">

            <details
                open
                class="group overflow-hidden rounded-2xl
                       border border-white/5
                       bg-gray-900/80
                       shadow-2xl shadow-black/10"
            >

                {{-- FILTER HEADER --}}
                <summary
                    class="flex cursor-pointer list-none items-center
                           justify-between px-5 py-4
                           [&::-webkit-details-marker]:hidden"
                >

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-9 w-9 items-center justify-center
                                   rounded-xl bg-white/5 text-gray-300"
                        >
                            <i class="fa-solid fa-sliders"></i>
                        </div>

                        <div>

                            <h2 class="text-sm font-bold text-white">
                                Filters
                            </h2>

                            <p class="text-xs text-gray-500">
                                Refine your categories
                            </p>

                        </div>

                    </div>


                    <div class="flex items-center gap-2">

                        @if($activeFilterCount > 0)

                            <span
                                class="inline-flex h-6 min-w-6 items-center
                                       justify-center rounded-full
                                       bg-indigo-500/15 px-2
                                       text-[11px] font-bold text-indigo-400"
                            >
                                {{ $activeFilterCount }}
                            </span>

                        @endif

                        <i
                            class="fa-solid fa-chevron-down
                                   text-xs text-gray-500
                                   transition-transform duration-200
                                   group-open:rotate-180"
                        ></i>

                    </div>

                </summary>


                <div class="border-t border-white/5 p-5">

                    <form
                        method="GET"
                        action="{{ route('category.list') }}"
                        class="space-y-5"
                    >

                        {{-- SEARCH --}}
                        <div>

                            <label
                                for="name"
                                class="mb-2 block text-xs font-semibold
                                       uppercase tracking-wider text-gray-400"
                            >
                                Search
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-solid fa-magnifying-glass
                                           absolute left-3.5 top-1/2
                                           -translate-y-1/2
                                           text-sm text-gray-500"
                                ></i>

                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    value="{{ request('name') }}"
                                    placeholder="Search categories..."
                                    class="w-full rounded-xl
                                           border border-white/10
                                           bg-gray-950/60
                                           py-2.5 pl-10 pr-3
                                           text-sm text-white
                                           placeholder:text-gray-600
                                           outline-none transition
                                           focus:border-indigo-500/50
                                           focus:ring-4 focus:ring-indigo-500/10"
                                />

                            </div>


                            <label
                                class="mt-2.5 inline-flex cursor-pointer
                                       items-center gap-2
                                       text-xs text-gray-500"
                            >

                                <input
                                    type="checkbox"
                                    name="name_ignore_case"
                                    value="1"
                                    @checked(request('name_ignore_case'))
                                    class="h-3.5 w-3.5 rounded
                                           border-gray-600 bg-gray-800
                                           text-indigo-600
                                           focus:ring-indigo-500/20"
                                >

                                <span>
                                    Ignore case
                                </span>

                            </label>

                        </div>


                        {{-- PARENT CATEGORY --}}
                        <div>

                            <label
                                for="parent_id"
                                class="mb-2 block text-xs font-semibold
                                       uppercase tracking-wider text-gray-400"
                            >
                                Parent category
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-solid fa-folder-tree
                                           pointer-events-none
                                           absolute left-3.5 top-1/2
                                           -translate-y-1/2
                                           text-xs text-gray-500"
                                ></i>

                                <select
                                    id="parent_id"
                                    name="parent_id"
                                    class="w-full appearance-none
                                           rounded-xl border border-white/10
                                           bg-gray-950/60
                                           py-2.5 pl-10 pr-9
                                           text-sm text-gray-200
                                           outline-none transition
                                           focus:border-indigo-500/50
                                           focus:ring-4 focus:ring-indigo-500/10"
                                >

                                    <option value="">
                                        All categories
                                    </option>

                                    @foreach ($parentCategories as $parent)

                                        <option
                                            value="{{ $parent->id }}"
                                            @selected(request('parent_id') == $parent->id)
                                        >
                                            {{ $parent->name }}
                                        </option>

                                    @endforeach

                                </select>

                                <i
                                    class="fa-solid fa-chevron-down
                                           pointer-events-none
                                           absolute right-3 top-1/2
                                           -translate-y-1/2
                                           text-[10px] text-gray-600"
                                ></i>

                            </div>

                        </div>


                        {{-- STATUS --}}
                        <div>

                            <label
                                for="is_active"
                                class="mb-2 block text-xs font-semibold
                                       uppercase tracking-wider text-gray-400"
                            >
                                Status
                            </label>

                            <select
                                id="is_active"
                                name="is_active"
                                class="w-full rounded-xl
                                       border border-white/10
                                       bg-gray-950/60
                                       px-3 py-2.5
                                       text-sm text-gray-200
                                       outline-none transition
                                       focus:border-indigo-500/50
                                       focus:ring-4 focus:ring-indigo-500/10"
                            >

                                <option value="">
                                    All statuses
                                </option>

                                <option
                                    value="1"
                                    @selected(request('is_active') === '1')
                                >
                                    Active
                                </option>

                                <option
                                    value="0"
                                    @selected(request('is_active') === '0')
                                >
                                    Inactive
                                </option>

                            </select>

                        </div>


                        {{-- SORT --}}
                        <div>

                            <label
                                class="mb-2 block text-xs font-semibold
                                       uppercase tracking-wider text-gray-400"
                            >
                                Sorting
                            </label>

                            <div class="grid grid-cols-[1fr_auto] gap-2">

                                <select
                                    id="sort_by"
                                    name="sort_by"
                                    class="min-w-0 rounded-xl
                                           border border-white/10
                                           bg-gray-950/60
                                           px-3 py-2.5
                                           text-sm text-gray-200
                                           outline-none transition
                                           focus:border-indigo-500/50
                                           focus:ring-4 focus:ring-indigo-500/10"
                                >

                                    <option
                                        value="created_at"
                                        @selected($sortBy === 'created_at')
                                    >
                                        Created date
                                    </option>

                                    <option
                                        value="name"
                                        @selected($sortBy === 'name')
                                    >
                                        Name
                                    </option>

                                    <option
                                        value="display_order"
                                        @selected($sortBy === 'display_order')
                                    >
                                        Display order
                                    </option>

                                </select>


                                <select
                                    name="sort_order"
                                    class="rounded-xl
                                           border border-white/10
                                           bg-gray-950/60
                                           px-3 py-2.5
                                           text-sm text-gray-200
                                           outline-none transition
                                           focus:border-indigo-500/50
                                           focus:ring-4 focus:ring-indigo-500/10"
                                >

                                    <option
                                        value="desc"
                                        @selected($sortOrder === 'desc')
                                    >
                                        ↓
                                    </option>

                                    <option
                                        value="asc"
                                        @selected($sortOrder === 'asc')
                                    >
                                        ↑
                                    </option>

                                </select>

                            </div>

                        </div>


                        {{-- ACTIONS --}}
                        <div class="grid grid-cols-2 gap-2 pt-1">

                            <button
                                type="submit"
                                class="inline-flex items-center
                                       justify-center gap-2
                                       rounded-xl bg-indigo-600
                                       px-4 py-2.5
                                       text-sm font-bold text-white
                                       shadow-lg shadow-indigo-600/20
                                       transition hover:bg-indigo-500
                                       active:scale-[0.98]"
                            >
                                <i class="fa-solid fa-filter"></i>
                                Apply
                            </button>


                            <a
                                href="{{ route('category.list') }}"
                                class="inline-flex items-center
                                       justify-center gap-2
                                       rounded-xl border border-white/10
                                       bg-white/5 px-4 py-2.5
                                       text-sm font-semibold text-gray-300
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
             CATEGORY RESULTS
        ====================================================== --}}
        <main class="min-w-0">


            {{-- RESULTS TOOLBAR --}}
            <div
                class="mb-4 flex flex-col gap-3
                       rounded-2xl border border-white/5
                       bg-gray-900/70 p-4
                       shadow-xl shadow-black/5
                       sm:flex-row sm:items-center
                       sm:justify-between"
            >

                <div>

                    <div class="flex items-center gap-2">

                        <span class="text-sm font-bold text-white">
                            Category hierarchy
                        </span>

                        @if($categories->total() > 0)

                            <span
                                class="rounded-full bg-white/5
                                       px-2 py-0.5
                                       text-[11px] font-semibold
                                       text-gray-400"
                            >
                                {{ $categories->total() }} total
                            </span>

                        @endif

                    </div>

                    <p class="mt-1 text-xs text-gray-500">
                        Manage categories, hierarchy and visibility.
                    </p>

                </div>


                @if($categories->total() > 0)

                    <div class="text-xs text-gray-500">

                        Showing

                        <span class="font-semibold text-gray-300">
                            {{ $categories->firstItem() }}–{{ $categories->lastItem() }}
                        </span>

                        of

                        <span class="font-semibold text-gray-300">
                            {{ $categories->total() }}
                        </span>

                    </div>

                @endif

            </div>


            {{-- =================================================
                 EMPTY STATE
            ================================================== --}}
            @if ($categories->isEmpty())

                <div
                    class="rounded-2xl border border-dashed
                           border-white/10
                           bg-gray-900/50
                           px-6 py-16 text-center"
                >

                    <div
                        class="mx-auto flex h-16 w-16
                               items-center justify-center
                               rounded-2xl bg-indigo-500/10
                               text-2xl text-indigo-400"
                    >
                        <i class="fa-solid fa-folder-open"></i>
                    </div>

                    <h3 class="mt-5 text-lg font-bold text-white">
                        No categories found
                    </h3>

                    <p class="mx-auto mt-2 max-w-md
                              text-sm leading-6 text-gray-500"
                    >
                        No categories match your current filters.
                        Try changing your search criteria or create a new category.
                    </p>

                    <div class="mt-6 flex justify-center gap-3">

                        <a
                            href="{{ route('category.list') }}"
                            class="inline-flex items-center gap-2
                                   rounded-xl border border-white/10
                                   bg-white/5 px-4 py-2.5
                                   text-sm font-semibold text-gray-300
                                   transition hover:bg-white/10 hover:text-white"
                        >
                            <i class="fa-solid fa-rotate-left"></i>
                            Clear filters
                        </a>


                        @if(Route::has('category.create'))

                            <a
                                href="{{ route('category.create') }}"
                                class="inline-flex items-center gap-2
                                       rounded-xl bg-indigo-600
                                       px-4 py-2.5
                                       text-sm font-bold text-white
                                       transition hover:bg-indigo-500"
                            >
                                <i class="fa-solid fa-plus"></i>
                                Create category
                            </a>

                        @endif

                    </div>

                </div>

            @else


                {{-- =================================================
                     CATEGORY LIST
                ================================================== --}}
                <div class="space-y-3">

                    @foreach ($categories as $category)

                        @php
                            /** @var \App\Models\CategoryModel $category */

                            $isRoot = !$category->parent;

                            $statusClasses = $category->is_active
                                ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300'
                                : 'border-red-500/20 bg-red-500/10 text-red-300';
                        @endphp

                        <div
                            x-data="{ open: false, showDeleteModal: false }"
                            class="group relative overflow-visible
                                   rounded-2xl border border-white/5
                                   bg-gray-900/80 p-5
                                   shadow-xl shadow-black/5
                                   transition duration-200
                                   hover:-translate-y-0.5
                                   hover:border-white/10
                                   hover:bg-gray-900"
                        >

                            <div class="flex items-start gap-4">


                                {{-- CATEGORY ICON --}}
                                <div class="pt-1">

                                    <div
                                        class="flex h-11 w-11
                                               items-center justify-center
                                               rounded-xl
                                               border border-white/5
                                               bg-gray-950/60
                                               text-indigo-400"
                                    >

                                        <i
                                            class="fa-solid
                                                   {{ $isRoot
                                                        ? 'fa-folder'
                                                        : 'fa-folder-tree' }}"
                                        ></i>

                                    </div>

                                </div>


                                {{-- CONTENT --}}
                                <div class="min-w-0 flex-1">

                                    {{-- TITLE + STATUS --}}
                                    <div class="flex flex-wrap items-center gap-2">

                                        <h3
                                            class="truncate text-base
                                                   font-bold text-white"
                                        >
                                            {{ $category->name }}
                                        </h3>


                                        {{-- ROOT / CHILD --}}
                                        @if($isRoot)

                                            <span
                                                class="inline-flex items-center
                                                       gap-1.5 rounded-full
                                                       border border-indigo-500/20
                                                       bg-indigo-500/10
                                                       px-2.5 py-1
                                                       text-[10px]
                                                       font-bold uppercase
                                                       tracking-wider
                                                       text-indigo-300"
                                            >
                                                <i class="fa-solid fa-folder text-[9px]"></i>
                                                Root
                                            </span>

                                        @else

                                            <span
                                                class="inline-flex items-center
                                                       gap-1.5 rounded-full
                                                       border border-violet-500/20
                                                       bg-violet-500/10
                                                       px-2.5 py-1
                                                       text-[10px]
                                                       font-bold uppercase
                                                       tracking-wider
                                                       text-violet-300"
                                            >
                                                <i class="fa-solid fa-folder-tree text-[9px]"></i>
                                                Child
                                            </span>

                                        @endif


                                        {{-- STATUS --}}
                                        <span
                                            class="inline-flex items-center
                                                   gap-1.5 rounded-full
                                                   border px-2.5 py-1
                                                   text-[10px]
                                                   font-bold uppercase
                                                   tracking-wider
                                                   {{ $statusClasses }}"
                                        >

                                            <span
                                                class="h-1.5 w-1.5 rounded-full
                                                    {{ $category->is_active
                                                        ? 'bg-emerald-400'
                                                        : 'bg-red-400' }}"
                                            ></span>

                                            {{ $category->is_active ? 'Active' : 'Inactive' }}

                                        </span>

                                    </div>


                                    {{-- PARENT --}}
                                    <div
                                        class="mt-2 flex flex-wrap
                                               items-center gap-x-4 gap-y-2
                                               text-xs text-gray-500"
                                    >

                                        <span class="inline-flex items-center gap-1.5">

                                            <i
                                                class="fa-solid fa-folder-tree
                                                       text-[10px] text-gray-600"
                                            ></i>

                                            <span>
                                                Parent:
                                            </span>

                                            <span class="font-medium text-gray-300">
                                                {{ $category->parent?->name ?? 'None' }}
                                            </span>

                                        </span>


                                        <span class="h-3 w-px bg-white/10"></span>


                                        <span class="inline-flex items-center gap-1.5">

                                            <i
                                                class="fa-solid fa-sort
                                                       text-[10px] text-gray-600"
                                            ></i>

                                            <span>
                                                Order:
                                            </span>

                                            <span class="font-medium text-gray-300">
                                                {{ $category->display_order }}
                                            </span>

                                        </span>

                                    </div>


                                    {{-- DESCRIPTION --}}
                                    @if($category->description)

                                        <p
                                            class="mt-3 max-w-3xl
                                                   text-sm leading-6
                                                   text-gray-400"
                                        >
                                            {{ $category->description }}
                                        </p>

                                    @endif


                                    {{-- META --}}
                                    <div
                                        class="mt-3 flex flex-wrap
                                               items-center
                                               gap-x-4 gap-y-2
                                               text-xs text-gray-500"
                                    >

                                        <span
                                            class="inline-flex items-center gap-1.5"
                                        >
                                            <i
                                                class="fa-solid fa-link
                                                       text-[10px] text-gray-600"
                                            ></i>

                                            <span class="text-gray-400">
                                                {{ $category->slug }}
                                            </span>
                                        </span>


                                        <span class="h-3 w-px bg-white/10"></span>


                                        <span
                                            class="inline-flex items-center gap-1.5"
                                        >
                                            <i
                                                class="fa-regular fa-calendar
                                                       text-[10px] text-gray-600"
                                            ></i>

                                            <span>
                                                Created
                                                {{ $category->created_at->format('d M Y') }}
                                            </span>
                                        </span>

                                    </div>

                                </div>


                                {{-- ACTIONS --}}
                                <div class="relative flex-shrink-0">

                                    <button
                                        type="button"
                                        @click="open = !open"
                                        class="flex h-9 w-9
                                               items-center justify-center
                                               rounded-xl
                                               border border-transparent
                                               text-gray-500
                                               transition
                                               hover:border-white/10
                                               hover:bg-white/5
                                               hover:text-gray-200"
                                        aria-label="Category actions"
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
                                               bg-gray-900 p-1
                                               shadow-2xl shadow-black/30"
                                    >

                                        {{-- EDIT --}}
                                        @if(Route::has('category.edit'))

                                            <a
                                                href="{{ route('category.edit', $category->id) }}"
                                                class="flex items-center gap-3
                                                       rounded-lg px-3 py-2.5
                                                       text-sm text-gray-300
                                                       transition
                                                       hover:bg-white/5
                                                       hover:text-white"
                                            >

                                                <span
                                                    class="flex h-7 w-7
                                                           items-center justify-center
                                                           rounded-lg
                                                           bg-indigo-500/10
                                                           text-indigo-400"
                                                >
                                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                </span>

                                                Edit category

                                            </a>

                                        @endif


                                        {{-- TOGGLE --}}
                                        @if(Route::has('category.toggle-active'))

                                            <form
                                                action="{{ route('category.toggle-active', $category->id) }}"
                                                method="POST"
                                            >

                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    class="flex w-full items-center
                                                           gap-3 rounded-lg
                                                           px-3 py-2.5
                                                           text-left text-sm
                                                           text-gray-300
                                                           transition
                                                           hover:bg-white/5
                                                           hover:text-white"
                                                >

                                                    <span
                                                        class="flex h-7 w-7
                                                               items-center
                                                               justify-center
                                                               rounded-lg
                                                               bg-emerald-500/10
                                                               text-emerald-400"
                                                    >
                                                        <i
                                                            class="fa-solid
                                                                {{ $category->is_active
                                                                    ? 'fa-toggle-off'
                                                                    : 'fa-toggle-on' }}
                                                                text-xs"
                                                        ></i>
                                                    </span>

                                                    {{ $category->is_active
                                                        ? 'Deactivate'
                                                        : 'Activate' }}

                                                </button>

                                            </form>

                                        @endif


                                        <div class="my-1 border-t border-white/5"></div>


                                        {{-- DELETE --}}
                                        @if(Route::has('category.destroy'))

                                            <button
                                                type="button"
                                                @click="showDeleteModal = true; open = false"
                                                class="flex w-full items-center
                                                       gap-3 rounded-lg
                                                       px-3 py-2.5
                                                       text-left text-sm
                                                       text-red-400
                                                       transition
                                                       hover:bg-red-500/10
                                                       hover:text-red-300"
                                            >

                                                <span
                                                    class="flex h-7 w-7
                                                           items-center
                                                           justify-center
                                                           rounded-lg
                                                           bg-red-500/10
                                                           text-red-400"
                                                >
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </span>

                                                Delete category

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
                                class="fixed inset-0 z-50 flex
                                       items-center justify-center
                                       bg-black/70 p-4
                                       backdrop-blur-sm"
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
                                    class="w-full max-w-md
                                           overflow-hidden rounded-2xl
                                           border border-white/10
                                           bg-gray-900
                                           shadow-2xl
                                           shadow-black/50"
                                >

                                    {{-- HEADER --}}
                                    <div
                                        class="border-b border-white/5
                                               px-6 py-5"
                                    >

                                        <div class="flex items-start gap-4">

                                            <div
                                                class="flex h-11 w-11
                                                       flex-shrink-0
                                                       items-center
                                                       justify-center
                                                       rounded-xl
                                                       bg-red-500/10
                                                       text-red-400
                                                       ring-1
                                                       ring-red-500/10"
                                            >
                                                <i class="fa-solid fa-triangle-exclamation"></i>
                                            </div>

                                            <div>

                                                <h3 class="text-lg font-bold text-white">
                                                    Delete category
                                                </h3>

                                                <p class="mt-1 text-sm text-gray-500">
                                                    This action requires confirmation.
                                                </p>

                                            </div>

                                        </div>

                                    </div>


                                    {{-- BODY --}}
                                    <div class="space-y-4 px-6 py-5">

                                        <p class="text-sm leading-6 text-gray-400">

                                            You are about to delete

                                            <strong class="font-semibold text-white">
                                                "{{ $category->name }}"
                                            </strong>.

                                        </p>


                                        {{-- WARNING --}}
                                        <div
                                            class="rounded-xl
                                                   border border-red-500/10
                                                   bg-red-500/5 p-4"
                                        >

                                            <div class="flex gap-3">

                                                <i
                                                    class="fa-solid fa-circle-info
                                                           mt-0.5 text-red-400"
                                                ></i>

                                                <div
                                                    class="text-sm leading-6
                                                           text-gray-400"
                                                >

                                                    <p
                                                        class="font-semibold
                                                               text-gray-200"
                                                    >
                                                        Before you continue
                                                    </p>

                                                    <ul class="mt-2 space-y-1.5 text-gray-500">

                                                        <li class="flex gap-2">
                                                            <span class="text-red-400">•</span>
                                                            The category will be removed from the active catalog.
                                                        </li>

                                                        <li class="flex gap-2">
                                                            <span class="text-red-400">•</span>
                                                            Its current category relationships may be affected.
                                                        </li>

                                                    </ul>

                                                </div>

                                            </div>

                                        </div>


                                        {{-- CATEGORY PREVIEW --}}
                                        <div
                                            class="flex items-center gap-3
                                                   rounded-xl
                                                   border border-white/5
                                                   bg-gray-950/50 p-3"
                                        >

                                            <div
                                                class="flex h-10 w-10
                                                       items-center
                                                       justify-center
                                                       rounded-lg
                                                       border border-white/5
                                                       bg-indigo-500/10
                                                       text-indigo-400"
                                            >
                                                <i
                                                    class="fa-solid
                                                        {{ $isRoot
                                                            ? 'fa-folder'
                                                            : 'fa-folder-tree' }}"
                                                ></i>
                                            </div>


                                            <div class="min-w-0">

                                                <p
                                                    class="truncate
                                                           text-sm
                                                           font-semibold
                                                           text-white"
                                                >
                                                    {{ $category->name }}
                                                </p>

                                                <p
                                                    class="truncate
                                                           text-xs
                                                           text-gray-500"
                                                >
                                                    {{ $category->slug }}
                                                </p>

                                            </div>

                                        </div>

                                    </div>


                                    {{-- FOOTER --}}
                                    <div
                                        class="flex gap-3
                                               border-t border-white/5
                                               bg-gray-950/30
                                               px-6 py-4"
                                    >

                                        <button
                                            @click="showDeleteModal = false"
                                            type="button"
                                            class="w-full rounded-xl
                                                   border border-white/10
                                                   bg-white/5
                                                   px-4 py-2.5
                                                   text-sm font-semibold
                                                   text-gray-300
                                                   transition
                                                   hover:bg-white/10
                                                   hover:text-white"
                                        >
                                            Cancel
                                        </button>


                                        <form
                                            action="{{ route('category.destroy', $category->id) }}"
                                            method="POST"
                                            class="w-full"
                                        >

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="w-full rounded-xl
                                                       bg-red-600
                                                       px-4 py-2.5
                                                       text-sm font-bold
                                                       text-white
                                                       shadow-lg
                                                       shadow-red-600/20
                                                       transition
                                                       hover:bg-red-500
                                                       active:scale-[0.98]"
                                            >
                                                <i class="fa-solid fa-trash-can mr-1.5"></i>
                                                Delete category
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
                @if($categories->hasPages())

                    <div
                        class="mt-6 overflow-hidden
                               rounded-2xl border border-white/5
                               bg-gray-900/60 p-4"
                    >
                        {{ $categories->withQueryString()->links() }}
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