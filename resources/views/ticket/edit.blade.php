@extends('layouts.base')

@section('title', 'Edit Ticket | Iris')

@section('content')
@php
    $ticketLabel = $ticket->ticket_number ?? $ticket->id;

    $safeColor = static fn (?string $color): string =>
        preg_match('/^#[0-9a-fA-F]{6}$/', $color ?? '') ? $color : '#818cf8';

    $statusColor = $safeColor($ticket->status?->color);

    $dateTimeValue = static function (string $field) use ($ticket): string {
        $oldValue = old($field);

        if ($oldValue !== null) {
            return (string) $oldValue;
        }

        return $ticket->{$field}?->format('Y-m-d\\TH:i') ?? '';
    };
@endphp

<div class="relative min-h-screen px-4 py-8 text-gray-100 sm:px-6 lg:px-8">
    <div
        class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-[radial-gradient(ellipse_at_50%_0%,rgba(99,102,241,.16),transparent_70%)]"
        aria-hidden="true"
    ></div>

    <div class="mx-auto max-w-5xl space-y-6">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="transition hover:text-gray-300" aria-label="Dashboard">
                <i class="fa-solid fa-house text-xs" aria-hidden="true"></i>
            </a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('ticket.list') }}" class="transition hover:text-gray-300">Tickets</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('ticket.show-details', $ticket->id) }}" class="transition hover:text-gray-300">#{{ $ticketLabel }}</a>
            <span aria-hidden="true">/</span>
            <span class="text-indigo-400" aria-current="page">Support update</span>
        </nav>

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-indigo-500/20 bg-indigo-500/10 text-indigo-400">
                    <i class="fa-solid fa-headset text-xl" aria-hidden="true"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-400">Support workspace</span>
                        <span class="rounded-lg border border-indigo-500/20 bg-indigo-500/10 px-2.5 py-1 font-mono text-xs font-semibold text-indigo-300">
                            #{{ $ticketLabel }}
                        </span>
                    </div>
                    <h1 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">Update ticket status</h1>
                    <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-400">
                        Update the ticket workflow and support timestamps. Request details, classification, and assignment are managed separately.
                    </p>
                </div>
            </div>

            <a
                href="{{ route('ticket.show-details', $ticket->id) }}"
                class="inline-flex h-10 items-center justify-center gap-2 self-start rounded-xl border border-white/10 px-4 text-sm font-medium text-gray-300 transition hover:bg-white/[0.05] hover:text-white"
            >
                <i class="fa-regular fa-eye text-xs" aria-hidden="true"></i>
                View ticket
            </a>
        </div>

        {{-- Feedback --}}
        @if (session('error'))
            <div role="alert" class="flex items-start gap-3 rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-300">
                <i class="fa-solid fa-circle-exclamation mt-0.5" aria-hidden="true"></i>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        @if (session('success'))
            <div role="status" class="flex items-start gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">
                <i class="fa-solid fa-circle-check mt-0.5" aria-hidden="true"></i>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="flex gap-3 rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-300">
                <i class="fa-solid fa-circle-exclamation mt-0.5" aria-hidden="true"></i>
                <div>
                    <p class="font-semibold">Please review the form.</p>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-red-300/90">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if (!isset($statuses) || $statuses->isEmpty())
            <div role="alert" class="flex gap-3 rounded-xl border border-amber-500/20 bg-amber-500/10 p-4 text-sm text-amber-200">
                <i class="fa-solid fa-triangle-exclamation mt-0.5" aria-hidden="true"></i>
                <div>
                    <p class="font-semibold">No active ticket statuses are available.</p>
                    <p class="mt-1 text-amber-200/80">Ask an administrator to configure an active ticket status before updating this ticket.</p>
                </div>
            </div>
        @endif

        {{-- Ticket overview --}}
        <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10">
            <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500/10 text-sky-400">
                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                </div>
                <div>
                    <h2 class="font-semibold text-white">Ticket overview</h2>
                    <p class="mt-0.5 text-xs text-gray-500">Information shown for context. These fields are not editable in this form.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3 sm:p-6">
                <div class="rounded-xl border border-white/[0.08] bg-white/[0.02] p-4">
                    <p class="mb-2 text-xs font-medium uppercase tracking-wider text-gray-500">Ticket number</p>
                    <p class="font-mono text-lg font-semibold text-white">#{{ $ticketLabel }}</p>
                    <p class="mt-1 text-xs text-gray-500">Internal ID: {{ $ticket->id }}</p>
                </div>

                <div class="rounded-xl border border-white/[0.08] bg-white/[0.02] p-4">
                    <p class="mb-2 text-xs font-medium uppercase tracking-wider text-gray-500">Requester</p>
                    <p class="truncate font-medium text-white">{{ $ticket->user?->full_name ?: ($ticket->user?->name ?? 'Unknown requester') }}</p>
                    <p class="mt-1 truncate text-xs text-gray-500">{{ $ticket->user?->email ?? 'No email available' }}</p>
                </div>

                <div class="rounded-xl border border-white/[0.08] bg-white/[0.02] p-4">
                    <p class="mb-2 text-xs font-medium uppercase tracking-wider text-gray-500">Current status</p>
                    <div class="flex items-center gap-2">
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border"
                            style="color: {{ $statusColor }}; border-color: {{ $statusColor }}55; background-color: {{ $statusColor }}15;"
                        >
                            <i class="{{ $ticket->status?->icon ?: 'fa-solid fa-circle-dot' }}" aria-hidden="true"></i>
                        </span>
                        <span class="truncate font-medium text-white">{{ $ticket->status?->name ?? 'Not defined' }}</span>
                    </div>
                </div>

                <div class="rounded-xl border border-white/[0.08] bg-white/[0.02] p-4">
                    <p class="mb-2 text-xs font-medium uppercase tracking-wider text-gray-500">Subject</p>
                    <p class="font-medium text-white">{{ $ticket->subject }}</p>
                    <p class="mt-1 line-clamp-2 text-xs leading-5 text-gray-500">{{ $ticket->description }}</p>
                </div>

                <div class="rounded-xl border border-white/[0.08] bg-white/[0.02] p-4">
                    <p class="mb-2 text-xs font-medium uppercase tracking-wider text-gray-500">Category / Priority</p>
                    <p class="font-medium text-white">{{ $ticket->category?->name ?? 'Not defined' }}</p>
                    <p class="mt-1 text-xs text-gray-500">Priority: {{ $ticket->priority?->name ?? 'Not defined' }}</p>
                </div>

                <div class="rounded-xl border border-white/[0.08] bg-white/[0.02] p-4">
                    <p class="mb-2 text-xs font-medium uppercase tracking-wider text-gray-500">Assigned agent</p>
                    <p class="font-medium text-white">{{ $ticket->agent?->full_name ?: ($ticket->agent?->name ?? 'Unassigned') }}</p>
                    <p class="mt-1 text-xs text-gray-500">Assignment is managed separately.</p>
                </div>
            </div>
        </section>

        {{-- Support-editable fields --}}
        <form action="{{ route('ticket.update', $ticket->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="version" value="{{ old('version', $ticket->version) }}">

            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-400">
                        <i class="fa-solid fa-route" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-white">Workflow</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Change the ticket status according to its current support stage.</p>
                    </div>
                </div>

                <div class="p-5 sm:p-6">
                    <label for="status_id" class="mb-2 block text-sm font-medium text-gray-200">
                        Ticket status <span class="text-red-400">*</span>
                    </label>
                    <select
                        id="status_id"
                        name="status_id"
                        required
                        @class([
                            'h-12 w-full rounded-xl border bg-white/[0.03] px-4 text-sm text-white transition focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 [&>option]:bg-gray-900',
                            'border-red-500/50' => $errors->has('status_id'),
                            'border-white/10' => ! $errors->has('status_id'),
                        ])
                    >
                        <option value="">Select a status</option>
                        @foreach (($statuses ?? collect()) as $status)
                            <option value="{{ $status->id }}" @selected((string) old('status_id', $ticket->status_id) === (string) $status->id)>
                                {{ $status->name }}{{ $status->code ? ' · ' . $status->code : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('status_id')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs leading-5 text-gray-500">Status changes should follow the support workflow configured for Iris.</p>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-500/10 text-violet-400">
                        <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-white">Support timestamps</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Record the first response, resolution, and closure times when applicable.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 sm:p-6">
                    <div>
                        <label for="first_response_at" class="mb-2 block text-sm font-medium text-gray-200">First response at</label>
                        <input
                            type="datetime-local"
                            id="first_response_at"
                            name="first_response_at"
                            value="{{ $dateTimeValue('first_response_at') }}"
                            @class([
                                'h-11 w-full rounded-xl border bg-white/[0.03] px-3.5 text-sm text-white transition focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10',
                                'border-red-500/50' => $errors->has('first_response_at'),
                                'border-white/10' => ! $errors->has('first_response_at'),
                            ])
                        >
                        @error('first_response_at')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">Leave empty if the first response has not occurred.</p>
                    </div>

                    <div>
                        <label for="resolved_at" class="mb-2 block text-sm font-medium text-gray-200">Resolved at</label>
                        <input
                            type="datetime-local"
                            id="resolved_at"
                            name="resolved_at"
                            value="{{ $dateTimeValue('resolved_at') }}"
                            @class([
                                'h-11 w-full rounded-xl border bg-white/[0.03] px-3.5 text-sm text-white transition focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10',
                                'border-red-500/50' => $errors->has('resolved_at'),
                                'border-white/10' => ! $errors->has('resolved_at'),
                            ])
                        >
                        @error('resolved_at')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">Leave empty if the ticket has not been resolved.</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="closed_at" class="mb-2 block text-sm font-medium text-gray-200">Closed at</label>
                        <input
                            type="datetime-local"
                            id="closed_at"
                            name="closed_at"
                            value="{{ $dateTimeValue('closed_at') }}"
                            @class([
                                'h-11 w-full rounded-xl border bg-white/[0.03] px-3.5 text-sm text-white transition focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 sm:max-w-xl',
                                'border-red-500/50' => $errors->has('closed_at'),
                                'border-white/10' => ! $errors->has('closed_at'),
                            ])
                        >
                        @error('closed_at')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">Leave empty if the ticket has not been closed.</p>
                    </div>
                </div>
            </section>

            {{-- Read-only metadata --}}
            <section class="rounded-2xl border border-white/10 bg-gray-950/40 p-5 sm:p-6">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-shield-halved mt-0.5 text-gray-500" aria-hidden="true"></i>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-200">Fields managed separately</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-400">
                            This support form only updates the ticket status and support timestamps. Subject, description, source, satisfaction feedback, escalation flag, category, priority, and assignment are not changed here.
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2 text-xs">
                            <span class="rounded-lg border border-white/10 bg-white/[0.03] px-2.5 py-1.5 text-gray-400">Source: {{ $ticket->source }}</span>
                            <span class="rounded-lg border border-white/10 bg-white/[0.03] px-2.5 py-1.5 text-gray-400">Version: {{ $ticket->version }}</span>
                            <span class="rounded-lg border border-white/10 bg-white/[0.03] px-2.5 py-1.5 text-gray-400">Escalated: {{ $ticket->is_escalated ? 'Yes' : 'No' }}</span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Actions --}}
            <div class="flex flex-col-reverse gap-3 border-t border-white/[0.08] pt-6 sm:flex-row sm:items-center sm:justify-between">
                <a
                    href="{{ route('ticket.show-details', $ticket->id) }}"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-white/10 px-5 text-sm font-medium text-gray-300 transition hover:bg-white/[0.05] hover:text-white"
                >
                    <i class="fa-solid fa-xmark text-xs" aria-hidden="true"></i>
                    Cancel
                </a>

                <button
                    type="submit"
                    @disabled(!isset($statuses) || $statuses->isEmpty())
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 text-sm font-semibold text-white shadow-lg shadow-indigo-950/30 transition hover:bg-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/30 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-indigo-600"
                >
                    <i class="fa-solid fa-floppy-disk text-xs" aria-hidden="true"></i>
                    Save support updates
                </button>
            </div>
        </form>

        <p class="text-center text-xs text-gray-600">
            <i class="fa-solid fa-lock mr-1" aria-hidden="true"></i>
            Only authorized support staff can update workflow fields.
        </p>
    </div>
</div>
@endsection
