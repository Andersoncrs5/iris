@extends('layouts.base')

@section('title', 'Edit Your Ticket | Iris')

@section('content')
@php
    $safeColor = static fn (?string $color): string =>
        preg_match('/^#[0-9a-fA-F]{6}$/', $color ?? '') ? $color : '#818cf8';

    $statusColor = $safeColor($ticket->status?->color);

    $sourceLabels = [
        'WEB' => 'Web',
        'EMAIL' => 'Email',
        'API' => 'API',
        'CHAT' => 'Chat',
        'WHATSAPP' => 'WhatsApp',
        'PHONE' => 'Phone',
    ];

    $selectedSource = old('source', $ticket->source);
    $selectedRating = old('satisfaction_rating', $ticket->satisfaction_rating);
    $selectedEscalation = (string) old(
        'is_escalated',
        $ticket->is_escalated ? '1' : '0'
    );

    $inputClass = 'w-full rounded-xl border bg-white/[0.03] px-4 py-3 text-sm text-white placeholder-gray-600 transition focus:border-indigo-500 focus:bg-white/[0.05] focus:outline-none focus:ring-4 focus:ring-indigo-500/10';
    $labelClass = 'mb-2 block text-sm font-medium text-gray-200';
@endphp

<style>
    [x-cloak] { display: none !important; }
</style>

<div class="relative min-h-screen px-4 py-8 text-gray-100 sm:px-6 lg:px-8">
    <div
        class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-[radial-gradient(ellipse_at_50%_0%,rgba(99,102,241,.16),transparent_70%)]"
        aria-hidden="true"
    ></div>

    <div class="mx-auto max-w-5xl">
        {{-- Breadcrumb --}}
        <nav class="mb-6 flex items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="transition hover:text-gray-300" aria-label="Dashboard">
                <i class="fa-solid fa-house text-xs" aria-hidden="true"></i>
            </a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('ticket.list') }}" class="transition hover:text-gray-300">Tickets</a>
            <span aria-hidden="true">/</span>
            <span class="text-indigo-400" aria-current="page">Edit your ticket</span>
        </nav>

        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-indigo-500/20 bg-indigo-500/10 text-indigo-400">
                    <i class="fa-solid fa-pen-to-square text-xl" aria-hidden="true"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-3xl font-bold tracking-tight text-white">Edit your ticket</h1>
                        <span class="rounded-lg border border-indigo-500/20 bg-indigo-500/10 px-2.5 py-1 font-mono text-xs font-semibold text-indigo-300">
                            #{{ $ticket->ticket_number }}
                        </span>
                    </div>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-gray-400">
                        Update your ticket details and feedback. Status and response timestamps are managed by the support team.
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

        @if (session('success'))
            <div role="status" class="mb-6 flex gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">
                <i class="fa-solid fa-circle-check mt-0.5" aria-hidden="true"></i>
                <p>{{ session('success') }}</p>
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

        <form action="{{ route('ticket.own.update', $ticket->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="version" value="{{ old('version', $ticket->version) }}">

            {{-- Ticket overview --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500/10 text-sky-400">
                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-white">Ticket overview</h2>
                        <p class="mt-0.5 text-xs text-gray-500">System-managed information about this request.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-3 sm:p-6">
                    <div class="rounded-xl border border-white/[0.08] bg-white/[0.02] p-4">
                        <p class="mb-2 text-xs font-medium uppercase tracking-wider text-gray-500">Ticket number</p>
                        <p class="font-mono text-lg font-semibold text-white">#{{ $ticket->ticket_number }}</p>
                        <p class="mt-1 text-xs text-gray-500">Reference number</p>
                    </div>

                    <div class="rounded-xl border border-white/[0.08] bg-white/[0.02] p-4">
                        <p class="mb-2 text-xs font-medium uppercase tracking-wider text-gray-500">Current status</p>
                        <div class="flex items-center gap-2">
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-lg border"
                                style="color: {{ $statusColor }}; border-color: {{ $statusColor }}55; background-color: {{ $statusColor }}15;"
                            >
                                <i class="{{ $ticket->status?->icon ?: 'fa-solid fa-circle-dot' }}" aria-hidden="true"></i>
                            </span>
                            <span class="font-medium text-white">{{ $ticket->status?->name ?? 'Unknown' }}</span>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Only authorized support staff can change status.</p>
                    </div>

                    <div class="rounded-xl border border-white/[0.08] bg-white/[0.02] p-4">
                        <p class="mb-2 text-xs font-medium uppercase tracking-wider text-gray-500">Assigned agent</p>
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg border border-white/10 bg-white/[0.04] text-gray-400">
                                <i class="fa-solid fa-headset" aria-hidden="true"></i>
                            </span>
                            <span class="truncate font-medium text-white">{{ $ticket->agent?->name ?? 'Unassigned' }}</span>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Assignment is managed by the support team.</p>
                    </div>
                </div>
            </section>

            {{-- Requester --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-400">
                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-white">Requester</h2>
                        <p class="mt-0.5 text-xs text-gray-500">This ticket remains associated with your account.</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-5 sm:p-6">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-indigo-500/20 bg-indigo-500/10 text-indigo-300">
                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="truncate font-medium text-white">{{ $ticket->user?->name ?? auth()->user()->name }}</p>
                        <p class="mt-1 truncate text-sm text-gray-400">{{ $ticket->user?->email ?? auth()->user()->email }}</p>
                    </div>
                    <span class="ml-auto shrink-0 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-300">Ticket owner</span>
                </div>
            </section>

            {{-- Owner-editable ticket details --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-500/10 text-violet-400">
                        <i class="fa-solid fa-file-pen" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-white">Ticket details</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Edit the subject, description, and source of your request.</p>
                    </div>
                </div>

                <div class="space-y-5 p-5 sm:p-6">
                    <div>
                        <label for="subject" class="{{ $labelClass }}">
                            Subject <span class="text-red-400">*</span>
                        </label>
                        <input
                            type="text"
                            name="subject"
                            id="subject"
                            value="{{ old('subject', $ticket->subject) }}"
                            maxlength="255"
                            required
                            autofocus
                            placeholder="Briefly describe the issue"
                            @class([
                                $inputClass,
                                'border-red-500/50' => $errors->has('subject'),
                                'border-white/10' => ! $errors->has('subject'),
                            ])
                        >
                        @error('subject')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="{{ $labelClass }}">
                            Description <span class="text-red-400">*</span>
                        </label>
                        <textarea
                            name="description"
                            id="description"
                            rows="6"
                            required
                            placeholder="Explain what happened, what you expected, and any relevant details..."
                            @class([
                                $inputClass . ' resize-y leading-6',
                                'border-red-500/50' => $errors->has('description'),
                                'border-white/10' => ! $errors->has('description'),
                            ])
                        >{{ old('description', $ticket->description) }}</textarea>
                        @error('description')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="source" class="{{ $labelClass }}">
                            Ticket source <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <select
                                name="source"
                                id="source"
                                required
                                @class([
                                    $inputClass . ' appearance-none pr-10 [&>option]:bg-gray-900',
                                    'border-red-500/50' => $errors->has('source'),
                                    'border-white/10' => ! $errors->has('source'),
                                ])
                            >
                                @foreach ($sourceLabels as $value => $sourceLabel)
                                    <option value="{{ $value }}" @selected($selectedSource === $value)>{{ $sourceLabel }}</option>
                                @endforeach
                            </select>
                            <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-gray-500" aria-hidden="true"></i>
                        </div>
                        @error('source')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">Select the channel associated with this ticket.</p>
                    </div>
                </div>
            </section>

            {{-- Satisfaction feedback --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500/10 text-amber-300">
                        <i class="fa-solid fa-star" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-white">Satisfaction feedback</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Provide or update your rating and comments.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 sm:p-6">
                    <div>
                        <label for="satisfaction_rating" class="{{ $labelClass }}">Satisfaction rating</label>
                        <input
                            type="number"
                            name="satisfaction_rating"
                            id="satisfaction_rating"
                            min="0"
                            max="65535"
                            step="1"
                            value="{{ $selectedRating }}"
                            placeholder="Leave blank if not rated"
                            @class([
                                $inputClass,
                                'border-red-500/50' => $errors->has('satisfaction_rating'),
                                'border-white/10' => ! $errors->has('satisfaction_rating'),
                            ])
                        >
                        @error('satisfaction_rating')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">Leave blank to clear the current rating.</p>
                    </div>

                    <div>
                        <label for="satisfaction_comment" class="{{ $labelClass }}">Satisfaction comment</label>
                        <textarea
                            name="satisfaction_comment"
                            id="satisfaction_comment"
                            rows="4"
                            maxlength="500"
                            placeholder="Share feedback about your experience..."
                            @class([
                                $inputClass . ' resize-y leading-6',
                                'border-red-500/50' => $errors->has('satisfaction_comment'),
                                'border-white/10' => ! $errors->has('satisfaction_comment'),
                            ])
                        >{{ old('satisfaction_comment', $ticket->satisfaction_comment) }}</textarea>
                        @error('satisfaction_comment')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">Maximum 500 characters.</p>
                    </div>
                </div>
            </section>

            {{-- Escalation flag --}}
            <section class="rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-orange-500/10 text-orange-300">
                        <i class="fa-solid fa-arrow-up-right-dots" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-white">Escalation</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Indicate whether this request needs additional attention.</p>
                    </div>
                </div>

                <div class="p-5 sm:p-6">
                    {{-- The hidden value ensures an unchecked checkbox submits false. --}}
                    <input type="hidden" name="is_escalated" value="0">
                    <label for="is_escalated" class="flex cursor-pointer items-start gap-3 rounded-xl border border-white/[0.08] bg-white/[0.02] p-4 transition hover:border-white/15 hover:bg-white/[0.035]">
                        <input
                            type="checkbox"
                            name="is_escalated"
                            id="is_escalated"
                            value="1"
                            @checked($selectedEscalation === '1')
                            class="mt-1 h-4 w-4 rounded border-gray-600 bg-gray-800 text-indigo-500 focus:ring-2 focus:ring-indigo-500/30 focus:ring-offset-0"
                        >
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-medium text-gray-200">Mark this ticket as escalated</span>
                            <span class="mt-1 block text-xs leading-5 text-gray-500">Use this flag to indicate that the request requires additional attention.</span>
                        </span>
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-orange-500/10 text-orange-300">
                            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                        </span>
                    </label>
                    @error('is_escalated')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            {{-- Read-only system timestamps --}}
            <section class="rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10">
                <div class="flex items-center gap-3 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-500/10 text-gray-400">
                        <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-white">Record history</h2>
                        <p class="mt-0.5 text-xs text-gray-500">Response and lifecycle timestamps are managed by the support team.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 sm:p-6">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500">Opened at</p>
                        <p class="mt-2 text-sm text-gray-200">{{ $ticket->opened_at?->format('M d, Y H:i') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500">Last activity</p>
                        <p class="mt-2 text-sm text-gray-200">{{ $ticket->last_activity_at?->format('M d, Y H:i') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500">First response</p>
                        <p class="mt-2 text-sm text-gray-200">{{ $ticket->first_response_at?->format('M d, Y H:i') ?? 'Not responded yet' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500">Resolved at</p>
                        <p class="mt-2 text-sm text-gray-200">{{ $ticket->resolved_at?->format('M d, Y H:i') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500">Closed at</p>
                        <p class="mt-2 text-sm text-gray-200">{{ $ticket->closed_at?->format('M d, Y H:i') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500">Last updated</p>
                        <p class="mt-2 text-sm text-gray-200">{{ $ticket->updated_at?->format('M d, Y H:i') ?? '—' }}</p>
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
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 text-sm font-semibold text-white shadow-lg shadow-indigo-950/30 transition hover:bg-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-500/30 active:scale-[0.99]"
                >
                    <i class="fa-solid fa-floppy-disk text-xs" aria-hidden="true"></i>
                    Save changes
                </button>
            </div>
        </form>

        <p class="mt-6 text-center text-xs text-gray-600">
            <i class="fa-solid fa-shield-halved mr-1" aria-hidden="true"></i>
            You can edit your ticket details, source, satisfaction feedback, and escalation flag. Status and support timestamps remain under the support team's control.
        </p>
    </div>
</div>
@endsection
