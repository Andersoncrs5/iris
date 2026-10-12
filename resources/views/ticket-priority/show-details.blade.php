@extends('layouts.base')

@section('title', 'Priority Details | Iris')

@section('content')
@php
    $safeColor = fn (?string $color) => preg_match('/^#[0-9a-fA-F]{6}$/', $color ?? '')
        ? $color
        : '#818cf8';

    $priorityColor = $safeColor($priority->color);

    $formatMinutes = function (?int $minutes): string {
        if ($minutes === null) {
            return 'Not configured';
        }

        if ($minutes < 60) {
            return $minutes . ' ' . \Illuminate\Support\Str::plural('minute', $minutes);
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours < 24) {
            return $hours . ' ' . \Illuminate\Support\Str::plural('hour', $hours)
                . ($remainingMinutes > 0 ? ' ' . $remainingMinutes . ' min' : '');
        }

        $days = intdiv($hours, 24);
        $remainingHours = $hours % 24;

        return $days . ' ' . \Illuminate\Support\Str::plural('day', $days)
            . ($remainingHours > 0 ? ' ' . $remainingHours . ' h' : '')
            . ($remainingMinutes > 0 ? ' ' . $remainingMinutes . ' min' : '');
    };

    $detailLabel = 'text-xs font-medium uppercase tracking-wider text-gray-500';
    $detailValue = 'mt-2 break-words text-sm font-medium text-gray-100';
@endphp

<div class="relative isolate min-h-screen px-4 py-8 text-gray-100 [color-scheme:dark] sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-[radial-gradient(ellipse_at_50%_0%,rgba(99,102,241,.16),transparent_70%)]" aria-hidden="true"></div>

    <div class="mx-auto max-w-6xl">
        {{-- Breadcrumb --}}
        <nav class="mb-4 flex items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="transition hover:text-gray-300" aria-label="Dashboard">
                <i class="fa-solid fa-house text-xs" aria-hidden="true"></i>
            </a>
            <span aria-hidden="true">/</span>
            <span>Ticket management</span>
            <span aria-hidden="true">/</span>
            <a href="{{ route('ticket.priority.list') }}" class="transition hover:text-gray-300">Priorities</a>
            <span aria-hidden="true">/</span>
            <span class="text-indigo-400" aria-current="page">Details</span>
        </nav>

        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex min-w-0 items-start gap-4">
                <div
                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border text-xl"
                    style="color: {{ $priorityColor }}; border-color: {{ $priorityColor }}40; background-color: {{ $priorityColor }}18;"
                >
                    <i class="{{ $priority->icon ?: 'fa-solid fa-flag' }}" aria-hidden="true"></i>
                </div>

                <div class="min-w-0">
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <span class="rounded-md border border-white/10 bg-white/[0.04] px-2 py-1 font-mono text-[11px] text-gray-400">
                            {{ $priority->code }}
                        </span>

                        @if ($priority->is_default)
                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-amber-500/20 bg-amber-500/10 px-2.5 py-1 text-xs font-medium text-amber-300">
                                <i class="fa-solid fa-star text-[10px]" aria-hidden="true"></i>
                                Default priority
                            </span>
                        @endif

                        @if ($priority->is_active)
                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400" aria-hidden="true"></span>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-500/20 bg-gray-500/10 px-2.5 py-1 text-xs font-medium text-gray-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-gray-500" aria-hidden="true"></span>
                                Inactive
                            </span>
                        @endif
                    </div>

                    <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                        {{ $priority->name }}
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                        {{ $priority->description ?: 'No description has been provided for this priority.' }}
                    </p>
                </div>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a
                    href="{{ route('ticket.priority.list') }}"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-white/10 px-4 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:bg-white/[0.05] hover:text-white"
                >
                    <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                    Back
                </a>

                @can('ticket.priority.update')
                    @if (Route::has('ticket.priority.edit'))
                        <a
                            href="{{ route('ticket.priority.edit', $priority->id) }}"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white shadow-lg shadow-indigo-950/30 transition hover:bg-indigo-500 focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/30"
                        >
                            <i class="fa-solid fa-pen text-xs" aria-hidden="true"></i>
                            Edit priority
                        </a>
                    @endif
                @endcan
            </div>
        </div>

        {{-- Priority summary --}}
        <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Priority summary">
            <div class="rounded-2xl border border-white/10 bg-gray-950/60 p-5 shadow-lg shadow-black/10 backdrop-blur-xl">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-400">Priority level</p>
                    <i class="fa-solid fa-layer-group text-indigo-400" aria-hidden="true"></i>
                </div>
                <p class="mt-3 text-2xl font-bold text-white">{{ $priority->level }}</p>
                <p class="mt-1 text-xs text-gray-500">Ordering level</p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-gray-950/60 p-5 shadow-lg shadow-black/10 backdrop-blur-xl">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-400">Response target</p>
                    <i class="fa-solid fa-reply text-sky-400" aria-hidden="true"></i>
                </div>
                <p class="mt-3 text-2xl font-bold text-white">
                    {{ $formatMinutes($priority->response_time_minutes) }}
                </p>
                <p class="mt-1 text-xs text-gray-500">Time to first response</p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-gray-950/60 p-5 shadow-lg shadow-black/10 backdrop-blur-xl">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-400">Resolution target</p>
                    <i class="fa-solid fa-check-double text-emerald-400" aria-hidden="true"></i>
                </div>
                <p class="mt-3 text-2xl font-bold text-white">
                    {{ $formatMinutes($priority->resolution_time_minutes) }}
                </p>
                <p class="mt-1 text-xs text-gray-500">Time to resolve the ticket</p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-gray-950/60 p-5 shadow-lg shadow-black/10 backdrop-blur-xl">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-400">Escalation target</p>
                    <i class="fa-solid fa-arrow-up-right-dots text-amber-400" aria-hidden="true"></i>
                </div>
                <p class="mt-3 text-2xl font-bold text-white">
                    {{ $formatMinutes($priority->escalation_time_minutes) }}
                </p>
                <p class="mt-1 text-xs text-gray-500">Time before escalation</p>
            </div>
        </section>

        {{-- Details cards --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- General details --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10 backdrop-blur-xl lg:col-span-2">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-300">
                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-white">Priority information</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Core settings and identification.</p>
                    </div>
                </div>

                <dl class="grid grid-cols-1 gap-x-8 gap-y-6 p-5 sm:grid-cols-2 sm:p-6">
                    <div>
                        <dt class="{{ $detailLabel }}">Name</dt>
                        <dd class="{{ $detailValue }}">{{ $priority->name }}</dd>
                    </div>

                    <div>
                        <dt class="{{ $detailLabel }}">Code</dt>
                        <dd class="{{ $detailValue }} font-mono">{{ $priority->code }}</dd>
                    </div>

                    <div>
                        <dt class="{{ $detailLabel }}">Priority level</dt>
                        <dd class="{{ $detailValue }}">{{ $priority->level }}</dd>
                    </div>

                    <div>
                        <dt class="{{ $detailLabel }}">Version</dt>
                        <dd class="{{ $detailValue }}">{{ $priority->version }}</dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="{{ $detailLabel }}">Description</dt>
                        <dd class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-gray-300">
                            {{ $priority->description ?: 'No description provided.' }}
                        </dd>
                    </div>
                </dl>
            </section>

            {{-- Appearance --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10 backdrop-blur-xl">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-500/10 text-violet-300">
                        <i class="fa-solid fa-palette" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-white">Appearance</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Visual identification.</p>
                    </div>
                </div>

                <div class="space-y-5 p-5">
                    <div>
                        <p class="{{ $detailLabel }}">Preview</p>
                        <div class="mt-3 flex items-center gap-3 rounded-xl border border-white/10 bg-white/[0.02] p-4">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl border text-lg"
                                style="color: {{ $priorityColor }}; border-color: {{ $priorityColor }}40; background-color: {{ $priorityColor }}18;"
                            >
                                <i class="{{ $priority->icon ?: 'fa-solid fa-flag' }}" aria-hidden="true"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-white">{{ $priority->name }}</p>
                                <p class="mt-1 text-xs text-gray-500">Level {{ $priority->level }}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <dt class="{{ $detailLabel }}">Color</dt>
                        <dd class="mt-2 flex items-center gap-2.5">
                            <span class="h-6 w-6 rounded-md border border-white/10" style="background-color: {{ $priorityColor }}" aria-hidden="true"></span>
                            <span class="font-mono text-sm text-gray-200">{{ $priority->color ?: 'Not configured' }}</span>
                        </dd>
                    </div>

                    <div>
                        <dt class="{{ $detailLabel }}">Icon class</dt>
                        <dd class="{{ $detailValue }} font-mono text-xs">{{ $priority->icon ?: 'Not configured' }}</dd>
                    </div>
                </div>
            </section>

            {{-- SLA details --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10 backdrop-blur-xl lg:col-span-2">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500/10 text-amber-300">
                        <i class="fa-solid fa-stopwatch" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-white">Service-level targets</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Configured time targets in minutes.</p>
                    </div>
                </div>

                <div class="divide-y divide-white/[0.06]">
                    @foreach ([
                        ['label' => 'Response time', 'value' => $priority->response_time_minutes, 'icon' => 'fa-reply', 'color' => 'text-sky-400'],
                        ['label' => 'Resolution time', 'value' => $priority->resolution_time_minutes, 'icon' => 'fa-check-double', 'color' => 'text-emerald-400'],
                        ['label' => 'Escalation time', 'value' => $priority->escalation_time_minutes, 'icon' => 'fa-arrow-up-right-dots', 'color' => 'text-amber-400'],
                    ] as $sla)
                        <div class="flex items-center justify-between gap-4 px-5 py-4 sm:px-6">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/[0.04] {{ $sla['color'] }}">
                                    <i class="fa-solid {{ $sla['icon'] }}" aria-hidden="true"></i>
                                </div>
                                <span class="text-sm text-gray-300">{{ $sla['label'] }}</span>
                            </div>
                            <span class="text-right text-sm font-semibold {{ $sla['value'] === null ? 'text-gray-500' : 'text-white' }}">
                                {{ $formatMinutes($sla['value']) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Record metadata --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10 backdrop-blur-xl">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-500/10 text-gray-300">
                        <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-white">Record metadata</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Audit timestamps.</p>
                    </div>
                </div>

                <dl class="space-y-5 p-5">
                    <div>
                        <dt class="{{ $detailLabel }}">Created at</dt>
                        <dd class="{{ $detailValue }}">
                            {{ $priority->created_at?->format('M d, Y') ?? '—' }}
                            @if ($priority->created_at)
                                <span class="mt-1 block text-xs font-normal text-gray-500">{{ $priority->created_at->format('H:i:s') }}</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="{{ $detailLabel }}">Last updated</dt>
                        <dd class="{{ $detailValue }}">
                            {{ $priority->updated_at?->format('M d, Y') ?? '—' }}
                            @if ($priority->updated_at)
                                <span class="mt-1 block text-xs font-normal text-gray-500">{{ $priority->updated_at->format('H:i:s') }}</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="{{ $detailLabel }}">Identifier</dt>
                        <dd class="mt-2 break-all font-mono text-xs text-gray-400">{{ $priority->id }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        {{-- Bottom navigation --}}
        <div class="mt-6 flex justify-start">
            <a
                href="{{ route('ticket.priority.list') }}"
                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-white/10 px-4 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:bg-white/[0.05] hover:text-white"
            >
                <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                Back to priorities
            </a>
        </div>
    </div>
</div>
@endsection
