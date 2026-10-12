
@extends('layouts.base')

@section('title', 'Create Ticket Priority | Iris')

@section('content')
<div
    class="min-h-screen px-4 py-8 sm:px-6 lg:px-8"
    x-data="{
        priorityName: @js(old('name', 'High')),
        priorityCode: @js(old('code', 'HIGH')),
        priorityLevel: @js(old('level', 3)),
        priorityColor: @js(old('color', '#6366f1')),
        priorityIcon: @js(old('icon', 'fa-solid fa-bolt'))
    }"
>
    <div class="mx-auto max-w-5xl">

        {{-- Breadcrumb --}}
        <nav class="mb-6 flex items-center gap-2 text-sm" aria-label="Breadcrumb">
            <a
                href="{{ route('ticket.priority.list') }}"
                class="text-gray-400 transition hover:text-white"
            >
                Ticket priorities
            </a>

            <i class="fa-solid fa-chevron-right text-[10px] text-gray-600" aria-hidden="true"></i>

            <span class="font-medium text-indigo-400" aria-current="page">
                Create priority
            </span>
        </nav>

        {{-- Page header --}}
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-indigo-500/20 bg-indigo-500/10">
                    <i class="fa-solid fa-gauge-high text-xl text-indigo-400" aria-hidden="true"></i>
                </div>

                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        Create ticket priority
                    </h1>

                    <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-400">
                        Define the priority level, visual appearance, and service-level targets for support tickets.
                    </p>
                </div>
            </div>

            <a
                href="{{ route('ticket.priority.list') }}"
                class="inline-flex items-center justify-center gap-2 self-start rounded-xl border border-white/10 bg-white/[0.03] px-4 py-2.5 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:bg-white/[0.06] hover:text-white sm:self-center"
            >
                <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                Back to priorities
            </a>
        </div>

        {{-- Validation summary --}}
        @if ($errors->any())
            <div
                role="alert"
                class="mb-6 flex gap-3 rounded-xl border border-red-500/20 bg-red-500/10 p-4"
            >
                <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-400" aria-hidden="true"></i>

                <div>
                    <h2 class="text-sm font-semibold text-red-300">
                        Unable to create priority
                    </h2>

                    <p class="mt-1 text-sm text-red-300/80">
                        Please review the fields highlighted below and try again.
                    </p>
                </div>
            </div>
        @endif

        <form
            action="{{ route('ticket.priority.create.form') }}"
            method="POST"
            class="space-y-6"
        >
            @csrf

            {{-- General information --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-400">
                        <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                    </div>

                    <div>
                        <h2 class="font-semibold text-white">General information</h2>
                        <p class="mt-0.5 text-xs text-gray-500">
                            Identify and classify this priority.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 sm:p-6">

                    {{-- Name --}}
                    <div>
                        <label for="name" class="mb-2 block text-sm font-medium text-gray-200">
                            Priority name <span class="text-red-400">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name') }}"
                            maxlength="100"
                            required
                            autofocus
                            placeholder="e.g. High"
                            x-model="priorityName"
                            @class([
                                'w-full rounded-xl border bg-white/[0.03] px-4 py-3 text-sm text-white placeholder-gray-600 transition',
                                'focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10',
                                'border-red-500/50' => $errors->has('name'),
                                'border-white/10' => ! $errors->has('name'),
                            ])
                            @if ($errors->has('name'))
                                aria-invalid="true"
                                aria-describedby="name-error"
                            @endif
                        />

                        @error('name')
                            <p id="name-error" class="mt-2 text-xs text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Code --}}
                    <div>
                        <label for="code" class="mb-2 block text-sm font-medium text-gray-200">
                            Priority code <span class="text-red-400">*</span>
                        </label>

                        <input
                            type="text"
                            name="code"
                            id="code"
                            value="{{ old('code') }}"
                            maxlength="50"
                            required
                            placeholder="e.g. HIGH"
                            autocomplete="off"
                            x-model="priorityCode"
                            @input="$el.value = $el.value.toUpperCase(); priorityCode = $el.value"
                            @class([
                                'w-full rounded-xl border bg-white/[0.03] px-4 py-3 font-mono text-sm text-white placeholder-gray-600 transition',
                                'focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10',
                                'border-red-500/50' => $errors->has('code'),
                                'border-white/10' => ! $errors->has('code'),
                            ])
                            @if ($errors->has('code'))
                                aria-invalid="true"
                                aria-describedby="code-error"
                            @endif
                        />

                        <p class="mt-1.5 text-xs text-gray-500">
                            Letters, numbers, dots, underscores, and hyphens.
                        </p>

                        @error('code')
                            <p id="code-error" class="mt-2 text-xs text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Level --}}
                    <div>
                        <label for="level" class="mb-2 block text-sm font-medium text-gray-200">
                            Priority level <span class="text-red-400">*</span>
                        </label>

                        <input
                            type="number"
                            name="level"
                            id="level"
                            value="{{ old('level', 3) }}"
                            min="1"
                            max="32767"
                            step="1"
                            required
                            placeholder="e.g. 3"
                            x-model="priorityLevel"
                            @class([
                                'w-full rounded-xl border bg-white/[0.03] px-4 py-3 text-sm text-white placeholder-gray-600 transition',
                                'focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10',
                                'border-red-500/50' => $errors->has('level'),
                                'border-white/10' => ! $errors->has('level'),
                            ])
                            @if ($errors->has('level'))
                                aria-invalid="true"
                                aria-describedby="level-error"
                            @endif
                        />

                        <p class="mt-1.5 text-xs text-gray-500">
                            Must be unique. Define the ordering convention in your workflow.
                        </p>

                        @error('level')
                            <p id="level-error" class="mt-2 text-xs text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="mb-2 block text-sm font-medium text-gray-200">
                            Description
                            <span class="font-normal text-gray-500">(optional)</span>
                        </label>

                        <textarea
                            name="description"
                            id="description"
                            rows="3"
                            maxlength="500"
                            placeholder="Describe when this priority should be used..."
                            @class([
                                'w-full resize-y rounded-xl border bg-white/[0.03] px-4 py-3 text-sm text-white placeholder-gray-600 transition',
                                'focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10',
                                'border-red-500/50' => $errors->has('description'),
                                'border-white/10' => ! $errors->has('description'),
                            ])
                            @if ($errors->has('description'))
                                aria-invalid="true"
                                aria-describedby="description-error"
                            @endif
                        >{{ old('description') }}</textarea>

                        @error('description')
                            <p id="description-error" class="mt-2 text-xs text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- Appearance --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-500/10 text-violet-400">
                        <i class="fa-solid fa-palette" aria-hidden="true"></i>
                    </div>

                    <div>
                        <h2 class="font-semibold text-white">Appearance</h2>
                        <p class="mt-0.5 text-xs text-gray-500">
                            Customize how this priority is displayed.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 p-5 sm:grid-cols-2 sm:p-6">

                    {{-- Color --}}
                    <div>
                        <label for="color" class="mb-2 block text-sm font-medium text-gray-200">
                            Display color
                        </label>

                        <div class="flex gap-3">
                            <input
                                type="color"
                                id="color_picker"
                                value="{{ old('color', '#6366f1') }}"
                                aria-label="Choose priority color"
                                class="h-12 w-14 shrink-0 cursor-pointer rounded-lg border border-white/10 bg-white/[0.03] p-1"
                                @input="$refs.colorInput.value = $event.target.value; priorityColor = $event.target.value"
                            />

                            <input
                                type="text"
                                name="color"
                                id="color"
                                x-ref="colorInput"
                                value="{{ old('color', '#6366f1') }}"
                                maxlength="30"
                                placeholder="#6366f1"
                                x-model="priorityColor"
                                @input="if (/^#[0-9a-fA-F]{6}$/.test($event.target.value)) $refs.colorPicker.value = $event.target.value"
                                x-ref="colorInput"
                                @class([
                                    'min-w-0 flex-1 rounded-xl border bg-white/[0.03] px-4 py-3 font-mono text-sm text-white placeholder-gray-600 transition',
                                    'focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10',
                                    'border-red-500/50' => $errors->has('color'),
                                    'border-white/10' => ! $errors->has('color'),
                                ])
                            />
                        </div>

                        @error('color')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Icon --}}
                    <div>
                        <label for="icon" class="mb-2 block text-sm font-medium text-gray-200">
                            Font Awesome icon
                        </label>

                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">
                                <i class="fa-solid fa-icons" aria-hidden="true"></i>
                            </div>

                            <input
                                type="text"
                                name="icon"
                                id="icon"
                                value="{{ old('icon', 'fa-solid fa-bolt') }}"
                                maxlength="100"
                                placeholder="fa-solid fa-bolt"
                                x-model="priorityIcon"
                                @class([
                                    'w-full rounded-xl border bg-white/[0.03] py-3 pl-11 pr-4 font-mono text-sm text-white placeholder-gray-600 transition',
                                    'focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10',
                                    'border-red-500/50' => $errors->has('icon'),
                                    'border-white/10' => ! $errors->has('icon'),
                                ])
                            />
                        </div>

                        <p class="mt-1.5 text-xs text-gray-500">
                            Example: <code class="text-gray-400">fa-solid fa-bolt</code>
                        </p>

                        @error('icon')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Preview --}}
                    <div class="sm:col-span-2">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Live preview
                        </p>

                        <div class="flex items-center justify-between gap-4 rounded-xl border border-white/10 bg-black/20 p-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/[0.03]"
                                    :style="{ color: /^#[0-9a-fA-F]{6}$/.test(priorityColor) ? priorityColor : '#818cf8' }"
                                >
                                    <i :class="priorityIcon || 'fa-solid fa-gauge-high'" aria-hidden="true"></i>
                                </div>

                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-white" x-text="priorityName || 'Priority name'"></p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Code:
                                        <span class="font-mono text-gray-400" x-text="priorityCode || 'CODE'"></span>
                                    </p>
                                </div>
                            </div>

                            <span
                                class="shrink-0 rounded-lg border px-3 py-1.5 text-xs font-semibold"
                                :style="{
                                    color: /^#[0-9a-fA-F]{6}$/.test(priorityColor) ? priorityColor : '#818cf8',
                                    borderColor: (/^#[0-9a-fA-F]{6}$/.test(priorityColor) ? priorityColor : '#818cf8') + '55',
                                    backgroundColor: (/^#[0-9a-fA-F]{6}$/.test(priorityColor) ? priorityColor : '#818cf8') + '15'
                                }"
                            >
                                Level <span x-text="priorityLevel || '—'"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Service-level targets --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500/10 text-amber-400">
                        <i class="fa-solid fa-stopwatch" aria-hidden="true"></i>
                    </div>

                    <div>
                        <h2 class="font-semibold text-white">Service-level targets</h2>
                        <p class="mt-0.5 text-xs text-gray-500">
                            Configure the time targets associated with this priority.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-3 sm:p-6">

                    {{-- Response time --}}
                    <div>
                        <label for="response_time_minutes" class="mb-2 block text-sm font-medium text-gray-200">
                            Response time
                        </label>

                        <div class="relative">
                            <input
                                type="number"
                                name="response_time_minutes"
                                id="response_time_minutes"
                                value="{{ old('response_time_minutes') }}"
                                min="1"
                                max="2147483647"
                                step="1"
                                placeholder="e.g. 30"
                                @class([
                                    'w-full rounded-xl border bg-white/[0.03] py-3 pl-4 pr-20 text-sm text-white placeholder-gray-600 transition',
                                    'focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10',
                                    'border-red-500/50' => $errors->has('response_time_minutes'),
                                    'border-white/10' => ! $errors->has('response_time_minutes'),
                                ])
                            />

                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-xs text-gray-500">
                                minutes
                            </span>
                        </div>

                        @error('response_time_minutes')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Resolution time --}}
                    <div>
                        <label for="resolution_time_minutes" class="mb-2 block text-sm font-medium text-gray-200">
                            Resolution time
                        </label>

                        <div class="relative">
                            <input
                                type="number"
                                name="resolution_time_minutes"
                                id="resolution_time_minutes"
                                value="{{ old('resolution_time_minutes') }}"
                                min="1"
                                max="2147483647"
                                step="1"
                                placeholder="e.g. 240"
                                @class([
                                    'w-full rounded-xl border bg-white/[0.03] py-3 pl-4 pr-20 text-sm text-white placeholder-gray-600 transition',
                                    'focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10',
                                    'border-red-500/50' => $errors->has('resolution_time_minutes'),
                                    'border-white/10' => ! $errors->has('resolution_time_minutes'),
                                ])
                            />

                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-xs text-gray-500">
                                minutes
                            </span>
                        </div>

                        @error('resolution_time_minutes')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Escalation time --}}
                    <div>
                        <label for="escalation_time_minutes" class="mb-2 block text-sm font-medium text-gray-200">
                            Escalation time
                        </label>

                        <div class="relative">
                            <input
                                type="number"
                                name="escalation_time_minutes"
                                id="escalation_time_minutes"
                                value="{{ old('escalation_time_minutes') }}"
                                min="1"
                                max="2147483647"
                                step="1"
                                placeholder="e.g. 120"
                                @class([
                                    'w-full rounded-xl border bg-white/[0.03] py-3 pl-4 pr-20 text-sm text-white placeholder-gray-600 transition',
                                    'focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10',
                                    'border-red-500/50' => $errors->has('escalation_time_minutes'),
                                    'border-white/10' => ! $errors->has('escalation_time_minutes'),
                                ])
                            />

                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-xs text-gray-500">
                                minutes
                            </span>
                        </div>

                        @error('escalation_time_minutes')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- Status and defaults --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                        <i class="fa-solid fa-toggle-on" aria-hidden="true"></i>
                    </div>

                    <div>
                        <h2 class="font-semibold text-white">Status and defaults</h2>
                        <p class="mt-0.5 text-xs text-gray-500">
                            Choose how this priority will be used.
                        </p>
                    </div>
                </div>

                <div class="space-y-4 p-5 sm:p-6">

                    {{-- Default --}}
                    <label
                        for="is_default"
                        class="flex cursor-pointer items-start gap-3 rounded-xl border border-white/[0.08] bg-white/[0.02] p-4 transition hover:border-white/15 hover:bg-white/[0.04]"
                    >
                        <input
                            type="hidden"
                            name="is_default"
                            value="0"
                        />

                        <input
                            type="checkbox"
                            name="is_default"
                            id="is_default"
                            value="1"
                            @checked((bool) old('is_default', false))
                            class="mt-0.5 h-4 w-4 rounded border-gray-600 bg-gray-800 text-indigo-500 focus:ring-2 focus:ring-indigo-500/30 focus:ring-offset-0"
                        />

                        <span class="min-w-0">
                            <span class="block text-sm font-medium text-gray-200">
                                Set as default priority
                            </span>

                            <span class="mt-1 block text-xs leading-5 text-gray-500">
                                Use this priority as the default when creating tickets without an explicitly selected priority.
                            </span>
                        </span>
                    </label>

                    {{-- Active --}}
                    <label
                        for="is_active"
                        class="flex cursor-pointer items-start gap-3 rounded-xl border border-white/[0.08] bg-white/[0.02] p-4 transition hover:border-white/15 hover:bg-white/[0.04]"
                    >
                        <input
                            type="hidden"
                            name="is_active"
                            value="0"
                        />

                        <input
                            type="checkbox"
                            name="is_active"
                            id="is_active"
                            value="1"
                            @checked((bool) old('is_active', true))
                            class="mt-0.5 h-4 w-4 rounded border-gray-600 bg-gray-800 text-indigo-500 focus:ring-2 focus:ring-indigo-500/30 focus:ring-offset-0"
                        />

                        <span class="min-w-0">
                            <span class="block text-sm font-medium text-gray-200">
                                Priority is active
                            </span>

                            <span class="mt-1 block text-xs leading-5 text-gray-500">
                                Active priorities can be selected when creating or managing support tickets.
                            </span>
                        </span>

                        <span class="ml-auto shrink-0 rounded-md border border-emerald-500/20 bg-emerald-500/10 px-2 py-1 text-[10px] font-semibold uppercase tracking-wider text-emerald-400">
                            Active
                        </span>
                    </label>

                    @error('is_default')
                        <p class="text-xs text-red-400">{{ $message }}</p>
                    @enderror

                    @error('is_active')
                        <p class="text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            {{-- Form actions --}}
            <div class="flex flex-col-reverse gap-3 border-t border-white/[0.08] pt-6 sm:flex-row sm:items-center sm:justify-end">
                <a
                    href="{{ route('ticket.priority.list') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-white/10 px-5 py-3 text-sm font-medium text-gray-300 transition hover:bg-white/[0.05] hover:text-white"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-950/30 transition hover:bg-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/30 active:scale-[0.99]"
                >
                    <i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>
                    Create priority
                </button>
            </div>
        </form>

        <p class="mt-6 text-center text-xs text-gray-600">
            <i class="fa-solid fa-shield-halved mr-1" aria-hidden="true"></i>
            Changes are subject to your assigned permissions and validation rules.
        </p>
    </div>
</div>
@endsection
