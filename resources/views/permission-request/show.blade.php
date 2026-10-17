@extends('layouts.base')

@section('title', 'Permission Request Details - Iris')

@section('content')
    @php
        $permission = $permissionRequest->permission;

        // Read the database value without invoking the enum cast.
        $statusValue = strtolower(
            (string) $permissionRequest->getRawOriginal('status')
        );

        $statusLabel = ucwords(
            str_replace(['_', '-'], ' ', $statusValue ?: 'pending')
        );

        $statusClasses = match ($statusValue) {
            'approved' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',
            'rejected' => 'border-red-500/20 bg-red-500/10 text-red-300',
            'pending' => 'border-amber-500/20 bg-amber-500/10 text-amber-300',
            default => 'border-white/10 bg-white/[0.04] text-gray-300',
        };

        $statusIcon = match ($statusValue) {
            'approved' => 'fa-solid fa-circle-check',
            'rejected' => 'fa-solid fa-circle-xmark',
            'pending' => 'fa-regular fa-clock',
            default => 'fa-solid fa-circle-info',
        };

        $isPending = $statusValue === 'pending';

        $isOwner = (int) auth()->id() === (int) $permissionRequest->user_id;

        $canUpdateRequest = $isOwner
            && $isPending
            && auth()->user()->can('permission.request.update');

        $canDeleteRequest = $isOwner
            && $isPending
            && auth()->user()->can('permission.request.delete');

        $canChangeStatus = auth()->user()->can(
            'permission.request.status.update'
        );

        $reviewedAt = filled($permissionRequest->reviewed_at)
            ? \Illuminate\Support\Carbon::parse(
                $permissionRequest->reviewed_at
            )->format('d/m/Y \a\t H:i')
            : '—';
    @endphp

    <div class="mx-auto w-full max-w-5xl space-y-6 pb-12">

        {{-- Page header --}}
        <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="mb-2 flex flex-wrap items-center gap-2 text-xs font-medium text-gray-500">
                    <i class="fa-solid fa-shield-halved text-indigo-400" aria-hidden="true"></i>

                    <span>Permissions</span>

                    <i class="fa-solid fa-chevron-right text-[9px]" aria-hidden="true"></i>

                    <a
                        href="{{ route('permission.request.my') }}"
                        class="transition hover:text-gray-300"
                    >
                        My Requests
                    </a>

                    <i class="fa-solid fa-chevron-right text-[9px]" aria-hidden="true"></i>

                    <span>#{{ $permissionRequest->id }}</span>
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-white">
                    Permission Request Details
                </h1>

                <p class="mt-1 text-sm text-gray-400">
                    Review the request information, current status and decision details.
                </p>
            </div>

            <span class="inline-flex w-fit shrink-0 items-center gap-2 rounded-lg border px-3 py-2 text-xs font-semibold {{ $statusClasses }}">
                <i class="{{ $statusIcon }}" aria-hidden="true"></i>
                {{ $statusLabel }}
            </span>
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

        {{-- Back button above the content --}}
        <div>
            <a
                href="{{ route('permission.request.my') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-white/10 px-4 py-2.5 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:bg-white/[0.04] hover:text-white"
            >
                <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                Back to My Requests
            </a>
        </div>

        {{-- Request summary --}}
        <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-900/70 shadow-lg shadow-black/10">

            <div class="flex flex-col gap-4 border-b border-white/[0.07] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="flex min-w-0 items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-indigo-400/15 bg-indigo-500/10 text-indigo-300">
                        <i class="fa-solid fa-file-circle-question text-lg" aria-hidden="true"></i>
                    </div>

                    <div class="min-w-0">
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            Request #{{ $permissionRequest->id }}
                        </p>

                        <h2 class="mt-1 break-words text-lg font-semibold text-white">
                            {{ $permission?->name ?? "Permission #{$permissionRequest->permission_id}" }}
                        </h2>

                        <p class="mt-1 text-xs text-gray-500">
                            Submitted
                            {{ $permissionRequest->created_at?->format('d/m/Y \a\t H:i') ?? '—' }}
                        </p>
                    </div>
                </div>

                <span class="inline-flex w-fit shrink-0 items-center gap-2 rounded-lg border px-3 py-2 text-xs font-semibold {{ $statusClasses }}">
                    <i class="{{ $statusIcon }}" aria-hidden="true"></i>
                    {{ $statusLabel }}
                </span>
            </div>

            {{-- Metadata --}}
            <div class="grid grid-cols-1 divide-y divide-white/[0.06] sm:grid-cols-2 sm:divide-y-0">

                <div class="flex items-start gap-3 px-5 py-4 sm:border-r sm:border-white/[0.06] sm:px-6">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-indigo-400/15 bg-indigo-500/[0.07] text-indigo-300">
                        <i class="fa-solid fa-key" aria-hidden="true"></i>
                    </div>

                    <div class="min-w-0">
                        <p class="text-xs text-gray-500">Permission</p>

                        <p class="mt-1 break-words text-sm font-semibold text-gray-200">
                            {{ $permission?->name ?? "Permission #{$permissionRequest->permission_id}" }}
                        </p>

                        <p class="mt-1 text-xs text-gray-500">
                            ID: {{ $permissionRequest->permission_id }}
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-3 px-5 py-4 sm:px-6">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-white/[0.07] bg-white/[0.03] text-gray-400">
                        <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Last updated</p>

                        <p class="mt-1 text-sm font-semibold text-gray-200">
                            {{ $permissionRequest->updated_at?->format('d/m/Y \a\t H:i') ?? '—' }}
                        </p>
                    </div>
                </div>

                @if ($permission && filled($permission->slug))
                    <div class="flex items-start gap-3 border-t border-white/[0.06] px-5 py-4 sm:px-6">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-white/[0.07] bg-white/[0.03] text-gray-400">
                            <i class="fa-solid fa-code" aria-hidden="true"></i>
                        </div>

                        <div class="min-w-0">
                            <p class="text-xs text-gray-500">Permission slug</p>

                            <p class="mt-1 break-all font-mono text-sm text-indigo-300">
                                {{ $permission->slug }}
                            </p>
                        </div>
                    </div>
                @endif

                @if ($permission && filled($permission->module))
                    <div class="flex items-start gap-3 border-t border-white/[0.06] px-5 py-4 sm:px-6">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-white/[0.07] bg-white/[0.03] text-gray-400">
                            <i class="fa-solid fa-cubes" aria-hidden="true"></i>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500">Module</p>

                            <p class="mt-1 text-sm font-semibold text-gray-200">
                                {{ $permission->module }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Permission description --}}
            @if ($permission && filled($permission->description))
                <div class="border-t border-white/[0.07] px-5 py-5 sm:px-6">
                    <div class="mb-2 flex items-center gap-2">
                        <i class="fa-regular fa-file-lines text-xs text-gray-500" aria-hidden="true"></i>

                        <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Permission Description
                        </h3>
                    </div>

                    <p class="whitespace-pre-line break-words text-sm leading-6 text-gray-300">{{ $permission->description }}</p>
                </div>
            @endif
        </section>

        {{-- Request justification --}}
        <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-900/70 shadow-lg shadow-black/10">
            <div class="border-b border-white/[0.07] px-5 py-5 sm:px-6">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-indigo-400/15 bg-indigo-500/10 text-indigo-300">
                        <i class="fa-solid fa-align-left" aria-hidden="true"></i>
                    </div>

                    <div>
                        <h2 class="text-base font-semibold text-white">
                            Request Justification
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Reason provided by the requester.
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-5 sm:p-6">
                <div class="rounded-xl border border-white/[0.07] bg-gray-950/40 p-4 sm:p-5">
                    <p class="whitespace-pre-line break-words text-sm leading-7 text-gray-300">{{ $permissionRequest->reason }}</p>
                </div>
            </div>
        </section>

        {{-- Review information --}}
        <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-900/70 shadow-lg shadow-black/10">
            <div class="border-b border-white/[0.07] px-5 py-5 sm:px-6">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/[0.07] bg-white/[0.03] text-gray-400">
                        <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                    </div>

                    <div>
                        <h2 class="text-base font-semibold text-white">
                            Review Information
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Details about the decision made on this request.
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-5 p-5 sm:p-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-white/[0.07] bg-white/[0.02] p-4">
                        <p class="text-xs text-gray-500">Reviewed by</p>

                        <p class="mt-2 text-sm font-semibold text-gray-200">
                            {{ $permissionRequest->reviewed_by ? "User #{$permissionRequest->reviewed_by}" : 'Not reviewed yet' }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-white/[0.07] bg-white/[0.02] p-4">
                        <p class="text-xs text-gray-500">Review date</p>

                        <p class="mt-2 text-sm font-semibold text-gray-200">
                            {{ $reviewedAt }}
                        </p>
                    </div>
                </div>

                <div>
                    <h3 class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Review Comment
                    </h3>

                    @if (filled($permissionRequest->review_comment))
                        <div class="rounded-xl border border-white/[0.07] bg-gray-950/40 p-4">
                            <p class="whitespace-pre-line break-words text-sm leading-6 text-gray-300">{{ $permissionRequest->review_comment }}</p>
                        </div>
                    @else
                        <p class="rounded-xl border border-dashed border-white/10 px-4 py-5 text-sm text-gray-500">
                            No review comment has been provided.
                        </p>
                    @endif
                </div>
            </div>
        </section>

        {{-- Actions --}}
        <section class="rounded-2xl border border-white/10 bg-gray-900/70 p-5 shadow-lg shadow-black/10 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-white">
                        Request Actions
                    </h2>

                    <p class="mt-1 text-xs leading-5 text-gray-500">
                        Available actions depend on the request status and your permissions.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">

                    {{-- Update --}}
                    @if ($canUpdateRequest)
                        <a
                            href="{{ route('permission.request.edit', ['id' => $permissionRequest->id]) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-indigo-400/20 bg-indigo-500/[0.07] px-4 py-2.5 text-sm font-medium text-indigo-300 transition hover:border-indigo-400/40 hover:bg-indigo-500/[0.14]"
                        >
                            <i class="fa-solid fa-pen-to-square text-xs" aria-hidden="true"></i>
                            Update
                        </a>
                    @endif

                    {{-- Delete --}}
                    @if ($canDeleteRequest)
                        <form
                            action="{{ route('permission.request.delete', ['id' => $permissionRequest->id]) }}"
                            method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this permission request? This action cannot be undone.');"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-lg border border-red-400/20 bg-red-500/[0.07] px-4 py-2.5 text-sm font-medium text-red-300 transition hover:border-red-400/40 hover:bg-red-500/[0.14]"
                            >
                                <i class="fa-solid fa-trash-can text-xs" aria-hidden="true"></i>
                                Delete
                            </button>
                        </form>
                    @endif

                    {{-- Change status: users with review permission --}}
                    @if ($canChangeStatus)
                        <a
                            href="{{ route('permission.request.change-status', ['id' => $permissionRequest->id]) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-amber-400/20 bg-amber-500/[0.07] px-4 py-2.5 text-sm font-medium text-amber-300 transition hover:border-amber-400/40 hover:bg-amber-500/[0.14]"
                        >
                            <i class="fa-solid fa-arrows-rotate text-xs" aria-hidden="true"></i>
                            Change Status
                        </a>
                    @endif

                    {{-- Back to list --}}
                    <a
                        href="{{ route('permission.request.my') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-white/10 px-4 py-2.5 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:bg-white/[0.04] hover:text-white"
                    >
                        <i class="fa-solid fa-list text-xs" aria-hidden="true"></i>
                        Back to List
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection