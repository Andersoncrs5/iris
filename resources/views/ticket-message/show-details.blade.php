@extends('layouts.base')

@section('title', 'Message Details | Iris')

@section('content')
    @php
        // The controller passes the already-loaded TicketModel.
        $ticket = $ticket ?? $ticketMessage->getRelation('ticket');

        $typeValue = $ticketMessage->type instanceof \BackedEnum
            ? $ticketMessage->type->value
            : (string) $ticketMessage->type;

        $isInternal = (bool) $ticketMessage->is_internal;
        $isEdited = (bool) $ticketMessage->is_edited;

        $canViewInternal = auth()->user()?->can(
            'ticket.message.internal.view'
        ) ?? false;

        // The model relationship is named parentMessage().
        $parentMessage = $ticketMessage->parentMessage;

        // Replies in this UI stay attached to the original message.
        $threadRootId = $ticketMessage->parent_id
            ?? $ticketMessage->id;

        $typeLabel = match ($typeValue) {
            'REPLY' => 'Comment',
            'NOTE' => 'Internal note',
            'SYSTEM_EVENT' => 'System event',
            default => $typeValue,
        };

        $typeStyle = match (true) {
            $isInternal => 'border-amber-400/20 bg-amber-400/10 text-amber-300',
            $typeValue === 'SYSTEM_EVENT' => 'border-sky-400/20 bg-sky-400/10 text-sky-300',
            default => 'border-indigo-400/20 bg-indigo-400/10 text-indigo-300',
        };

        $authorName = $ticketMessage->user?->name ?? 'Unknown user';

        $canReply = $ticket !== null
            && ! $ticket->trashed()
            && (auth()->user()?->can('ticket.message.create') ?? false);

        $formatDate = static fn ($date) =>
            $date?->format('M d, Y · H:i') ?? 'Not recorded';
    @endphp

    <div class="relative isolate min-h-screen px-4 py-8 text-gray-100 sm:px-6 lg:px-8">
        <div
            class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-[radial-gradient(ellipse_at_50%_0%,rgba(99,102,241,.16),transparent_70%)]"
            aria-hidden="true"
        ></div>

        <div class="mx-auto max-w-5xl space-y-6">

            {{-- Breadcrumb --}}
            <nav class="flex flex-wrap items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="transition hover:text-gray-300">
                    <i class="fa-solid fa-house text-xs" aria-hidden="true"></i>
                    <span class="sr-only">Dashboard</span>
                </a>

                <span aria-hidden="true">/</span>

                <a
                    href="{{ url('/ticket/show-details/' . $ticketMessage->ticket_id) }}"
                    class="transition hover:text-gray-300"
                >
                    Ticket #{{ $ticket?->ticket_number ?? $ticketMessage->ticket_id }}
                </a>

                <span aria-hidden="true">/</span>
                <span class="text-indigo-400" aria-current="page">Message details</span>
            </nav>

            {{-- Flash messages --}}
            @if (session('success'))
                <div role="status" class="flex items-start gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">
                    <i class="fa-solid fa-circle-check mt-0.5" aria-hidden="true"></i>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div role="alert" class="flex items-start gap-3 rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-300">
                    <i class="fa-solid fa-circle-exclamation mt-0.5" aria-hidden="true"></i>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            {{-- Header --}}
            <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-indigo-400/20 bg-indigo-500/10 text-indigo-300">
                        <i class="fa-solid fa-comment-dots text-lg" aria-hidden="true"></i>
                    </div>

                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Message #{{ $ticketMessage->id }}
                        </p>
                        <h1 class="mt-1 text-2xl font-bold tracking-tight text-white">
                            Message details
                        </h1>
                        <p class="mt-1 text-sm text-gray-400">
                            View the comment and its conversation context.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a
                        href="{{ url('/ticket/show-details/' . $ticketMessage->ticket_id) }}"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-white/10 px-4 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:bg-white/[0.05] hover:text-white"
                    >
                        <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                        Back to ticket
                    </a>

                    {{-- Create a new top-level comment --}}
                    @if ($canReply)
                        <a
                            href="{{ route('ticket.message.create', ['id' => $ticketMessage->ticket_id]) }}"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-indigo-500 px-4 text-sm font-semibold text-white transition hover:bg-indigo-400 focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/20"
                        >
                            <i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>
                            Add comment
                        </a>
                    @endif
                </div>
            </header>

            {{-- Ticket summary --}}
            @if ($ticket)
                <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10">
                    <div class="flex flex-col gap-3 border-b border-white/[0.07] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                                Related ticket #{{ $ticket->ticket_number }}
                            </p>
                            <h2 class="mt-1 break-words text-lg font-semibold text-white">
                                {{ $ticket->subject }}
                            </h2>
                            <p class="mt-1 text-xs text-gray-500">
                                Created {{ $formatDate($ticket->created_at) }}
                            </p>
                        </div>

                        @if ($ticket->status)
                            <span class="inline-flex w-fit shrink-0 items-center gap-2 rounded-lg border border-white/10 bg-white/[0.03] px-3 py-2 text-xs font-medium text-gray-300">
                                @if ($ticket->status->icon)
                                    <i class="{{ $ticket->status->icon }}" aria-hidden="true"></i>
                                @endif
                                {{ $ticket->status->name }}
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 gap-4 px-5 py-4 sm:grid-cols-3 sm:px-6">
                        <div>
                            <p class="text-xs text-gray-500">Requester</p>
                            <p class="mt-1 text-sm font-medium text-gray-200">
                                {{ $ticket->user?->name ?? 'Unavailable' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Assigned agent</p>
                            <p class="mt-1 text-sm font-medium text-gray-200">
                                {{ $ticket->agent?->name ?? 'Unassigned' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Priority</p>
                            <p class="mt-1 text-sm font-medium text-gray-200">
                                {{ $ticket->priority?->name ?? 'Unavailable' }}
                            </p>
                        </div>
                    </div>
                </section>
            @endif

            {{-- Parent message, when the current message is a reply --}}
            @if ($parentMessage !== null && (! $parentMessage->is_internal || $canViewInternal))
                <section class="overflow-hidden rounded-xl border border-indigo-400/20 bg-indigo-500/[0.04]">
                    <div class="flex items-center gap-2 border-b border-indigo-400/10 px-5 py-3">
                        <i class="fa-solid fa-reply text-xs text-indigo-400" aria-hidden="true"></i>
                        <h2 class="text-sm font-semibold text-indigo-200">Replying to</h2>
                        <span class="ml-auto text-xs text-gray-500">Message #{{ $parentMessage->id }}</span>
                    </div>

                    <div class="px-5 py-4">
                        <p class="text-sm font-semibold text-gray-200">
                            {{ $parentMessage->user?->name ?? 'Unknown user' }}
                        </p>
                        <p class="mt-3 whitespace-pre-line break-words text-sm leading-6 text-gray-400">
                            {{ $parentMessage->message }}
                        </p>
                    </div>
                </section>
            @endif

            {{-- Main message --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/[0.07] px-5 py-4 sm:px-6">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 text-xs font-semibold {{ $typeStyle }}">
                            @if ($isInternal)
                                <i class="fa-solid fa-lock text-[10px]" aria-hidden="true"></i>
                            @elseif ($typeValue === 'SYSTEM_EVENT')
                                <i class="fa-solid fa-gears text-[10px]" aria-hidden="true"></i>
                            @else
                                <i class="fa-solid fa-comment text-[10px]" aria-hidden="true"></i>
                            @endif
                            {{ $typeLabel }}
                        </span>

                        @if ($isEdited)
                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/[0.03] px-2.5 py-1 text-xs text-gray-400">
                                <i class="fa-solid fa-pen text-[10px]" aria-hidden="true"></i>
                                Edited
                            </span>
                        @endif
                    </div>

                    <span class="font-mono text-xs text-gray-500">v{{ $ticketMessage->version }}</span>
                </div>

                <div class="p-5 sm:p-7">
                    {{-- Author --}}
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-indigo-400/20 bg-indigo-500/10 text-sm font-bold text-indigo-200">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($authorName, 0, 1)) }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="break-words text-sm font-semibold text-white">
                                {{-- Keep existing profile-view logic unchanged. --}}
                                @if ($ticketMessage->user && \Illuminate\Support\Facades\Route::has('user.show'))
                                    <a href="{{ route('user.show', ['id' => $ticketMessage->user->id]) }}" class="transition hover:text-indigo-300">
                                        {{ $authorName }}
                                    </a>
                                @else
                                    {{ $authorName }}
                                @endif
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                Posted {{ $formatDate($ticketMessage->created_at) }}
                            </p>
                            @if ($isEdited && $ticketMessage->edited_at)
                                <p class="mt-1 text-xs text-gray-500">
                                    Last edited {{ $formatDate($ticketMessage->edited_at) }}
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Message body --}}
                    <div class="mt-6 rounded-xl border border-white/[0.06] bg-white/[0.02] p-4 sm:p-5">
                        <p class="whitespace-pre-line break-words text-sm leading-7 text-gray-200">{{ $ticketMessage->message }}</p>
                    </div>

                    {{-- Actions for the main message --}}
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <div class="text-xs text-gray-500">
                            Message #{{ $ticketMessage->id }}
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            @can('update', $ticketMessage)
                                <a
                                    href="{{ route('ticket.message.edit', ['id' => $ticketMessage->id]) }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 px-3.5 py-2 text-xs font-semibold text-gray-300 transition hover:border-white/20 hover:bg-white/[0.04] hover:text-white"
                                >
                                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                    Edit comment
                                </a>
                            @endcan

                            @can('delete', $ticketMessage)
                                <form
                                    action="{{ route('ticket.message.delete', ['id' => $ticketMessage->id]) }}"
                                    method="POST"
                                    onsubmit="return confirm('Delete this comment? This action cannot be undone.');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-red-400/20 bg-red-500/[0.06] px-3.5 py-2 text-xs font-semibold text-red-300 transition hover:border-red-400/40 hover:bg-red-500/10 hover:text-red-200"
                                    >
                                        <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                        Delete comment
                                    </button>
                                </form>
                            @endcan

                            @if ($canReply)
                                <a
                                    href="{{ route('ticket.message.create', [
                                        'id' => $ticketMessage->ticket_id,
                                        'parentId' => $threadRootId,
                                    ]) }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-400/20 bg-indigo-500/[0.06] px-3.5 py-2 text-xs font-semibold text-indigo-300 transition hover:border-indigo-400/40 hover:bg-indigo-500/10 hover:text-indigo-200"
                                >
                                    <i class="fa-solid fa-reply" aria-hidden="true"></i>
                                    Reply to comment
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Attachments metadata --}}
                    @if (is_array($ticketMessage->attachments) && count($ticketMessage->attachments) > 0)
                        <div class="mt-5 flex items-center gap-3 rounded-xl border border-white/[0.07] bg-white/[0.02] p-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-sky-500/10 text-sky-300">
                                <i class="fa-solid fa-paperclip" aria-hidden="true"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-200">Attachments</p>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ count($ticketMessage->attachments) }}
                                    {{ \Illuminate\Support\Str::plural('attachment', count($ticketMessage->attachments)) }} associated with this message.
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- Metadata --}}
                    <div class="mt-6 grid grid-cols-1 gap-4 border-t border-white/[0.07] pt-5 sm:grid-cols-3">
                        <div>
                            <p class="text-xs text-gray-500">Message ID</p>
                            <p class="mt-1 break-all font-mono text-xs text-gray-300">{{ $ticketMessage->id }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Author ID</p>
                            <p class="mt-1 break-all font-mono text-xs text-gray-300">{{ $ticketMessage->user_id }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Last updated</p>
                            <p class="mt-1 text-xs text-gray-300">{{ $formatDate($ticketMessage->updated_at) }}</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Paginated replies in the conversation thread --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/10">
                <div class="flex flex-col gap-3 border-b border-white/[0.07] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-500/10 text-violet-300">
                            <i class="fa-solid fa-comments" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h2 class="font-semibold text-white">Replies</h2>
                            <p class="mt-0.5 text-xs text-gray-500">
                                {{ $replies->total() }}
                                {{ \Illuminate\Support\Str::plural('reply', $replies->total()) }} in this conversation.
                            </p>
                        </div>
                    </div>

                    @if ($canReply)
                        <a
                            href="{{ route('ticket.message.create', ['id' => $ticketMessage->ticket_id, 'parentId' => $threadRootId]) }}"
                            class="inline-flex h-9 items-center justify-center gap-2 rounded-lg border border-indigo-400/20 bg-indigo-500/[0.06] px-3.5 text-xs font-semibold text-indigo-300 transition hover:border-indigo-400/40 hover:bg-indigo-500/10 hover:text-indigo-200"
                        >
                            <i class="fa-solid fa-reply" aria-hidden="true"></i>
                            Reply to thread
                        </a>
                    @endif
                </div>

                @if ($replies->isEmpty())
                    <div class="px-5 py-10 text-center sm:px-6">
                        <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-xl border border-white/10 bg-white/[0.03] text-gray-500">
                            <i class="fa-regular fa-comments text-lg" aria-hidden="true"></i>
                        </div>
                        <h3 class="mt-3 text-sm font-semibold text-gray-200">No replies yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Replies to this comment will appear here.</p>
                    </div>
                @else
                    <div class="divide-y divide-white/[0.07]">
                        @foreach ($replies as $reply)
                            <article class="flex items-start gap-3 px-5 py-5 sm:px-6">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-white/10 bg-white/[0.04] text-xs font-semibold text-gray-300">
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($reply->user?->name ?? 'U', 0, 1)) }}
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-sm font-semibold text-gray-200">
                                                {{-- Keep the existing profile-view logic unchanged. --}}
                                                @if ($reply->user && \Illuminate\Support\Facades\Route::has('user.show'))
                                                    <a href="{{ route('user.show', ['id' => $reply->user->id]) }}" class="transition hover:text-indigo-300">
                                                        {{ $reply->user->name }}
                                                    </a>
                                                @else
                                                    {{ $reply->user?->name ?? 'Unknown user' }}
                                                @endif
                                            </p>

                                            @if ($reply->is_internal)
                                                <span class="inline-flex items-center gap-1 rounded-md border border-amber-400/20 bg-amber-400/10 px-2 py-0.5 text-[10px] font-semibold uppercase text-amber-300">
                                                    <i class="fa-solid fa-lock text-[9px]" aria-hidden="true"></i>
                                                    Internal note
                                                </span>
                                            @endif
                                        </div>

                                        <time datetime="{{ $reply->created_at?->toIso8601String() }}" class="text-xs text-gray-500">
                                            {{ $formatDate($reply->created_at) }}
                                        </time>
                                    </div>

                                    <p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-gray-400">{{ $reply->message }}</p>

                                    <div class="mt-3 flex flex-wrap items-center gap-4">
                                        <a
                                            href="{{ route('ticket.message.show', ['id' => $reply->id]) }}"
                                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-sky-300 transition hover:text-sky-200"
                                        >
                                            <i class="fa-solid fa-eye text-[10px]" aria-hidden="true"></i>
                                            View details
                                        </a>

                                        @if ($canReply)
                                            <a
                                                href="{{ route('ticket.message.create', ['id' => $ticketMessage->ticket_id, 'parentId' => $threadRootId]) }}"
                                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-300 transition hover:text-indigo-200"
                                            >
                                                <i class="fa-solid fa-reply text-[10px]" aria-hidden="true"></i>
                                                Reply
                                            </a>
                                        @endif

                                        @can('update', $reply)
                                            <a
                                                href="{{ route('ticket.message.edit', ['id' => $reply->id]) }}"
                                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-300 transition hover:text-white"
                                            >
                                                <i class="fa-solid fa-pen-to-square text-[10px]" aria-hidden="true"></i>
                                                Edit comment
                                            </a>
                                        @endcan

                                        @can('delete', $reply)
                                            <form
                                                action="{{ route('ticket.message.delete', ['id' => $reply->id]) }}"
                                                method="POST"
                                                class="inline-flex"
                                                onsubmit="return confirm('Delete this reply? This action cannot be undone.');"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-300 transition hover:text-red-200"
                                                >
                                                    <i class="fa-solid fa-trash-can text-[10px]" aria-hidden="true"></i>
                                                    Delete comment
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if ($replies->hasPages())
                        <div class="border-t border-white/[0.07] px-5 py-4 sm:px-6">
                            {{ $replies->links() }}
                        </div>
                    @endif
                @endif
            </section>

            {{-- Footer --}}
            <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-white/[0.07] pt-5">
                <p class="text-xs text-gray-500">Message record · ID {{ $ticketMessage->id }}</p>
                <div class="flex flex-wrap items-center gap-4">
                    @if ($canReply)
                        <a
                            href="{{ route('ticket.message.create', ['id' => $ticketMessage->ticket_id]) }}"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-300 transition hover:text-indigo-200"
                        >
                            <i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>
                            Add a new comment
                        </a>
                    @endif

                    <a
                        href="{{ url('/ticket/show-details/' . $ticketMessage->ticket_id) }}"
                        class="inline-flex items-center gap-2 text-sm font-medium text-gray-400 transition hover:text-white"
                    >
                        <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                        Return to ticket
                    </a>
                </div>
            </footer>
        </div>
    </div>
@endsection
