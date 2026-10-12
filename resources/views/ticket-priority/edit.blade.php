@extends('layouts.base')

@section('title', 'Edit Ticket Priority | Iris')

@section('content')
@php
    $storedColor = old('color', $priority->color);
    $initialColor = preg_match('/^#[0-9a-fA-F]{6}$/', is_string($storedColor) ? $storedColor : '')
        ? $storedColor
        : '#818cf8';

    $field = 'block w-full rounded-xl border bg-white/[0.03] px-4 py-3 text-sm text-white placeholder-gray-600 transition focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10';
    $label = 'mb-2 block text-sm font-medium text-gray-200';
@endphp

<div
    class="relative isolate min-h-screen px-4 py-8 text-gray-100 [color-scheme:dark] sm:px-6 lg:px-8"
    x-data="{
        priorityName: @js(old('name', $priority->name)),
        priorityCode: @js(old('code', $priority->code)),
        priorityLevel: @js(old('level', $priority->level)),
        priorityColor: @js($initialColor),
        colorValue: @js($storedColor ?? ''),
        priorityIcon: @js(old('icon', $priority->icon ?? 'fa-solid fa-flag'))
    }"
>
    <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-[radial-gradient(ellipse_at_50%_0%,rgba(99,102,241,.16),transparent_70%)]" aria-hidden="true"></div>

    <div class="mx-auto max-w-5xl">
        {{-- Breadcrumb --}}
        <nav class="mb-4 flex flex-wrap items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="transition hover:text-gray-300" aria-label="Dashboard">
                <i class="fa-solid fa-house text-xs" aria-hidden="true"></i>
            </a>
            <span aria-hidden="true">/</span>
            <span>Ticket management</span>
            <span aria-hidden="true">/</span>
            <a href="{{ route('ticket.priority.list') }}" class="transition hover:text-gray-300">Priorities</a>
            <span aria-hidden="true">/</span>
            <span class="text-indigo-400" aria-current="page">Edit</span>
        </nav>

        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex min-w-0 items-start gap-4">
                <div
                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border text-xl"
                    :style="{ color: priorityColor, borderColor: priorityColor + '40', backgroundColor: priorityColor + '18' }"
                >
                    <i :class="priorityIcon || 'fa-solid fa-flag'" aria-hidden="true"></i>
                </div>

                <div class="min-w-0">
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <span class="rounded-md border border-white/10 bg-white/[0.04] px-2 py-1 font-mono text-[11px] text-gray-400">
                            ID: {{ $priority->id }}
                        </span>
                        <span class="rounded-md border border-white/10 bg-white/[0.04] px-2 py-1 text-[11px] text-gray-400">
                            Version {{ $priority->version }}
                        </span>
                    </div>

                    <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Edit ticket priority</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                        Update the priority classification, visual appearance, and service-level targets.
                    </p>
                </div>
            </div>

            <a
                href="{{ route('ticket.priority.show-details', $priority->id) }}"
                class="inline-flex h-10 shrink-0 items-center justify-center gap-2 self-start rounded-xl border border-white/10 px-4 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:bg-white/[0.05] hover:text-white"
            >
                <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                Back to details
            </a>
        </div>

        {{-- Validation summary --}}
        @if ($errors->any())
            <div role="alert" class="mb-6 flex items-start gap-3 rounded-xl border border-red-500/25 bg-red-500/10 p-4 text-sm text-red-300">
                <i class="fa-solid fa-circle-exclamation mt-0.5" aria-hidden="true"></i>
                <div>
                    <p class="font-semibold">Unable to update this priority</p>
                    <p class="mt-1 text-red-300/80">Review the highlighted fields and try again.</p>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-xs text-red-300/80">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div role="alert" class="mb-6 flex items-center gap-3 rounded-xl border border-red-500/25 bg-red-500/10 p-4 text-sm text-red-300">
                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <form action="{{ route('ticket.priority.update', $priority->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="version" value="{{ $priority->version }}">

            {{-- General information --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10 backdrop-blur-xl">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-300">
                        <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-white">General information</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Update the name, unique code, and priority level.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 sm:p-6">
                    <div>
                        <label for="name" class="{{ $label }}">Priority name <span class="text-red-400">*</span></label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name', $priority->name) }}"
                            maxlength="100"
                            required
                            autofocus
                            placeholder="e.g. High"
                            x-model="priorityName"
                            @class([
                                $field,
                                'border-red-500/50' => $errors->has('name'),
                                'border-white/10' => ! $errors->has('name'),
                            ])
                            @if ($errors->has('name')) aria-invalid="true" aria-describedby="name-error" @endif
                        >
                        @error('name')
                            <p id="name-error" class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="code" class="{{ $label }}">Priority code <span class="text-red-400">*</span></label>
                        <input
                            type="text"
                            name="code"
                            id="code"
                            value="{{ old('code', $priority->code) }}"
                            maxlength="50"
                            required
                            autocomplete="off"
                            placeholder="e.g. HIGH"
                            x-model="priorityCode"
                            @input="$el.value = $el.value.toUpperCase(); priorityCode = $el.value"
                            @class([
                                $field . ' font-mono uppercase',
                                'border-red-500/50' => $errors->has('code'),
                                'border-white/10' => ! $errors->has('code'),
                            ])
                            @if ($errors->has('code')) aria-invalid="true" aria-describedby="code-error" @endif
                        >
                        <p class="mt-1.5 text-xs text-gray-500">Letters, numbers, dots, underscores, and hyphens.</p>
                        @error('code')
                            <p id="code-error" class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="level" class="{{ $label }}">Priority level <span class="text-red-400">*</span></label>
                        <input
                            type="number"
                            name="level"
                            id="level"
                            value="{{ old('level', $priority->level) }}"
                            min="1"
                            max="32767"
                            step="1"
                            required
                            placeholder="e.g. 3"
                            x-model="priorityLevel"
                            @class([
                                $field,
                                'border-red-500/50' => $errors->has('level'),
                                'border-white/10' => ! $errors->has('level'),
                            ])
                            @if ($errors->has('level')) aria-invalid="true" aria-describedby="level-error" @endif
                        >
                        <p class="mt-1.5 text-xs text-gray-500">Each priority must have a unique level.</p>
                        @error('level')
                            <p id="level-error" class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="{{ $label }}">Description <span class="font-normal text-gray-500">(optional)</span></label>
                        <textarea
                            name="description"
                            id="description"
                            rows="3"
                            maxlength="500"
                            placeholder="Describe when this priority should be used..."
                            @class([
                                $field . ' resize-y',
                                'border-red-500/50' => $errors->has('description'),
                                'border-white/10' => ! $errors->has('description'),
                            ])
                            @if ($errors->has('description')) aria-invalid="true" aria-describedby="description-error" @endif
                        >{{ old('description', $priority->description) }}</textarea>
                        @error('description')
                            <p id="description-error" class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- Appearance --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10 backdrop-blur-xl">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-500/10 text-violet-300">
                        <i class="fa-solid fa-palette" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-white">Appearance</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Customize the color and icon displayed for this priority.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 p-5 sm:grid-cols-2 sm:p-6">
                    <div>
                        <label for="color" class="{{ $label }}">Display color</label>
                        <div class="flex gap-3">
                            <input
                                type="color"
                                id="color_picker"
                                x-ref="colorPicker"
                                value="{{ $initialColor }}"
                                aria-label="Choose priority color"
                                class="h-11 w-14 shrink-0 cursor-pointer rounded-lg border border-white/10 bg-white/[0.03] p-1"
                                @input="colorValue = $event.target.value; priorityColor = $event.target.value; $refs.colorText.value = $event.target.value"
                            >
                            <input
                                type="text"
                                name="color"
                                id="color"
                                x-ref="colorText"
                                value="{{ $storedColor }}"
                                maxlength="30"
                                placeholder="#6366f1"
                                x-model="colorValue"
                                @input="if (/^#[0-9a-fA-F]{6}$/.test(colorValue)) { priorityColor = colorValue; $refs.colorPicker.value = colorValue }"
                                @class([
                                    $field . ' min-w-0 font-mono',
                                    'border-red-500/50' => $errors->has('color'),
                                    'border-white/10' => ! $errors->has('color'),
                                ])
                                @if ($errors->has('color')) aria-invalid="true" aria-describedby="color-error" @endif
                            >
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500">Use a hex value such as #6366f1 for the color picker preview.</p>
                        @error('color')
                            <p id="color-error" class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="icon" class="{{ $label }}">Font Awesome icon</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">
                                <i class="fa-solid fa-icons" aria-hidden="true"></i>
                            </div>
                            <input
                                type="text"
                                name="icon"
                                id="icon"
                                value="{{ old('icon', $priority->icon) }}"
                                maxlength="100"
                                placeholder="fa-solid fa-flag"
                                x-model="priorityIcon"
                                @class([
                                    $field . ' pl-11 font-mono',
                                    'border-red-500/50' => $errors->has('icon'),
                                    'border-white/10' => ! $errors->has('icon'),
                                ])
                                @if ($errors->has('icon')) aria-invalid="true" aria-describedby="icon-error" @endif
                            >
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500">Example: <code class="text-gray-400">fa-solid fa-bolt</code></p>
                        @error('icon')
                            <p id="icon-error" class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Live preview --}}
                    <div class="sm:col-span-2">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Live preview</p>
                        <div class="flex items-center justify-between gap-4 rounded-xl border border-white/10 bg-black/20 p-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border"
                                    :style="{ color: priorityColor, borderColor: priorityColor + '40', backgroundColor: priorityColor + '18' }"
                                >
                                    <i :class="priorityIcon || 'fa-solid fa-flag'" aria-hidden="true"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-white" x-text="priorityName || 'Priority name'"></p>
                                    <p class="mt-1 text-xs text-gray-500">Code: <span class="font-mono text-gray-300" x-text="priorityCode || 'CODE'"></span></p>
                                </div>
                            </div>
                            <span
                                class="shrink-0 rounded-lg border px-3 py-1.5 text-xs font-semibold"
                                :style="{ color: priorityColor, borderColor: priorityColor + '55', backgroundColor: priorityColor + '15' }"
                            >
                                Level <span x-text="priorityLevel || '—'"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- SLA targets --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10 backdrop-blur-xl">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-300">
                        <i class="fa-solid fa-stopwatch" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-white">Service-level targets</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Update the time targets associated with this priority.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-3 sm:p-6">
                    @foreach ([
                        'response_time_minutes' => ['label' => 'Response time', 'value' => $priority->response_time_minutes, 'placeholder' => 'e.g. 30'],
                        'resolution_time_minutes' => ['label' => 'Resolution time', 'value' => $priority->resolution_time_minutes, 'placeholder' => 'e.g. 240'],
                        'escalation_time_minutes' => ['label' => 'Escalation time', 'value' => $priority->escalation_time_minutes, 'placeholder' => 'e.g. 120'],
                    ] as $fieldName => $config)
                        <div>
                            <label for="{{ $fieldName }}" class="{{ $label }}">{{ $config['label'] }}</label>
                            <div class="relative">
                                <input
                                    type="number"
                                    name="{{ $fieldName }}"
                                    id="{{ $fieldName }}"
                                    value="{{ old($fieldName, $config['value']) }}"
                                    min="1"
                                    max="2147483647"
                                    step="1"
                                    placeholder="{{ $config['placeholder'] }}"
                                    @class([
                                        $field . ' pr-20',
                                        'border-red-500/50' => $errors->has($fieldName),
                                        'border-white/10' => ! $errors->has($fieldName),
                                    ])
                                    @if ($errors->has($fieldName)) aria-invalid="true" aria-describedby="{{ $fieldName }}-error" @endif
                                >
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-xs text-gray-500">minutes</span>
                            </div>
                            @error($fieldName)
                                <p id="{{ $fieldName }}-error" class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Status and defaults --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10 backdrop-blur-xl">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-300">
                        <i class="fa-solid fa-toggle-on" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-white">Status and defaults</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Choose how this priority should be used.</p>
                    </div>
                </div>

                <div class="space-y-4 p-5 sm:p-6">
                    <label for="is_default" class="flex cursor-pointer items-start gap-3 rounded-xl border border-white/[0.08] bg-white/[0.02] p-4 transition hover:border-white/15 hover:bg-white/[0.04]">
                        <input type="hidden" name="is_default" value="0">
                        <input
                            type="checkbox"
                            name="is_default"
                            id="is_default"
                            value="1"
                            @checked((bool) old('is_default', $priority->is_default))
                            class="mt-0.5 h-4 w-4 rounded border-gray-600 bg-gray-800 text-indigo-500 focus:ring-2 focus:ring-indigo-500/30 focus:ring-offset-0"
                        >
                        <span class="min-w-0">
                            <span class="block text-sm font-medium text-gray-200">Set as default priority</span>
                            <span class="mt-1 block text-xs leading-5 text-gray-500">Use this priority when a ticket is created without an explicitly selected priority.</span>
                        </span>
                    </label>

                    <label for="is_active" class="flex cursor-pointer items-start gap-3 rounded-xl border border-white/[0.08] bg-white/[0.02] p-4 transition hover:border-white/15 hover:bg-white/[0.04]">
                        <input type="hidden" name="is_active" value="0">
                        <input
                            type="checkbox"
                            name="is_active"
                            id="is_active"
                            value="1"
                            @checked((bool) old('is_active', $priority->is_active))
                            class="mt-0.5 h-4 w-4 rounded border-gray-600 bg-gray-800 text-indigo-500 focus:ring-2 focus:ring-indigo-500/30 focus:ring-offset-0"
                        >
                        <span class="min-w-0">
                            <span class="block text-sm font-medium text-gray-200">Priority is active</span>
                            <span class="mt-1 block text-xs leading-5 text-gray-500">Active priorities can be selected when creating or managing tickets.</span>
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

            {{-- Actions --}}
            <div class="flex flex-col-reverse gap-3 border-t border-white/[0.08] pt-6 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-gray-500">Changes are saved immediately after successful validation.</p>
                <div class="flex flex-col-reverse gap-3 sm:flex-row">
                    <a
                        href="{{ route('ticket.priority.show-details', $priority->id) }}"
                        class="inline-flex h-11 items-center justify-center rounded-xl border border-white/10 px-5 text-sm font-medium text-gray-300 transition hover:bg-white/[0.05] hover:text-white"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 text-sm font-semibold text-white shadow-lg shadow-indigo-950/30 transition hover:bg-indigo-500 focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/30 active:scale-[0.99]"
                    >
                        <i class="fa-solid fa-floppy-disk text-xs" aria-hidden="true"></i>
                        Save changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
