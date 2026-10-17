@extends('layouts.base')

@section('title', 'Edit comment - Iris')

@section('content')
    @php
        $ticket = $message->ticket;

        $ticketTitle = $ticket?->title
            ?? $ticket?->subject
            ?? "Ticket #{$message->ticket_id}";

        $status = $ticket?->status;
        $priority = $ticket?->priority;

        $statusName = $status?->name ?? 'Not specified';
        $statusColor = $status?->color ?? '#64748b';
        $statusIcon = $status?->icon ?? 'fa-solid fa-circle';

        $priorityName = $priority?->name ?? 'Not specified';
        $priorityColor = $priority?->color ?? '#6366f1';
        $priorityIcon = $priority?->icon ?? 'fa-solid fa-bolt';
    @endphp

    <div class="mx-auto w-full max-w-5xl space-y-6 pb-12">

        {{-- Page header --}}
        <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-xs font-medium text-gray-500">
                    <i class="fa-solid fa-ticket text-indigo-400" aria-hidden="true"></i>

                    <span>Tickets</span>

                    <i class="fa-solid fa-chevron-right text-[9px]" aria-hidden="true"></i>

                    <span>#{{ $message->ticket_id }}</span>

                    <i class="fa-solid fa-chevron-right text-[9px]" aria-hidden="true"></i>

                    <span>Message #{{ $message->id }}</span>
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-white">
                    Edit comment
                </h1>

                <p class="mt-1 text-sm text-gray-400">
                    Update your message in the ticket conversation.
                </p>
            </div>

            <a
                href="{{ url()->previous() }}"
                class="inline-flex items-center justify-center gap-2 self-start rounded-lg border border-white/10 px-4 py-2.5 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:bg-white/[0.04] hover:text-white sm:self-center"
            >
                <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                Back
            </a>
        </header>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="flex items-center gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/[0.08] px-4 py-3 text-sm text-emerald-300">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 rounded-xl border border-red-500/20 bg-red-500/[0.08] px-4 py-3 text-sm text-red-300">
                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                {{ session('error') }}
            </div>
        @endif

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="rounded-xl border border-red-500/20 bg-red-500/[0.08] px-4 py-3 text-sm text-red-300">
                <div class="mb-2 flex items-center gap-2 font-semibold">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                    Please correct the following errors.
                </div>

                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Ticket summary --}}
        <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-900/70 shadow-lg shadow-black/10">

            <div class="flex flex-col gap-4 border-b border-white/[0.07] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="flex min-w-0 items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-indigo-400/15 bg-indigo-500/10 text-indigo-300">
                        <i class="fa-solid fa-ticket text-lg" aria-hidden="true"></i>
                    </div>

                    <div class="min-w-0">
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            Ticket #{{ $message->ticket_id }}
                        </p>

                        <h2 class="mt-1 break-words text-lg font-semibold text-white">
                            {{ $ticketTitle }}
                        </h2>

                        <p class="mt-1 text-xs text-gray-500">
                            Message #{{ $message->id }}
                            <span class="mx-1">·</span>
                            Created
                            {{ $message->created_at?->format('M d, Y \a\t H:i') ?? '—' }}
                        </p>
                    </div>
                </div>

                {{-- Ticket status --}}
                <div
                    class="inline-flex w-fit shrink-0 items-center gap-2 rounded-lg border px-3 py-2 text-xs font-semibold"
                    style="color: {{ $statusColor }}; background-color: {{ $statusColor }}18; border-color: {{ $statusColor }}45;"
                >
                    <i class="{{ $statusIcon }}" aria-hidden="true"></i>
                    {{ $statusName }}
                </div>
            </div>

            {{-- Ticket metadata --}}
            <div class="grid grid-cols-1 divide-y divide-white/[0.06] sm:grid-cols-2 sm:divide-y-0">

                <div class="flex items-center gap-3 px-5 py-4 sm:border-r sm:border-white/[0.06] sm:px-6">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border"
                        style="color: {{ $priorityColor }}; background-color: {{ $priorityColor }}15; border-color: {{ $priorityColor }}35;"
                    >
                        <i class="{{ $priorityIcon }}" aria-hidden="true"></i>
                    </div>

                    <div class="min-w-0">
                        <p class="text-xs text-gray-500">Priority</p>

                        <p class="mt-1 text-sm font-semibold text-gray-200">
                            {{ $priorityName }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 px-5 py-4 sm:px-6">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-white/[0.07] bg-white/[0.03] text-gray-400">
                        <i class="fa-regular fa-calendar text-sm" aria-hidden="true"></i>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Last updated</p>

                        <p class="mt-1 text-sm font-semibold text-gray-200">
                            {{ $message->updated_at?->format('d/m/Y H:i') ?? '—' }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Edit message form --}}
        <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-900/70 shadow-lg shadow-black/10">

            <div class="border-b border-white/[0.07] px-5 py-5 sm:px-6">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-indigo-400/15 bg-indigo-500/10 text-indigo-300">
                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                    </div>

                    <div>
                        <h2 class="text-base font-semibold text-white">
                            Edit your message
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Make the necessary changes and save your update.
                        </p>
                    </div>
                </div>
            </div>

            <form
                action="{{ route('ticket.message.edit.form', ['id' => $message->id]) }}"
                method="POST"
                class="space-y-5 p-5 sm:p-6"
            >
                @csrf
                @method('PUT')

                <div>
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <label for="message" class="text-sm font-medium text-gray-200">
                            Message <span class="text-red-400">*</span>
                        </label>

                        <span class="text-xs text-gray-600">
                            Max. 10,000 characters
                        </span>
                    </div>

                    <textarea
                        id="message"
                        name="message"
                        rows="8"
                        maxlength="10000"
                        required
                        autofocus
                        placeholder="Write your message..."
                        class="block w-full resize-y rounded-xl border border-white/10 bg-gray-950/60 px-4 py-3.5 text-sm leading-6 text-gray-100 outline-none transition placeholder:text-gray-600 hover:border-white/15 focus:border-indigo-400/50 focus:ring-4 focus:ring-indigo-500/10 @error('message') border-red-400/50 focus:border-red-400/60 focus:ring-red-500/10 @enderror"
                    >{{ old('message', $message->message) }}</textarea>

                    @error('message')
                        <p class="mt-2 flex items-center gap-2 text-xs text-red-400">
                            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                            {{ $message }}
                        </p>
                    @enderror

                    <p class="mt-2 text-xs leading-5 text-gray-500">
                        Only the message content will be updated.
                    </p>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-white/[0.07] pt-5 sm:flex-row sm:items-center sm:justify-end">

                    <a
                        href="{{ route('ticket.show-details', ['id' => $message->ticket_id]) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-white/10 px-5 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/[0.04] hover:text-white"
                    >
                        <i class="fa-solid fa-ticket text-xs" aria-hidden="true"></i>
                        See Ticket
                    </a>

                    <a
                        href="{{ url()->previous() }}"
                        class="inline-flex items-center justify-center rounded-lg border border-white/10 px-5 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/[0.04] hover:text-white"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-500/10 transition hover:bg-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 active:scale-[0.98]"
                    >
                        <i class="fa-solid fa-floppy-disk text-xs" aria-hidden="true"></i>
                        Save changes
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection