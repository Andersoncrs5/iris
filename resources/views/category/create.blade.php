@extends('layouts.base')

@section('title', 'Create Category - Iris App')

@section('content')

<div
    x-data="{
        icon: @js(old('icon', '')),
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
                        <i class="fa-solid fa-folder-plus"></i>
                    </span>

                    <span
                        class="text-xs font-semibold uppercase
                               tracking-[0.18em] text-indigo-400"
                    >
                        Catalog
                    </span>

                </div>


                <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                    Create Category
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                    Create a new category and define its hierarchy, visibility,
                    ordering and presentation.
                </p>

            </div>


            {{-- BACK --}}
            @if(Route::has('category.list'))

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

            @endif

        </div>

    </div>


    {{-- =========================================================
         FORM LAYOUT
    ========================================================== --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">


        {{-- =====================================================
             MAIN FORM
        ====================================================== --}}
        <div>

            <form
                action="{{ route('category.store') }}"
                method="POST"
                class="overflow-hidden rounded-2xl
                       border border-white/5
                       bg-gray-900/80
                       shadow-2xl shadow-black/10"
            >

                @csrf


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
                                class="flex h-9 w-9 items-center justify-center
                                       rounded-xl bg-indigo-500/10
                                       text-indigo-400"
                            >
                                <i class="fa-solid fa-circle-info"></i>
                            </div>

                            <div>

                                <h2 class="text-sm font-bold text-white">
                                    Basic information
                                </h2>

                                <p class="mt-0.5 text-xs text-gray-500">
                                    Identify and describe your category.
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

                                <span
                                    class="pointer-events-none
                                           absolute left-3.5 top-1/2
                                           flex -translate-y-1/2
                                           items-center
                                           text-sm text-gray-500"
                                >
                                    <i class="fa-solid fa-tag"></i>
                                </span>

                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    value="{{ old('name') }}"
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
                                           focus:ring-4
                                           focus:ring-indigo-500/10"
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

                                <span
                                    class="pointer-events-none
                                           absolute left-3.5 top-1/2
                                           flex -translate-y-1/2
                                           items-center
                                           text-sm text-gray-500"
                                >
                                    <i class="fa-solid fa-link"></i>
                                </span>

                                <input
                                    type="text"
                                    name="slug"
                                    id="slug"
                                    value="{{ old('slug') }}"
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
                                           focus:ring-4
                                           focus:ring-indigo-500/10"
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
                                       focus:ring-4
                                       focus:ring-indigo-500/10"
                            >{{ old('description') }}</textarea>

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
                     STRUCTURE
                ================================================== --}}
                <section class="border-t border-white/5">

                    <div
                        class="border-b border-white/5
                               px-6 py-5 sm:px-7"
                    >

                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-9 w-9 items-center justify-center
                                       rounded-xl bg-violet-500/10
                                       text-violet-400"
                            >
                                <i class="fa-solid fa-folder-tree"></i>
                            </div>

                            <div>

                                <h2 class="text-sm font-bold text-white">
                                    Category structure
                                </h2>

                                <p class="mt-0.5 text-xs text-gray-500">
                                    Define where this category belongs.
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
                                    class="w-full appearance-none
                                           rounded-xl
                                           border border-white/10
                                           bg-gray-950/60
                                           py-3 pl-10 pr-10
                                           text-sm text-gray-200
                                           outline-none transition
                                           focus:border-indigo-500/50
                                           focus:ring-4
                                           focus:ring-indigo-500/10"
                                >

                                    <option value="">
                                        None — Root category
                                    </option>

                                    @if(isset($categories))

                                        @foreach($categories as $parent)

                                            <option
                                                value="{{ $parent->id }}"
                                                @selected(old('parent_id') == $parent->id)
                                            >
                                                {{ $parent->name }}
                                            </option>

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
                                Leave empty to create a top-level category.
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
                                    value="{{ old('display_order', 0) }}"
                                    min="0"
                                    class="w-full rounded-xl
                                           border border-white/10
                                           bg-gray-950/60
                                           py-3 pl-10 pr-4
                                           text-sm text-white
                                           outline-none transition
                                           focus:border-indigo-500/50
                                           focus:ring-4
                                           focus:ring-indigo-500/10"
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
                                class="flex h-9 w-9 items-center justify-center
                                       rounded-xl bg-fuchsia-500/10
                                       text-fuchsia-400"
                            >
                                <i class="fa-solid fa-palette"></i>
                            </div>

                            <div>

                                <h2 class="text-sm font-bold text-white">
                                    Presentation
                                </h2>

                                <p class="mt-0.5 text-xs text-gray-500">
                                    Configure how the category appears.
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
                                               focus:ring-4
                                               focus:ring-indigo-500/10"
                                    />

                                </div>


                                {{-- ICON PREVIEW --}}
                                <div
                                    class="flex h-[46px] items-center
                                           justify-center rounded-xl
                                           border border-white/10
                                           bg-gray-950/60
                                           text-indigo-400"
                                    title="Icon preview"
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
                                   rounded-xl border border-white/5
                                   bg-gray-950/40 px-4 py-4"
                        >

                            <div class="flex items-center gap-3">

                                <div
                                    class="flex h-9 w-9 items-center
                                           justify-center rounded-lg
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
                                        Make this category available immediately.
                                    </p>

                                </div>

                            </div>


                            <label
                                class="relative inline-flex cursor-pointer
                                       items-center"
                            >

                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    class="peer sr-only"
                                    @checked(old('is_active', '1') == '1')
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
                                           after:border
                                           after:border-gray-600
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
                     FOOTER ACTIONS
                ================================================== --}}
                <div
                    class="flex flex-col-reverse gap-3
                           border-t border-white/5
                           bg-gray-950/30
                           px-6 py-5
                           sm:flex-row sm:items-center
                           sm:justify-end
                           sm:px-7"
                >

                    @if(Route::has('category.list'))

                        <a
                            href="{{ route('category.list') }}"
                            class="inline-flex items-center justify-center
                                   gap-2 rounded-xl
                                   border border-white/10
                                   bg-white/5 px-5 py-2.5
                                   text-sm font-semibold
                                   text-gray-300
                                   transition
                                   hover:bg-white/10
                                   hover:text-white
                                   active:scale-[0.98]"
                        >
                            Cancel
                        </a>

                    @endif


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
                        <i class="fa-solid fa-plus"></i>
                        Create Category
                    </button>

                </div>

            </form>

        </div>


        {{-- =====================================================
             RIGHT SIDEBAR
        ====================================================== --}}
        <aside class="space-y-4">


            {{-- PREVIEW --}}
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
                            class="flex h-8 w-8 items-center justify-center
                                   rounded-lg bg-indigo-500/10
                                   text-indigo-400"
                        >
                            <i class="fa-solid fa-eye text-xs"></i>
                        </div>

                        <div>

                            <h2 class="text-sm font-bold text-white">
                                Preview
                            </h2>

                            <p class="text-xs text-gray-500">
                                Category appearance
                            </p>

                        </div>

                    </div>

                </div>


                <div class="p-5">

                    <div
                        class="rounded-xl border border-white/5
                               bg-gray-950/50 p-4"
                    >

                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-11 w-11
                                       items-center justify-center
                                       rounded-xl bg-indigo-500/10
                                       text-indigo-400"
                            >
                                <i
                                    :class="icon || 'fa-solid fa-folder'"
                                ></i>
                            </div>

                            <div class="min-w-0">

                                <p class="truncate text-sm font-bold text-white">
                                    <span id="preview-name">
                                        {{ old('name', 'New Category') }}
                                    </span>
                                </p>

                                <p class="mt-0.5 text-xs text-gray-500">
                                    {{ old('slug', 'new-category') }}
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- STRUCTURE INFO --}}
            <div
                class="rounded-2xl border border-white/5
                       bg-gray-900/80
                       p-5
                       shadow-2xl shadow-black/10"
            >

                <div class="flex items-start gap-3">

                    <div
                        class="flex h-9 w-9 flex-shrink-0
                               items-center justify-center
                               rounded-xl bg-violet-500/10
                               text-violet-400"
                    >
                        <i class="fa-solid fa-circle-info"></i>
                    </div>

                    <div>

                        <h3 class="text-sm font-bold text-white">
                            Category hierarchy
                        </h3>

                        <p class="mt-2 text-xs leading-5 text-gray-500">
                            Select a parent category to create a nested
                            category. Leaving the parent empty creates a
                            root-level category.
                        </p>

                    </div>

                </div>

            </div>


            {{-- STATUS INFO --}}
            <div
                class="rounded-2xl border border-white/5
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
                            Active categories are immediately available
                            throughout the system.
                        </p>

                    </div>

                </div>

            </div>

        </aside>

    </div>

</div>


{{-- =============================================================
     SMALL PREVIEW HELPER
============================================================= --}}
<script>
    document.addEventListener('input', function (event) {

        if (event.target.id === 'name') {
            const preview = document.getElementById('preview-name');

            if (preview) {
                preview.textContent = event.target.value || 'New Category';
            }
        }

    });
</script>

@endsection