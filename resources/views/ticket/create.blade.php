
@extends('layouts.base')

@section('title', 'Create Ticket | Iris')

@section('content')
@php
    $safeColor = static fn (?string $color): string =>
        preg_match('/^#[0-9a-fA-F]{6}$/', $color ?? '')
            ? $color
            : '#818cf8';

    $categoryById = $categories->keyBy('id');

    $categoryOptions = $categories->map(function ($category) use ($categoryById) {
        $parent = $category->parent_id !== null
            ? $categoryById->get($category->parent_id)
            : null;

        return [
            'id' => (string) $category->id,
            'name' => $category->name,
            'icon' => $category->icon ?: 'fa-solid fa-folder',
            'parentName' => $parent?->name,
            'displayOrder' => (int) $category->display_order,
        ];
    })->values()->all();

    $priorityOptions = $priorities->map(function ($priority) use ($safeColor) {
        return [
            'id' => (string) $priority->id,
            'name' => $priority->name,
            'code' => $priority->code,
            'level' => (int) $priority->level,
            'color' => $safeColor($priority->color),
            'icon' => $priority->icon ?: 'fa-solid fa-flag',
            'isDefault' => (bool) $priority->is_default,
            'responseTime' => $priority->response_time_minutes,
            'resolutionTime' => $priority->resolution_time_minutes,
            'escalationTime' => $priority->escalation_time_minutes,
        ];
    })->values()->all();

    $selectedCategoryId = (string) old('category_id', '');
    $selectedPriorityId = (string) old(
        'priority_id',
        $defaultPriorityId ?? ''
    );

    $statusColor = $safeColor($initialStatus?->color);
@endphp

<div
    class="relative min-h-screen px-4 py-8 text-gray-100 sm:px-6 lg:px-8"
    x-data="{
        categoryOpen: false,
        categoryId: @js($selectedCategoryId),
        categories: @js($categoryOptions),

        priorityOpen: false,
        priorityId: @js($selectedPriorityId),
        priorities: @js($priorityOptions),

        get selectedCategory() {
            return this.categories.find(
                item => String(item.id) === String(this.categoryId)
            ) ?? null;
        },

        get selectedPriority() {
            return this.priorities.find(
                item => String(item.id) === String(this.priorityId)
            ) ?? null;
        }
    }"
>
    <div
        class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-[radial-gradient(ellipse_at_50%_0%,rgba(99,102,241,.16),transparent_70%)]"
        aria-hidden="true"
    ></div>

    <div class="mx-auto max-w-5xl">

        {{-- Header --}}
        <nav class="mb-6 flex items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="transition hover:text-gray-300">
                <i class="fa-solid fa-house text-xs" aria-hidden="true"></i>
                <span class="sr-only">Dashboard</span>
            </a>

            <span aria-hidden="true">/</span>
            <span>Tickets</span>
            <span aria-hidden="true">/</span>
            <span class="text-indigo-400" aria-current="page">Create ticket</span>
        </nav>

        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-indigo-500/20 bg-indigo-500/10 text-indigo-400">
                    <i class="fa-solid fa-ticket text-xl" aria-hidden="true"></i>
                </div>

                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-white">
                        Create a ticket
                    </h1>

                    <p class="mt-2 max-w-xl text-sm leading-6 text-gray-400">
                        Describe your issue and select the category and priority that best fit your request.
                    </p>
                </div>
            </div>

            <a
                href="{{ route('ticket.list') }}"
                class="inline-flex h-10 items-center justify-center gap-2 self-start rounded-xl border border-white/10 px-4 text-sm font-medium text-gray-300 transition hover:bg-white/[0.05] hover:text-white"
            >
                <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                Back to tickets
            </a>
        </div>

        {{-- Feedback --}}
        @if (session('error'))
            <div role="alert" class="mb-6 flex gap-3 rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-300">
                <i class="fa-solid fa-circle-exclamation mt-0.5" aria-hidden="true"></i>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="mb-6 flex gap-3 rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-300">
                <i class="fa-solid fa-circle-exclamation mt-0.5" aria-hidden="true"></i>
                <div>
                    <p class="font-semibold">Please review the form.</p>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-red-300/90">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if (! $initialStatus)
            <div role="alert" class="mb-6 flex gap-3 rounded-xl border border-amber-500/20 bg-amber-500/10 p-4 text-sm text-amber-200">
                <i class="fa-solid fa-triangle-exclamation mt-0.5" aria-hidden="true"></i>
                <div>
                    <p class="font-semibold">Ticket creation is not configured yet.</p>
                    <p class="mt-1 text-amber-200/80">
                        There is no active initial ticket status. An administrator must configure a status with the initial flag enabled.
                    </p>
                </div>
            </div>
        @endif

        @if ($categories->isEmpty() || $priorities->isEmpty())
            <div role="alert" class="mb-6 rounded-xl border border-amber-500/20 bg-amber-500/10 p-4 text-sm text-amber-200">
                Ticket creation requires at least one active category and one active priority.
            </div>
        @endif

        <form
            action="{{ route('ticket.create.form') }}"
            method="POST"
            class="space-y-6"
        >
            @csrf
            @method("POST")

            {{-- Requester --}}
            <section class="overflow-visible rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-400">
                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                    </div>

                    <div>
                        <h2 class="font-semibold text-white">Requester</h2>
                        <p class="mt-0.5 text-xs text-gray-500">
                            The ticket will be associated with your account.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-5 sm:p-6">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-indigo-500/20 bg-indigo-500/10 text-indigo-300">
                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                    </div>

                    <div class="min-w-0">
                        <p class="truncate font-medium text-white">
                            {{ auth()->user()->name }}
                        </p>

                        <p class="mt-1 truncate text-sm text-gray-400">
                            {{ auth()->user()->email }}
                        </p>
                    </div>

                    <span class="ml-auto shrink-0 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-300">
                        Current user
                    </span>
                </div>
            </section>

            {{-- Ticket details --}}
            <section class="overflow-visible rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-500/10 text-violet-400">
                        <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                    </div>

                    <div>
                        <h2 class="font-semibold text-white">Ticket details</h2>
                        <p class="mt-0.5 text-xs text-gray-500">
                            Provide a clear subject and description.
                        </p>
                    </div>
                </div>

                <div class="space-y-5 p-5 sm:p-6">
                    <div>
                        <label for="subject" class="mb-2 block text-sm font-medium text-gray-200">
                            Subject <span class="text-red-400">*</span>
                        </label>

                        <input
                            type="text"
                            name="subject"
                            id="subject"
                            value="{{ old('subject') }}"
                            maxlength="255"
                            required
                            autofocus
                            placeholder="Briefly describe the issue"
                            @class([
                                'w-full rounded-xl border bg-white/[0.03] px-4 py-3 text-sm text-white placeholder-gray-600 transition',
                                'focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10',
                                'border-red-500/50' => $errors->has('subject'),
                                'border-white/10' => ! $errors->has('subject'),
                            ])
                        />

                        @error('subject')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="mb-2 block text-sm font-medium text-gray-200">
                            Description <span class="text-red-400">*</span>
                        </label>

                        <textarea
                            name="description"
                            id="description"
                            rows="6"
                            required
                            placeholder="Explain what happened, what you expected, and any relevant details..."
                            @class([
                                'w-full resize-y rounded-xl border bg-white/[0.03] px-4 py-3 text-sm leading-6 text-white placeholder-gray-600 transition',
                                'focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10',
                                'border-red-500/50' => $errors->has('description'),
                                'border-white/10' => ! $errors->has('description'),
                            ])
                        >{{ old('description') }}</textarea>

                        @error('description')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- Classification --}}
            <section class="overflow-visible rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500/10 text-sky-400">
                        <i class="fa-solid fa-list-check" aria-hidden="true"></i>
                    </div>

                    <div>
                        <h2 class="font-semibold text-white">Classification</h2>
                        <p class="mt-0.5 text-xs text-gray-500">
                            Categorize your request and indicate its urgency.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 p-5 sm:grid-cols-2 sm:p-6">

                    {{-- Category selector --}}
                    <div
                        class="relative"
                        @click.outside="categoryOpen = false"
                        @keydown.escape.window="categoryOpen = false"
                    >
                        <label class="mb-2 block text-sm font-medium text-gray-200">
                            Category <span class="text-red-400">*</span>
                        </label>

                        <input
                            type="hidden"
                            name="category_id"
                            :value="categoryId"
                        />

                        <button
                            type="button"
                            @click="categoryOpen = !categoryOpen; priorityOpen = false"
                            :aria-expanded="categoryOpen.toString()"
                            aria-haspopup="listbox"
                            class="flex min-h-12 w-full items-center gap-3 rounded-xl border border-white/10 bg-white/[0.03] px-3.5 py-3 text-left transition hover:border-white/20 focus:outline-none focus:ring-4 focus:ring-indigo-500/10"
                            :class="categoryOpen ? 'border-indigo-500' : ''"
                        >
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/[0.05] text-indigo-300">
                                <i :class="selectedCategory?.icon ?? 'fa-solid fa-folder'" aria-hidden="true"></i>
                            </span>

                            <span class="min-w-0 flex-1">
                                <span
                                    class="block truncate text-sm font-medium"
                                    :class="selectedCategory ? 'text-white' : 'text-gray-500'"
                                    x-text="selectedCategory?.name ?? 'Select a category'"
                                ></span>

                                <span
                                    class="mt-1 block truncate font-mono text-xs text-gray-500"
                                    x-text="selectedCategory ? 'ID: ' + selectedCategory.id : 'Choose the best matching category'"
                                ></span>
                            </span>

                            <i
                                class="fa-solid fa-chevron-down text-xs text-gray-500 transition-transform"
                                :class="categoryOpen ? 'rotate-180' : ''"
                                aria-hidden="true"
                            ></i>
                        </button>

                        <div
                            x-show="categoryOpen"
                            x-cloak
                            x-transition.opacity
                            class="absolute left-0 right-0 z-30 mt-2 max-h-64 overflow-y-auto rounded-xl border border-white/10 bg-[#11111c] p-1.5 shadow-2xl shadow-black/50"
                            role="listbox"
                            aria-label="Ticket categories"
                        >
                            <template x-for="item in categories" :key="item.id">
                                <button
                                    type="button"
                                    role="option"
                                    :aria-selected="String(categoryId) === String(item.id)"
                                    @click="categoryId = String(item.id); categoryOpen = false"
                                    class="flex w-full items-center gap-3 rounded-lg px-3 py-3 text-left transition hover:bg-white/[0.06]"
                                    :class="String(categoryId) === String(item.id) ? 'bg-indigo-500/10' : ''"
                                >
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/[0.05] text-indigo-300">
                                        <i :class="item.icon || 'fa-solid fa-folder'" aria-hidden="true"></i>
                                    </span>

                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-medium text-gray-200" x-text="item.name"></span>

                                        <span class="mt-1 block text-xs text-gray-500">
                                            <span x-text="'ID: ' + item.id"></span>
                                            <span x-show="item.parentName" x-text="' · ' + item.parentName"></span>
                                        </span>
                                    </span>

                                    <i
                                        x-show="String(categoryId) === String(item.id)"
                                        class="fa-solid fa-check text-xs text-indigo-400"
                                        aria-hidden="true"
                                    ></i>
                                </button>
                            </template>

                            <p x-show="categories.length === 0" class="px-3 py-5 text-center text-sm text-gray-500">
                                No active categories available.
                            </p>
                        </div>

                        @error('category_id')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Priority selector --}}
                    <div
                        class="relative"
                        @click.outside="priorityOpen = false"
                        @keydown.escape.window="priorityOpen = false"
                    >
                        <label class="mb-2 block text-sm font-medium text-gray-200">
                            Priority <span class="text-red-400">*</span>
                        </label>

                        <input
                            type="hidden"
                            name="priority_id"
                            :value="priorityId"
                        />

                        <button
                            type="button"
                            @click="priorityOpen = !priorityOpen; categoryOpen = false"
                            :aria-expanded="priorityOpen.toString()"
                            aria-haspopup="listbox"
                            class="flex min-h-12 w-full items-center gap-3 rounded-xl border border-white/10 bg-white/[0.03] px-3.5 py-3 text-left transition hover:border-white/20 focus:outline-none focus:ring-4 focus:ring-indigo-500/10"
                            :class="priorityOpen ? 'border-indigo-500' : ''"
                        >
                            <span
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border"
                                :style="{
                                    color: selectedPriority?.color ?? '#818cf8',
                                    borderColor: (selectedPriority?.color ?? '#818cf8') + '55',
                                    backgroundColor: (selectedPriority?.color ?? '#818cf8') + '15'
                                }"
                            >
                                <i :class="selectedPriority?.icon ?? 'fa-solid fa-flag'" aria-hidden="true"></i>
                            </span>

                            <span class="min-w-0 flex-1">
                                <span
                                    class="flex flex-wrap items-center gap-2 text-sm font-medium"
                                    :class="selectedPriority ? 'text-white' : 'text-gray-500'"
                                >
                                    <span x-text="selectedPriority?.name ?? 'Select a priority'"></span>

                                    <span
                                        x-show="selectedPriority?.isDefault"
                                        class="rounded-md border border-amber-500/20 bg-amber-500/10 px-1.5 py-0.5 text-[10px] font-semibold text-amber-300"
                                    >
                                        DEFAULT
                                    </span>
                                </span>

                                <span
                                    class="mt-1 block truncate font-mono text-xs text-gray-500"
                                    x-text="selectedPriority ? selectedPriority.code + ' · Level ' + selectedPriority.level : 'Choose the urgency of the request'"
                                ></span>
                            </span>

                            <i
                                class="fa-solid fa-chevron-down text-xs text-gray-500 transition-transform"
                                :class="priorityOpen ? 'rotate-180' : ''"
                                aria-hidden="true"
                            ></i>
                        </button>

                        <div
                            x-show="priorityOpen"
                            x-cloak
                            x-transition.opacity
                            class="absolute left-0 right-0 z-30 mt-2 max-h-80 overflow-y-auto rounded-xl border border-white/10 bg-[#11111c] p-1.5 shadow-2xl shadow-black/50"
                            role="listbox"
                            aria-label="Ticket priorities"
                        >
                            <template x-for="item in priorities" :key="item.id">
                                <button
                                    type="button"
                                    role="option"
                                    :aria-selected="String(priorityId) === String(item.id)"
                                    @click="priorityId = String(item.id); priorityOpen = false"
                                    class="flex w-full items-start gap-3 rounded-lg px-3 py-3 text-left transition hover:bg-white/[0.06]"
                                    :class="String(priorityId) === String(item.id) ? 'bg-indigo-500/10' : ''"
                                >
                                    <span
                                        class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border"
                                        :style="{
                                            color: item.color,
                                            borderColor: item.color + '55',
                                            backgroundColor: item.color + '15'
                                        }"
                                    >
                                        <i :class="item.icon" aria-hidden="true"></i>
                                    </span>

                                    <span class="min-w-0 flex-1">
                                        <span class="flex flex-wrap items-center gap-2">
                                            <span class="text-sm font-medium text-gray-200" x-text="item.name"></span>

                                            <span
                                                x-show="item.isDefault"
                                                class="rounded-md border border-amber-500/20 bg-amber-500/10 px-1.5 py-0.5 text-[10px] font-semibold text-amber-300"
                                            >
                                                DEFAULT
                                            </span>
                                        </span>

                                        <span class="mt-1 block font-mono text-xs text-gray-500" x-text="item.code + ' · ID: ' + item.id"></span>

                                        <span class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-400">
                                            <span x-text="'Level ' + item.level"></span>
                                            <span x-show="item.responseTime !== null" x-text="'Response: ' + item.responseTime + ' min'"></span>
                                            <span x-show="item.resolutionTime !== null" x-text="'Resolution: ' + item.resolutionTime + ' min'"></span>
                                        </span>
                                    </span>

                                    <i
                                        x-show="String(priorityId) === String(item.id)"
                                        class="fa-solid fa-check mt-2 text-xs text-indigo-400"
                                        aria-hidden="true"
                                    ></i>
                                </button>
                            </template>

                            <p x-show="priorities.length === 0" class="px-3 py-5 text-center text-sm text-gray-500">
                                No active priorities available.
                            </p>
                        </div>

                        @error('priority_id')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Selected priority preview --}}
                <div
                    x-show="selectedPriority"
                    x-cloak
                    class="mx-5 mb-5 rounded-xl border p-4 sm:mx-6 sm:mb-6"
                    :style="{
                        borderColor: (selectedPriority?.color ?? '#818cf8') + '40',
                        backgroundColor: (selectedPriority?.color ?? '#818cf8') + '0D'
                    }"
                >
                    <div class="flex items-start gap-3">
                        <i
                            class="fa-solid fa-stopwatch mt-0.5"
                            :style="{ color: selectedPriority?.color ?? '#818cf8' }"
                            aria-hidden="true"
                        ></i>

                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-white">
                                Priority service targets
                            </p>

                            <div class="mt-2 flex flex-wrap gap-x-5 gap-y-2 text-xs text-gray-400">
                                <span x-text="'Response: ' + (selectedPriority?.responseTime !== null && selectedPriority?.responseTime !== undefined ? selectedPriority.responseTime + ' min' : 'Not configured')"></span>
                                <span x-text="'Resolution: ' + (selectedPriority?.resolutionTime !== null && selectedPriority?.resolutionTime !== undefined ? selectedPriority.resolutionTime + ' min' : 'Not configured')"></span>
                                <span x-text="'Escalation: ' + (selectedPriority?.escalationTime !== null && selectedPriority?.escalationTime !== undefined ? selectedPriority.escalationTime + ' min' : 'Not configured')"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Workflow --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                        <i class="fa-solid fa-route" aria-hidden="true"></i>
                    </div>

                    <div>
                        <h2 class="font-semibold text-white">Assignment and workflow</h2>
                        <p class="mt-0.5 text-xs text-gray-500">
                            Initial values are managed by the ticket system.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 sm:p-6">

                    {{-- Initial status --}}
                    <div class="rounded-xl border border-white/[0.08] bg-white/[0.02] p-4">
                        <p class="mb-3 text-xs font-medium uppercase tracking-wider text-gray-500">
                            Initial status
                        </p>

                        @if ($initialStatus)
                            <div class="flex items-center gap-3">
                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border"
                                    style="color: {{ $statusColor }}; border-color: {{ $statusColor }}55; background-color: {{ $statusColor }}15;"
                                >
                                    <i class="{{ $initialStatus->icon ?: 'fa-solid fa-circle-dot' }}" aria-hidden="true"></i>
                                </span>

                                <div class="min-w-0">
                                    <p class="font-medium text-white">
                                        {{ $initialStatus->name }}
                                    </p>

                                    <p class="mt-1 font-mono text-xs text-gray-500">
                                        {{ $initialStatus->code }} · ID: {{ $initialStatus->id }}
                                    </p>
                                </div>

                                <span class="ml-auto shrink-0 rounded-md border border-sky-500/20 bg-sky-500/10 px-2 py-1 text-[10px] font-semibold uppercase text-sky-300">
                                    Initial
                                </span>
                            </div>
                        @else
                            <p class="text-sm text-amber-300">
                                No active initial status is configured.
                            </p>
                        @endif

                        <p class="mt-3 text-xs leading-5 text-gray-500">
                            The initial status is assigned by the server when the ticket is created.
                        </p>
                    </div>

                    {{-- Agent assignment --}}
                    <div class="rounded-xl border border-white/[0.08] bg-white/[0.02] p-4">
                        <p class="mb-3 text-xs font-medium uppercase tracking-wider text-gray-500">
                            Assigned agent
                        </p>

                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-white/10 bg-white/[0.04] text-gray-400">
                                <i class="fa-solid fa-user-clock" aria-hidden="true"></i>
                            </span>

                            <div>
                                <p class="font-medium text-white">Unassigned</p>
                                <p class="mt-1 text-xs text-gray-500">
                                    An agent can be assigned later.
                                </p>
                            </div>
                        </div>

                        <p class="mt-3 text-xs leading-5 text-gray-500">
                            You do not need to choose an agent to submit your request.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Actions --}}
            <div class="flex flex-col-reverse gap-3 border-t border-white/[0.08] pt-6 sm:flex-row sm:items-center sm:justify-end">
                <a
                    href="{{ route('ticket.list') }}"
                    class="inline-flex h-11 items-center justify-center rounded-xl border border-white/10 px-5 text-sm font-medium text-gray-300 transition hover:bg-white/[0.05] hover:text-white"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    @disabled(! $initialStatus || $categories->isEmpty() || $priorities->isEmpty())
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 text-sm font-semibold text-white shadow-lg shadow-indigo-950/30 transition hover:bg-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/30 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-indigo-600"
                >
                    <i class="fa-solid fa-paper-plane text-xs" aria-hidden="true"></i>
                    Submit ticket
                </button>
            </div>
        </form>

        <p class="mt-6 text-center text-xs text-gray-600">
            <i class="fa-solid fa-shield-halved mr-1" aria-hidden="true"></i>
            Your ticket will be associated with your account.
        </p>
    </div>
</div>
@endsection
