@extends('layouts.base')

@section('title', 'Update Category - Iris App')

@section('content')

<div
    x-data="{
        name: @js(old('name', $category->name)),
        slug: @js(old('slug', $category->slug)),
        icon: @js(old('icon', $category->icon ?? '')),
        parent: @js(old('parent_id', $category->parent_id)),
        isActive: @js(old('is_active', $category->is_active)),
    }"
    class="min-h-full"
>

    {{-- =========================================================
         PAGE HEADER
    ========================================================== --}}
    <div class="mb-8">

        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

            <div>

                <div class="mb-3 flex items-center gap-2">

                    <span
                        class="inline-flex h-9 w-9 items-center justify-center
                               rounded-xl bg-indigo-500/10
                               text-indigo-400
                               ring-1 ring-indigo-500/20"
                    >
                        <i class="fa-solid fa-folder-pen"></i>
                    </span>

                    <span
                        class="text-xs font-semibold uppercase
                               tracking-[0.18em] text-indigo-400"
                    >
                        Catalog
                    </span>

                </div>


                <div class="flex flex-wrap items-center gap-3">

                    <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                        Update Category
                    </h1>

                    @if($category->is_active)

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full
                                   border border-emerald-500/20
                                   bg-emerald-500/10
                                   px-2.5 py-1
                                   text-[10px] font-bold
                                   uppercase tracking-wider
                                   text-emerald-300"
                        >
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            Active
                        </span>

                    @else

                        <span
                            class="inline-flex items-center gap-1.5
                                   rounded-full
                                   border border-red-500/20
                                   bg-red-500/10
                                   px-2.5 py-1
                                   text-[10px] font-bold
                                   uppercase tracking-wider
                                   text-red-300"
                        >
                            <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
                            Inactive
                        </span>

                    @endif

                </div>


                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                    Update the category information, hierarchy, presentation
                    and visibility settings.
                </p>

            </div>


            {{-- BACK --}}
            <a
                href="{{ route('category.list') }}"
                class="inline-flex items-center justify-center gap-2
                       rounded-xl border border-white/10
                       bg-white/5 px-4 py-2.5
                       text-sm font-semibold text-gray-300
                       transition
                       hover:bg-white/10 hover:text-white
                       active:scale-[0.98]"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Back to Categories
            </a>

        </div>

    </div>


    {{-- =========================================================
         MAIN LAYOUT
    ========================================================== --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">


        {{-- =====================================================
             FORM
        ====================================================== --}}
        <div>

            <form
                action="{{ route('category.update', $category->id) }}"
                method="POST"
                class="overflow-hidden rounded-2xl
                       border border-white/5
                       bg-gray-900/80
                       shadow-2xl shadow-black/10"
            >

                @csrf
                @method('PUT')


                {{-- =================================================
                     BASIC INFORMATION
                ================================================== --}}
                <section>

                    <div
                        class="border-b border-white/5
                               px-6 py-5 sm:px-7"
                    >

                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-9 w-9
                                       items-center justify-center
                                       rounded-xl
                                       bg-indigo-500/10
                                       text-indigo-400"
                            >
                                <i class="fa-solid fa-circle-info"></i>
                            </div>

                            <div>

                                <h2 class="text-sm font-bold text-white">
                                    Basic information
                                </h2>

                                <p class="mt-0.5 text-xs text-gray-500">
                                    Update the identity of this category.
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="space-y-5 p-6 sm:p-7">

                        {{-- NAME --}}
                        <div>

                            <label
                                for="name"
                                class="mb-2 block text-xs
                                       font-semibold uppercase
                                       tracking-wider text-gray-400"
                            >
                                Category name
                                <span class="text-red-400">*</span>
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-solid fa-tag
                                           pointer-events-none
                                           absolute left-3.5 top-1/2
                                           -translate-y-1/2
                                           text-sm text-gray-500"
                                ></i>

                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    x-model="name"
                                    required
                                    autocomplete="off"
                                    placeholder="e.g. Hardware"
                                    class="w-full rounded-xl
                                           border border-white/10
                                           bg-gray-950/60
                                           py-3 pl-10 pr-4
                                           text-sm text-white
                                           placeholder:text-gray-600
                                           outline-none transition
                                           focus:border-indigo-500/50
                                           focus:ring-4 focus:ring-indigo-500/10"
                                />

                            </div>

                            @error('name')

                                <p
                                    class="mt-2 flex items-center gap-1.5
                                           text-xs text-red-400"
                                >
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- SLUG --}}
                        <div>

                            <label
                                for="slug"
                                class="mb-2 block text-xs
                                       font-semibold uppercase
                                       tracking-wider text-gray-400"
                            >
                                Slug
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-solid fa-link
                                           pointer-events-none
                                           absolute left-3.5 top-1/2
                                           -translate-y-1/2
                                           text-sm text-gray-500"
                                ></i>

                                <input
                                    type="text"
                                    name="slug"
                                    id="slug"
                                    x-model="slug"
                                    autocomplete="off"
                                    placeholder="hardware"
                                    class="w-full rounded-xl
                                           border border-white/10
                                           bg-gray-950/60
                                           py-3 pl-10 pr-4
                                           text-sm text-white
                                           placeholder:text-gray-600
                                           outline-none transition
                                           focus:border-indigo-500/50
                                           focus:ring-4 focus:ring-indigo-500/10"
                                />

                            </div>

                            <p class="mt-2 text-xs text-gray-600">
                                Used as the category's URL-friendly identifier.
                            </p>

                            @error('slug')

                                <p
                                    class="mt-2 flex items-center gap-1.5
                                           text-xs text-red-400"
                                >
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- DESCRIPTION --}}
                        <div>

                            <div class="mb-2 flex items-center justify-between">

                                <label
                                    for="description"
                                    class="block text-xs
                                           font-semibold uppercase
                                           tracking-wider text-gray-400"
                                >
                                    Description
                                </label>

                                <span class="text-[11px] text-gray-600">
                                    Optional
                                </span>

                            </div>

                            <textarea
                                name="description"
                                id="description"
                                rows="5"
                                placeholder="Describe what this category is used for..."
                                class="w-full resize-none rounded-xl
                                       border border-white/10
                                       bg-gray-950/60
                                       px-4 py-3
                                       text-sm leading-6 text-white
                                       placeholder:text-gray-600
                                       outline-none transition
                                       focus:border-indigo-500/50
                                       focus:ring-4 focus:ring-indigo-500/10"
                            >{{ old('description', $category->description) }}</textarea>

                            @error('description')

                                <p
                                    class="mt-2 flex items-center gap-1.5
                                           text-xs text-red-400"
                                >
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                    </div>

                </section>


                {{-- =================================================
                     CATEGORY STRUCTURE
                ================================================== --}}
                <section class="border-t border-white/5">

                    <div
                        class="border-b border-white/5
                               px-6 py-5 sm:px-7"
                    >

                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-9 w-9
                                       items-center justify-center
                                       rounded-xl
                                       bg-violet-500/10
                                       text-violet-400"
                            >
                                <i class="fa-solid fa-folder-tree"></i>
                            </div>

                            <div>

                                <h2 class="text-sm font-bold text-white">
                                    Category structure
                                </h2>

                                <p class="mt-0.5 text-xs text-gray-500">
                                    Manage hierarchy and ordering.
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2 sm:p-7">

                        {{-- PARENT --}}
                        <div>

                            <label
                                for="parent_id"
                                class="mb-2 block text-xs
                                       font-semibold uppercase
                                       tracking-wider text-gray-400"
                            >
                                Parent category
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-solid fa-folder-tree
                                           pointer-events-none
                                           absolute left-3.5 top-1/2
                                           -translate-y-1/2
                                           text-sm text-gray-500"
                                ></i>

                                <select
                                    name="parent_id"
                                    id="parent_id"
                                    x-model="parent"
                                    class="w-full appearance-none
                                           rounded-xl
                                           border border-white/10
                                           bg-gray-950/60
                                           py-3 pl-10 pr-10
                                           text-sm text-gray-200
                                           outline-none transition
                                           focus:border-indigo-500/50
                                           focus:ring-4 focus:ring-indigo-500/10"
                                >

                                    <option value="">
                                        None — Root category
                                    </option>

                                    @if(isset($categories))

                                        @foreach($categories as $parentCategory)

                                            @if($parentCategory->id !== $category->id)

                                                <option
                                                    value="{{ $parentCategory->id }}"
                                                    @selected(
                                                        old(
                                                            'parent_id',
                                                            $category->parent_id
                                                        ) == $parentCategory->id
                                                    )
                                                >
                                                    {{ $parentCategory->name }}
                                                </option>

                                            @endif

                                        @endforeach

                                    @endif

                                </select>

                                <i
                                    class="fa-solid fa-chevron-down
                                           pointer-events-none
                                           absolute right-3 top-1/2
                                           -translate-y-1/2
                                           text-[10px] text-gray-600"
                                ></i>

                            </div>

                            <p class="mt-2 text-xs text-gray-600">
                                Leave empty to make this a root category.
                            </p>

                            @error('parent_id')

                                <p
                                    class="mt-2 flex items-center gap-1.5
                                           text-xs text-red-400"
                                >
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- DISPLAY ORDER --}}
                        <div>

                            <label
                                for="display_order"
                                class="mb-2 block text-xs
                                       font-semibold uppercase
                                       tracking-wider text-gray-400"
                            >
                                Display order
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-solid fa-arrow-down-1-9
                                           pointer-events-none
                                           absolute left-3.5 top-1/2
                                           -translate-y-1/2
                                           text-sm text-gray-500"
                                ></i>

                                <input
                                    type="number"
                                    name="display_order"
                                    id="display_order"
                                    value="{{ old('display_order', $category->display_order) }}"
                                    min="0"
                                    class="w-full rounded-xl
                                           border border-white/10
                                           bg-gray-950/60
                                           py-3 pl-10 pr-4
                                           text-sm text-white
                                           outline-none transition
                                           focus:border-indigo-500/50
                                           focus:ring-4 focus:ring-indigo-500/10"
                                />

                            </div>

                            <p class="mt-2 text-xs text-gray-600">
                                Lower values appear first.
                            </p>

                            @error('display_order')

                                <p
                                    class="mt-2 flex items-center gap-1.5
                                           text-xs text-red-400"
                                >
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                    </div>

                </section>


                {{-- =================================================
                     PRESENTATION
                ================================================== --}}
                <section class="border-t border-white/5">

                    <div
                        class="border-b border-white/5
                               px-6 py-5 sm:px-7"
                    >

                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-9 w-9
                                       items-center justify-center
                                       rounded-xl
                                       bg-fuchsia-500/10
                                       text-fuchsia-400"
                            >
                                <i class="fa-solid fa-palette"></i>
                            </div>

                            <div>

                                <h2 class="text-sm font-bold text-white">
                                    Presentation
                                </h2>

                                <p class="mt-0.5 text-xs text-gray-500">
                                    Control how the category appears in the system.
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="space-y-5 p-6 sm:p-7">

                        {{-- ICON --}}
                        <div>

                            <div class="mb-2 flex items-center justify-between">

                                <label
                                    for="icon"
                                    class="block text-xs
                                           font-semibold uppercase
                                           tracking-wider text-gray-400"
                                >
                                    Icon class
                                </label>

                                <span class="text-[11px] text-gray-600">
                                    Font Awesome
                                </span>

                            </div>


                            <div class="grid grid-cols-[1fr_64px] gap-3">

                                <div class="relative">

                                    <i
                                        class="fa-solid fa-icons
                                               pointer-events-none
                                               absolute left-3.5 top-1/2
                                               -translate-y-1/2
                                               text-sm text-gray-500"
                                    ></i>

                                    <input
                                        type="text"
                                        name="icon"
                                        id="icon"
                                        x-model="icon"
                                        placeholder="fa-solid fa-folder"
                                        autocomplete="off"
                                        class="w-full rounded-xl
                                               border border-white/10
                                               bg-gray-950/60
                                               py-3 pl-10 pr-4
                                               text-sm text-white
                                               placeholder:text-gray-600
                                               outline-none transition
                                               focus:border-indigo-500/50
                                               focus:ring-4 focus:ring-indigo-500/10"
                                    />

                                </div>


                                {{-- ICON PREVIEW --}}
                                <div
                                    class="flex h-[46px]
                                           items-center justify-center
                                           rounded-xl
                                           border border-white/10
                                           bg-gray-950/60
                                           text-indigo-400"
                                >

                                    <i
                                        :class="icon || 'fa-solid fa-folder'"
                                        class="text-lg"
                                    ></i>

                                </div>

                            </div>

                            <p class="mt-2 text-xs text-gray-600">
                                Example:
                                <span class="font-mono text-gray-500">
                                    fa-solid fa-folder
                                </span>
                            </p>

                            @error('icon')

                                <p
                                    class="mt-2 flex items-center gap-1.5
                                           text-xs text-red-400"
                                >
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- ACTIVE STATUS --}}
                        <div
                            class="flex items-center justify-between
                                   rounded-xl
                                   border border-white/5
                                   bg-gray-950/40
                                   px-4 py-4"
                        >

                            <div class="flex items-center gap-3">

                                <div
                                    class="flex h-9 w-9
                                           items-center justify-center
                                           rounded-lg
                                           bg-emerald-500/10
                                           text-emerald-400"
                                >
                                    <i class="fa-solid fa-circle-check"></i>
                                </div>

                                <div>

                                    <p class="text-sm font-semibold text-white">
                                        Active category
                                    </p>

                                    <p class="mt-0.5 text-xs text-gray-500">
                                        Make this category available to the system.
                                    </p>

                                </div>

                            </div>


                            <label
                                class="relative inline-flex cursor-pointer items-center"
                            >

                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    x-model="isActive"
                                    class="peer sr-only"
                                    @checked(old('is_active', $category->is_active))
                                >

                                <div
                                    class="h-6 w-11 rounded-full
                                           bg-gray-700
                                           transition
                                           peer-focus:ring-4
                                           peer-focus:ring-indigo-500/20
                                           peer-checked:bg-indigo-600
                                           after:absolute after:left-[2px]
                                           after:top-[2px]
                                           after:h-5 after:w-5
                                           after:rounded-full
                                           after:border after:border-gray-600
                                           after:bg-white
                                           after:transition-all
                                           peer-checked:after:translate-x-full
                                           peer-checked:after:border-white"
                                ></div>

                            </label>

                        </div>

                    </div>

                </section>


                {{-- =================================================
                     FOOTER
                ================================================== --}}
                <div
                    class="flex flex-col-reverse gap-3
                           border-t border-white/5
                           bg-gray-950/30
                           px-6 py-5
                           sm:flex-row sm:items-center
                           sm:justify-end sm:px-7"
                >

                    <a
                        href="{{ route('category.list') }}"
                        class="inline-flex items-center justify-center
                               gap-2 rounded-xl
                               border border-white/10
                               bg-white/5 px-5 py-2.5
                               text-sm font-semibold text-gray-300
                               transition
                               hover:bg-white/10 hover:text-white
                               active:scale-[0.98]"
                    >
                        Cancel
                    </a>


                    <button
                        type="submit"
                        class="inline-flex items-center justify-center
                               gap-2 rounded-xl
                               bg-indigo-600 px-6 py-2.5
                               text-sm font-bold text-white
                               shadow-lg shadow-indigo-600/20
                               transition
                               hover:-translate-y-0.5
                               hover:bg-indigo-500
                               active:translate-y-0"
                    >
                        <i class="fa-solid fa-floppy-disk"></i>
                        Save Changes
                    </button>

                </div>

            </form>

        </div>


        {{-- =====================================================
             SIDEBAR
        ====================================================== --}}
        <aside class="space-y-4">


            {{-- LIVE PREVIEW --}}
            <div
                class="overflow-hidden rounded-2xl
                       border border-white/5
                       bg-gray-900/80
                       shadow-2xl shadow-black/10"
            >

                <div
                    class="border-b border-white/5
                           px-5 py-4"
                >

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-8 w-8
                                   items-center justify-center
                                   rounded-lg
                                   bg-indigo-500/10
                                   text-indigo-400"
                        >
                            <i class="fa-solid fa-eye text-xs"></i>
                        </div>

                        <div>

                            <h2 class="text-sm font-bold text-white">
                                Preview
                            </h2>

                            <p class="text-xs text-gray-500">
                                Updated appearance
                            </p>

                        </div>

                    </div>

                </div>


                <div class="p-5">

                    <div
                        class="rounded-xl
                               border border-white/5
                               bg-gray-950/50 p-4"
                    >

                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-11 w-11
                                       items-center justify-center
                                       rounded-xl
                                       bg-indigo-500/10
                                       text-indigo-400"
                            >

                                <i
                                    :class="icon || 'fa-solid fa-folder'"
                                ></i>

                            </div>


                            <div class="min-w-0">

                                <p
                                    class="truncate
                                           text-sm font-bold text-white"
                                    x-text="name || 'Category name'"
                                ></p>

                                <p
                                    class="mt-1 truncate
                                           text-xs text-gray-500"
                                    x-text="slug || 'category-slug'"
                                ></p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- CURRENT CATEGORY --}}
            <div
                class="rounded-2xl
                       border border-white/5
                       bg-gray-900/80
                       p-5
                       shadow-2xl shadow-black/10"
            >

                <div class="flex items-start gap-3">

                    <div
                        class="flex h-9 w-9 flex-shrink-0
                               items-center justify-center
                               rounded-xl
                               bg-violet-500/10
                               text-violet-400"
                    >
                        <i class="fa-solid fa-database"></i>
                    </div>

                    <div class="min-w-0">

                        <h3 class="text-sm font-bold text-white">
                            Current category
                        </h3>

                        <p
                            class="mt-2 truncate
                                   text-sm font-semibold
                                   text-gray-300"
                        >
                            {{ $category->name }}
                        </p>

                        <p
                            class="mt-1 truncate
                                   font-mono text-[11px]
                                   text-gray-600"
                        >
                            ID: {{ $category->id }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- HIERARCHY --}}
            <div
                class="rounded-2xl
                       border border-white/5
                       bg-gray-900/80
                       p-5
                       shadow-2xl shadow-black/10"
            >

                <div class="flex items-start gap-3">

                    <div
                        class="flex h-9 w-9 flex-shrink-0
                               items-center justify-center
                               rounded-xl
                               bg-amber-500/10
                               text-amber-400"
                    >
                        <i class="fa-solid fa-folder-tree"></i>
                    </div>

                    <div>

                        <h3 class="text-sm font-bold text-white">
                            Hierarchy
                        </h3>

                        <p class="mt-2 text-xs leading-5 text-gray-500">
                            Changing the parent category may change
                            where this category appears in the catalog.
                        </p>

                    </div>

                </div>

            </div>


            {{-- STATUS --}}
            <div
                class="rounded-2xl
                       border border-white/5
                       bg-gray-900/80
                       p-5
                       shadow-2xl shadow-black/10"
            >

                <div class="flex items-start gap-3">

                    <div
                        class="flex h-9 w-9 flex-shrink-0
                               items-center justify-center
                               rounded-xl bg-emerald-500/10
                               text-emerald-400"
                    >
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                    <div>

                        <h3 class="text-sm font-bold text-white">
                            Visibility
                        </h3>

                        <p class="mt-2 text-xs leading-5 text-gray-500">
                            Inactive categories remain stored but
                            are no longer available for normal use.
                        </p>

                    </div>

                </div>

            </div>

        </aside>

    </div>

</div>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>

@endsection