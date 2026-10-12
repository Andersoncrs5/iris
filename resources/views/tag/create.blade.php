@extends('layouts.base')

@section('title', 'Create Tag - Iris App')

@section('content')

<div
    x-data="{
        icon: 'fa-solid fa-tag',
        tagName: @js(old('name', '')),
        color: @js(old('color', '#6366f1')),
        type: @js(old('type', 'GENERAL')),
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
                        <i class="fa-solid fa-tag"></i>
                    </span>

                    <span
                        class="text-xs font-semibold uppercase
                               tracking-[0.18em] text-indigo-400"
                    >
                        Catalog
                    </span>

                </div>


                <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                    Create Tag
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                    Create a new tag to organize, classify and identify
                    information across the support system.
                </p>

            </div>


            {{-- BACK --}}
            @if(Route::has('tag.list'))

                <a
                    href="{{ route('tag.list') }}"
                    class="inline-flex items-center justify-center gap-2
                           rounded-xl border border-white/10
                           bg-white/5 px-4 py-2.5
                           text-sm font-semibold text-gray-300
                           transition
                           hover:bg-white/10 hover:text-white
                           active:scale-[0.98]"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Tags
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
                action="{{ route('tag.store') }}"
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
                                    Define the identity of your tag.
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
                                Tag name
                                <span class="text-red-400">*</span>
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-solid fa-hashtag
                                           pointer-events-none
                                           absolute left-3.5 top-1/2
                                           -translate-y-1/2
                                           text-sm text-gray-500"
                                ></i>

                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    x-model="tagName"
                                    value="{{ old('name') }}"
                                    required
                                    autocomplete="off"
                                    placeholder="e.g. Important"
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
                                    value="{{ old('slug') }}"
                                    autocomplete="off"
                                    placeholder="important"
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
                                Used as the tag's URL-friendly identifier.
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
                                placeholder="Describe what this tag represents..."
                                class="w-full resize-none rounded-xl
                                       border border-white/10
                                       bg-gray-950/60
                                       px-4 py-3
                                       text-sm leading-6 text-white
                                       placeholder:text-gray-600
                                       outline-none transition
                                       focus:border-indigo-500/50
                                       focus:ring-4 focus:ring-indigo-500/10"
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
                     CLASSIFICATION
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
                                <i class="fa-solid fa-layer-group"></i>
                            </div>

                            <div>

                                <h2 class="text-sm font-bold text-white">
                                    Classification
                                </h2>

                                <p class="mt-0.5 text-xs text-gray-500">
                                    Define how this tag should be categorized.
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2 sm:p-7">


                        {{-- TYPE --}}
                        <div>

                            <label
                                for="type"
                                class="mb-2 block text-xs
                                       font-semibold uppercase
                                       tracking-wider text-gray-400"
                            >
                                Tag type
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-solid fa-layer-group
                                           pointer-events-none
                                           absolute left-3.5 top-1/2
                                           -translate-y-1/2
                                           text-sm text-gray-500"
                                ></i>

                                <select
                                    name="type"
                                    id="type"
                                    x-model="type"
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

                                    <option value="GENERAL">
                                        General
                                    </option>

                                    <option value="SYSTEM">
                                        System
                                    </option>

                                    <option value="CONTENT">
                                        Content
                                    </option>

                                    <option value="PRODUCT">
                                        Product
                                    </option>

                                    <option value="STATUS">
                                        Status
                                    </option>

                                </select>

                                <i
                                    class="fa-solid fa-chevron-down
                                           pointer-events-none
                                           absolute right-3 top-1/2
                                           -translate-y-1/2
                                           text-[10px] text-gray-600"
                                ></i>

                            </div>

                            @error('type')

                                <p
                                    class="mt-2 flex items-center gap-1.5
                                           text-xs text-red-400"
                                >
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- COLOR --}}
                        <div>

                            <label
                                for="color"
                                class="mb-2 block text-xs
                                       font-semibold uppercase
                                       tracking-wider text-gray-400"
                            >
                                Tag color
                            </label>

                            <div class="grid grid-cols-[1fr_58px] gap-2">

                                <div class="relative">

                                    <i
                                        class="fa-solid fa-palette
                                               pointer-events-none
                                               absolute left-3.5 top-1/2
                                               -translate-y-1/2
                                               text-sm text-gray-500"
                                    ></i>

                                    <input
                                        type="text"
                                        name="color"
                                        id="color"
                                        x-model="color"
                                        value="{{ old('color', '#6366f1') }}"
                                        placeholder="#4F46E5"
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


                                {{-- COLOR PICKER --}}
                                <div class="relative h-[46px]">

                                    <input
                                        type="color"
                                        x-model="color"
                                        class="absolute inset-0
                                               h-full w-full cursor-pointer
                                               rounded-xl border border-white/10
                                               bg-gray-950/60 p-1"
                                    >

                                </div>

                            </div>

                            <p class="mt-2 text-xs text-gray-600">
                                Use a valid HEX or CSS color.
                            </p>

                            @error('color')

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
                     STATUS
                ================================================== --}}
                <section class="border-t border-white/5">

                    <div
                        class="px-6 py-5 sm:px-7"
                    >

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
                                        Active tag
                                    </p>

                                    <p class="mt-0.5 text-xs text-gray-500">
                                        Make this tag available immediately.
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
                           sm:justify-end sm:px-7"
                >

                    @if(Route::has('tag.list'))

                        <a
                            href="{{ route('tag.list') }}"
                            class="inline-flex items-center justify-center
                                   gap-2 rounded-xl
                                   border border-white/10
                                   bg-white/5 px-5 py-2.5
                                   text-sm font-semibold text-gray-300
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
                               gap-2 rounded-xl bg-indigo-600
                               px-6 py-2.5
                               text-sm font-bold text-white
                               shadow-lg shadow-indigo-600/20
                               transition
                               hover:-translate-y-0.5
                               hover:bg-indigo-500
                               active:translate-y-0"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Create Tag
                    </button>

                </div>

            </form>

        </div>


        {{-- =====================================================
             RIGHT SIDEBAR
        ====================================================== --}}
        <aside class="space-y-4">


            {{-- =================================================
                 LIVE PREVIEW
            ================================================== --}}
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
                                Tag appearance
                            </p>

                        </div>

                    </div>

                </div>


                <div class="space-y-4 p-5">

                    {{-- PREVIEW CARD --}}
                    <div
                        class="rounded-xl border border-white/5
                               bg-gray-950/50 p-4"
                    >

                        <div class="flex items-center gap-3">

                            {{-- COLOR / ICON --}}
                            <div
                                class="flex h-11 w-11
                                       flex-shrink-0
                                       items-center justify-center
                                       rounded-xl"
                                :style="`background-color: ${color}15; color: ${color}`"
                            >
                                <i
                                    :class="icon"
                                    class="text-base"
                                ></i>
                            </div>


                            <div class="min-w-0 flex-1">

                                <div
                                    class="flex flex-wrap
                                           items-center gap-2"
                                >

                                    <p
                                        class="truncate
                                               text-sm font-bold text-white"
                                        x-text="tagName || 'New Tag'"
                                    ></p>


                                    {{-- TYPE --}}
                                    <span
                                        class="rounded-full
                                               px-2 py-0.5
                                               text-[9px]
                                               font-bold uppercase
                                               tracking-wider"
                                        :style="`
                                            background-color: ${color}18;
                                            color: ${color};
                                            border: 1px solid ${color}30;
                                        `"
                                        x-text="type"
                                    ></span>

                                </div>


                                <p
                                    class="mt-1 text-xs text-gray-500"
                                >
                                    Tag preview
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- COLOR PREVIEW --}}
                    <div
                        class="rounded-xl border border-white/5
                               bg-gray-950/40 p-4"
                    >

                        <div class="mb-3 flex items-center justify-between">

                            <span
                                class="text-xs font-semibold uppercase
                                       tracking-wider text-gray-500"
                            >
                                Selected color
                            </span>

                            <span
                                class="font-mono text-xs text-gray-400"
                                x-text="color || '#6366f1'"
                            ></span>

                        </div>


                        <div
                            class="h-2.5 w-full rounded-full"
                            :style="`background-color: ${color || '#6366f1'}`"
                        ></div>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 TAG TYPES
            ================================================== --}}
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
                               rounded-xl
                               bg-violet-500/10
                               text-violet-400"
                    >
                        <i class="fa-solid fa-layer-group"></i>
                    </div>

                    <div>

                        <h3 class="text-sm font-bold text-white">
                            Tag types
                        </h3>

                        <p class="mt-2 text-xs leading-5 text-gray-500">
                            Tags can be grouped by their intended purpose,
                            such as system metadata, content or product
                            classification.
                        </p>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 STATUS INFO
            ================================================== --}}
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
                               rounded-xl
                               bg-emerald-500/10
                               text-emerald-400"
                    >
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                    <div>

                        <h3 class="text-sm font-bold text-white">
                            Visibility
                        </h3>

                        <p class="mt-2 text-xs leading-5 text-gray-500">
                            Active tags are immediately available for use
                            throughout the system.
                        </p>

                    </div>

                </div>

            </div>

        </aside>

    </div>

</div>

<style>
    input[type="color"] {
        appearance: none;
        -webkit-appearance: none;
    }

    input[type="color"]::-webkit-color-swatch-wrapper {
        padding: 0;
    }

    input[type="color"]::-webkit-color-swatch {
        border: none;
        border-radius: 0.625rem;
    }

    input[type="color"]::-moz-color-swatch {
        border: none;
        border-radius: 0.625rem;
    }
</style>

@endsection