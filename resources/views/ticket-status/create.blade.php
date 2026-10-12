@extends('layouts.base')

@section('title', 'Create Ticket Status - Iris App')

@section('content')

<div
    x-data="{
        statusName: @js(old('name', '')),
        code: @js(old('code', '')),
        description: @js(old('description', '')),
        color: @js(old('color', '#6366f1')),
        icon: @js(old('icon', 'fa-solid fa-circle-half-stroke')),
        displayOrder: @js(old('display_order', 0)),
        isInitial: @js(old('is_initial', false)),
        isFinal: @js(old('is_final', false)),
        stopsSla: @js(old('stops_sla', false)),
        requiresComment: @js(old('requires_comment', false)),
        isActive: @js(old('is_active', true))
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
                        <i class="fa-solid fa-circle-half-stroke"></i>
                    </span>

                    <span
                        class="text-xs font-semibold uppercase
                               tracking-[0.18em] text-indigo-400"
                    >
                        Ticket Management
                    </span>

                </div>

                <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                    Create Ticket Status
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                    Create a status to define a stage in the lifecycle of a support ticket.
                </p>

            </div>

            @if(Route::has('ticket.status.list'))

                <a
                    href="{{ route('ticket.status.list') }}"
                    class="inline-flex items-center justify-center gap-2
                           rounded-xl border border-white/10
                           bg-white/5 px-4 py-2.5
                           text-sm font-semibold text-gray-300
                           transition
                           hover:bg-white/10 hover:text-white
                           active:scale-[0.98]"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Statuses
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
                action="{{ route('ticket.status.create.form') }}"
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
                                    Define the identity and appearance of this status.
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
                                Status name
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
                                    x-model="statusName"
                                    value="{{ old('name') }}"
                                    required
                                    maxlength="100"
                                    autocomplete="off"
                                    placeholder="e.g. In Progress"
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
                                <p class="mt-2 flex items-center gap-1.5 text-xs text-red-400">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- CODE --}}
                        <div>

                            <label
                                for="code"
                                class="mb-2 block text-xs
                                       font-semibold uppercase
                                       tracking-wider text-gray-400"
                            >
                                Status code
                                <span class="text-red-400">*</span>
                            </label>

                            <div class="relative">

                                <i
                                    class="fa-solid fa-code
                                           pointer-events-none
                                           absolute left-3.5 top-1/2
                                           -translate-y-1/2
                                           text-sm text-gray-500"
                                ></i>

                                <input
                                    type="text"
                                    name="code"
                                    id="code"
                                    x-model="code"
                                    value="{{ old('code') }}"
                                    required
                                    maxlength="50"
                                    autocomplete="off"
                                    placeholder="e.g. IN_PROGRESS"
                                    class="w-full rounded-xl
                                           border border-white/10
                                           bg-gray-950/60
                                           py-3 pl-10 pr-4
                                           text-sm font-mono text-white
                                           placeholder:text-gray-600
                                           uppercase
                                           outline-none transition
                                           focus:border-indigo-500/50
                                           focus:ring-4
                                           focus:ring-indigo-500/10"
                                />

                            </div>

                            <p class="mt-2 text-[11px] text-gray-600">
                                Machine-readable identifier used internally by the system.
                            </p>

                            @error('code')
                                <p class="mt-2 flex items-center gap-1.5 text-xs text-red-400">
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
                                Status color
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
                                        maxlength="30"
                                        autocomplete="off"
                                        placeholder="#6366F1"
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
                                Use a HEX or CSS color.
                            </p>

                            @error('color')
                                <p class="mt-2 flex items-center gap-1.5 text-xs text-red-400">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- ICON --}}
                        <div>

                            <label
                                for="icon"
                                class="mb-2 block text-xs
                                       font-semibold uppercase
                                       tracking-wider text-gray-400"
                            >
                                Icon class
                            </label>

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
                                    value="{{ old('icon', 'fa-solid fa-circle-half-stroke') }}"
                                    maxlength="100"
                                    autocomplete="off"
                                    placeholder="fa-solid fa-clock"
                                    class="w-full rounded-xl
                                           border border-white/10
                                           bg-gray-950/60
                                           py-3 pl-10 pr-4
                                           text-sm font-mono text-white
                                           placeholder:text-gray-600
                                           outline-none transition
                                           focus:border-indigo-500/50
                                           focus:ring-4
                                           focus:ring-indigo-500/10"
                                />

                            </div>

                            <p class="mt-2 text-xs text-gray-600">
                                Font Awesome icon class.
                            </p>

                            @error('icon')
                                <p class="mt-2 flex items-center gap-1.5 text-xs text-red-400">
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
                                x-model="description"
                                rows="5"
                                maxlength="500"
                                placeholder="Describe when this status should be used..."
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
                                <p class="mt-2 flex items-center gap-1.5 text-xs text-red-400">
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
                                    class="fa-solid fa-list-ol
                                           pointer-events-none
                                           absolute left-3.5 top-1/2
                                           -translate-y-1/2
                                           text-sm text-gray-500"
                                ></i>

                                <input
                                    type="number"
                                    name="display_order"
                                    id="display_order"
                                    x-model="displayOrder"
                                    value="{{ old('display_order', 0) }}"
                                    min="0"
                                    max="999999"
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
                                Controls the order in which statuses are displayed.
                            </p>

                            @error('display_order')
                                <p class="mt-2 flex items-center gap-1.5 text-xs text-red-400">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    </div>

                </section>


                {{-- =================================================
                     BEHAVIOR
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
                                <i class="fa-solid fa-sliders"></i>
                            </div>

                            <div>

                                <h2 class="text-sm font-bold text-white">
                                    Status behavior
                                </h2>

                                <p class="mt-0.5 text-xs text-gray-500">
                                    Configure how this status behaves in the ticket lifecycle.
                                </p>

                            </div>

                        </div>

                    </div>


                    <div class="space-y-3 p-6 sm:p-7">

                        {{-- INITIAL --}}
                        <label
                            class="group flex cursor-pointer items-start gap-4
                                   rounded-xl border border-white/5
                                   bg-gray-950/40 p-4
                                   transition
                                   hover:border-indigo-500/20
                                   hover:bg-indigo-500/5"
                        >

                            <input
                                type="checkbox"
                                name="is_initial"
                                value="1"
                                x-model="isInitial"
                                @checked(old('is_initial', false))
                                class="mt-1 h-4 w-4 rounded
                                       border-gray-600 bg-gray-900
                                       text-indigo-600
                                       focus:ring-indigo-500"
                            >

                            <span>

                                <span class="flex items-center gap-2 text-sm font-semibold text-white">
                                    <i class="fa-solid fa-play text-indigo-400"></i>
                                    Initial status
                                </span>

                                <span class="mt-1 block text-xs leading-5 text-gray-500">
                                    New tickets can start their lifecycle with this status.
                                </span>

                            </span>

                        </label>


                        {{-- FINAL --}}
                        <label
                            class="group flex cursor-pointer items-start gap-4
                                   rounded-xl border border-white/5
                                   bg-gray-950/40 p-4
                                   transition
                                   hover:border-emerald-500/20
                                   hover:bg-emerald-500/5"
                        >

                            <input
                                type="checkbox"
                                name="is_final"
                                value="1"
                                x-model="isFinal"
                                @checked(old('is_final', false))
                                class="mt-1 h-4 w-4 rounded
                                       border-gray-600 bg-gray-900
                                       text-indigo-600
                                       focus:ring-indigo-500"
                            >

                            <span>

                                <span class="flex items-center gap-2 text-sm font-semibold text-white">
                                    <i class="fa-solid fa-flag-checkered text-emerald-400"></i>
                                    Final status
                                </span>

                                <span class="mt-1 block text-xs leading-5 text-gray-500">
                                    Indicates that the ticket has reached the end of its lifecycle.
                                </span>

                            </span>

                        </label>


                        {{-- STOP SLA --}}
                        <label
                            class="group flex cursor-pointer items-start gap-4
                                   rounded-xl border border-white/5
                                   bg-gray-950/40 p-4
                                   transition
                                   hover:border-amber-500/20
                                   hover:bg-amber-500/5"
                        >

                            <input
                                type="checkbox"
                                name="stops_sla"
                                value="1"
                                x-model="stopsSla"
                                @checked(old('stops_sla', false))
                                class="mt-1 h-4 w-4 rounded
                                       border-gray-600 bg-gray-900
                                       text-indigo-600
                                       focus:ring-indigo-500"
                            >

                            <span>

                                <span class="flex items-center gap-2 text-sm font-semibold text-white">
                                    <i class="fa-solid fa-stopwatch text-amber-400"></i>
                                    Stop SLA timer
                                </span>

                                <span class="mt-1 block text-xs leading-5 text-gray-500">
                                    Stops SLA timing while the ticket remains in this status.
                                </span>

                            </span>

                        </label>


                        {{-- REQUIRE COMMENT --}}
                        <label
                            class="group flex cursor-pointer items-start gap-4
                                   rounded-xl border border-white/5
                                   bg-gray-950/40 p-4
                                   transition
                                   hover:border-blue-500/20
                                   hover:bg-blue-500/5"
                        >

                            <input
                                type="checkbox"
                                name="requires_comment"
                                value="1"
                                x-model="requiresComment"
                                @checked(old('requires_comment', false))
                                class="mt-1 h-4 w-4 rounded
                                       border-gray-600 bg-gray-900
                                       text-indigo-600
                                       focus:ring-indigo-500"
                            >

                            <span>

                                <span class="flex items-center gap-2 text-sm font-semibold text-white">
                                    <i class="fa-solid fa-comment-dots text-blue-400"></i>
                                    Require comment
                                </span>

                                <span class="mt-1 block text-xs leading-5 text-gray-500">
                                    Requires a comment whenever a ticket enters this status.
                                </span>

                            </span>

                        </label>

                    </div>

                </section>


                {{-- =================================================
                     STATUS
                ================================================== --}}
                <section class="border-t border-white/5">

                    <div class="p-6 sm:p-7">

                        <div
                            class="flex items-center justify-between
                                   rounded-xl border border-white/5
                                   bg-gray-950/40 px-4 py-4"
                        >

                            <div class="flex items-center gap-3">

                                <div
                                    class="flex h-9 w-9 items-center justify-center
                                           rounded-lg bg-emerald-500/10
                                           text-emerald-400"
                                >
                                    <i class="fa-solid fa-circle-check"></i>
                                </div>

                                <div>

                                    <p class="text-sm font-semibold text-white">
                                        Active status
                                    </p>

                                    <p class="mt-0.5 text-xs text-gray-500">
                                        Active statuses can be assigned to tickets.
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
                                    @checked(old('is_active', true))
                                >

                                <div
                                    class="h-6 w-11 rounded-full
                                           bg-gray-700 transition
                                           peer-focus:ring-4
                                           peer-focus:ring-indigo-500/20
                                           peer-checked:bg-indigo-600
                                           after:absolute
                                           after:left-[2px]
                                           after:top-[2px]
                                           after:h-5
                                           after:w-5
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

                    @if(Route::has('ticket.status.list'))

                        <a
                            href="{{ route('ticket.status.list') }}"
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
                        Create Status
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
                                Status appearance
                            </p>

                        </div>

                    </div>

                </div>


                <div class="space-y-4 p-5">

                    {{-- STATUS CARD --}}
                    <div
                        class="rounded-xl border border-white/5
                               bg-gray-950/50 p-4"
                    >

                        <div class="flex items-center gap-3">

                            <div
                                class="flex h-11 w-11 flex-shrink-0
                                       items-center justify-center
                                       rounded-xl"
                                :style="`
                                    background-color: ${color}18;
                                    color: ${color};
                                    border: 1px solid ${color}30;
                                `"
                            >
                                <i
                                    :class="icon || 'fa-solid fa-circle-half-stroke'"
                                    class="text-base"
                                ></i>
                            </div>


                            <div class="min-w-0 flex-1">

                                <div class="flex flex-wrap items-center gap-2">

                                    <p
                                        class="truncate text-sm font-bold text-white"
                                        x-text="statusName || 'New Status'"
                                    ></p>

                                    <span
                                        class="rounded-full px-2 py-0.5
                                               text-[9px] font-bold
                                               uppercase tracking-wider"
                                        :style="`
                                            background-color: ${color}18;
                                            color: ${color};
                                            border: 1px solid ${color}30;
                                        `"
                                        x-text="code || 'STATUS'"
                                    ></span>

                                </div>

                                <p
                                    class="mt-1 truncate text-xs text-gray-500"
                                    x-text="description || 'Ticket status preview'"
                                ></p>

                            </div>

                        </div>

                    </div>


                    {{-- FLAGS --}}
                    <div
                        class="rounded-xl border border-white/5
                               bg-gray-950/40 p-4"
                    >

                        <p
                            class="mb-3 text-xs font-semibold
                                   uppercase tracking-wider text-gray-500"
                        >
                            Behavior
                        </p>

                        <div class="flex flex-wrap gap-2">

                            <span
                                x-show="isInitial"
                                class="inline-flex items-center gap-1.5
                                       rounded-full border
                                       border-indigo-500/20
                                       bg-indigo-500/10
                                       px-2.5 py-1
                                       text-[10px] font-semibold
                                       text-indigo-400"
                            >
                                <i class="fa-solid fa-play"></i>
                                Initial
                            </span>

                            <span
                                x-show="isFinal"
                                class="inline-flex items-center gap-1.5
                                       rounded-full border
                                       border-emerald-500/20
                                       bg-emerald-500/10
                                       px-2.5 py-1
                                       text-[10px] font-semibold
                                       text-emerald-400"
                            >
                                <i class="fa-solid fa-flag-checkered"></i>
                                Final
                            </span>

                            <span
                                x-show="stopsSla"
                                class="inline-flex items-center gap-1.5
                                       rounded-full border
                                       border-amber-500/20
                                       bg-amber-500/10
                                       px-2.5 py-1
                                       text-[10px] font-semibold
                                       text-amber-400"
                            >
                                <i class="fa-solid fa-stopwatch"></i>
                                Stops SLA
                            </span>

                            <span
                                x-show="requiresComment"
                                class="inline-flex items-center gap-1.5
                                       rounded-full border
                                       border-blue-500/20
                                       bg-blue-500/10
                                       px-2.5 py-1
                                       text-[10px] font-semibold
                                       text-blue-400"
                            >
                                <i class="fa-solid fa-comment-dots"></i>
                                Comment required
                            </span>

                            <span
                                x-show="!isInitial && !isFinal && !stopsSla && !requiresComment"
                                class="text-xs text-gray-600"
                            >
                                No special behavior configured.
                            </span>

                        </div>

                    </div>


                    {{-- STATUS COLOR --}}
                    <div
                        class="rounded-xl border border-white/5
                               bg-gray-950/40 p-4"
                    >

                        <div class="mb-3 flex items-center justify-between">

                            <span
                                class="text-xs font-semibold
                                       uppercase tracking-wider text-gray-500"
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


                    {{-- ACTIVE STATE --}}
                    <div
                        class="flex items-center justify-between
                               rounded-xl border border-white/5
                               bg-gray-950/40 px-4 py-3"
                    >

                        <div class="flex items-center gap-2.5">

                            <i
                                class="fa-solid fa-circle text-[8px]"
                                :class="isActive ? 'text-emerald-400' : 'text-gray-600'"
                            ></i>

                            <span class="text-xs font-medium text-gray-400">
                                <span
                                    x-text="isActive ? 'Active status' : 'Inactive status'"
                                ></span>
                            </span>

                        </div>

                        <span
                            class="text-[10px] font-semibold uppercase tracking-wider"
                            :class="isActive ? 'text-emerald-400' : 'text-gray-600'"
                            x-text="isActive ? 'Enabled' : 'Disabled'"
                        ></span>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 LIFECYCLE INFO
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
                               rounded-xl bg-violet-500/10
                               text-violet-400"
                    >
                        <i class="fa-solid fa-route"></i>
                    </div>

                    <div>

                        <h3 class="text-sm font-bold text-white">
                            Ticket lifecycle
                        </h3>

                        <p class="mt-2 text-xs leading-5 text-gray-500">
                            Statuses define the stages a ticket can move through,
                            from its initial state to its final state.
                        </p>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 SLA INFO
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
                               rounded-xl bg-amber-500/10
                               text-amber-400"
                    >
                        <i class="fa-solid fa-stopwatch"></i>
                    </div>

                    <div>

                        <h3 class="text-sm font-bold text-white">
                            SLA behavior
                        </h3>

                        <p class="mt-2 text-xs leading-5 text-gray-500">
                            Enable <span class="text-gray-300">Stop SLA timer</span>
                            when the ticket should no longer consume SLA time
                            while waiting in this state.
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
