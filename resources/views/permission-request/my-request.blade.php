@extends('layouts.base')

@section('title', 'My Permission Requests - Iris')

@section('content')
    <div class="mx-auto w-full max-w-7xl space-y-6 pb-12">

        {{-- Page header --}}
        <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-xs font-medium text-gray-500">
                    <i class="fa-solid fa-shield-halved text-indigo-400" aria-hidden="true"></i>
                    <span>Permissions</span>
                    <i class="fa-solid fa-chevron-right text-[9px]" aria-hidden="true"></i>
                    <span>My Requests</span>
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-white">
                    My Permission Requests
                </h1>

                <p class="mt-1 text-sm text-gray-400">
                    Track your requests and manage pending submissions.
                </p>
            </div>

            <a
                href="{{ route('permission.request.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-500/10 transition hover:bg-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/20"
            >
                <i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>
                New Request
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

        {{-- Back button: above the list --}}
        <div>
            <a
                href="{{ url()->previous() }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-white/10 px-4 py-2.5 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:bg-white/[0.04] hover:text-white"
            >
                <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                Back
            </a>
        </div>

        {{-- Summary --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-white/10 bg-gray-900/70 p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-400">Total requests</p>
                    <i class="fa-solid fa-layer-group text-indigo-400" aria-hidden="true"></i>
                </div>

                <p class="mt-3 text-2xl font-bold text-white">
                    {{ $permissionRequests->total() }}
                </p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-gray-900/70 p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-400">Pending on this page</p>
                    <i class="fa-regular fa-clock text-amber-400" aria-hidden="true"></i>
                </div>

                <p class="mt-3 text-2xl font-bold text-white">
                    {{ $permissionRequests->getCollection()->filter(function ($item) {
                        $value = $item->status instanceof \BackedEnum
                            ? $item->status->value
                            : (string) $item->status;

                        return strtolower($value) === 'pending';
                    })->count() }}
                </p>
            </div>

            <div class="rounded-2xl border border-white/10 bg-gray-900/70 p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-400">Current page</p>
                    <i class="fa-solid fa-list-ol text-sky-400" aria-hidden="true"></i>
                </div>

                <p class="mt-3 text-2xl font-bold text-white">
                    {{ $permissionRequests->currentPage() }}
                    <span class="text-base font-medium text-gray-500">
                        / {{ max(1, $permissionRequests->lastPage()) }}
                    </span>
                </p>
            </div>
        </div>

        {{-- Request list --}}
        <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-900/70 shadow-lg shadow-black/10">

            <div class="flex flex-col gap-3 border-b border-white/[0.07] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-indigo-400/15 bg-indigo-500/10 text-indigo-300">
                        <i class="fa-solid fa-list-check" aria-hidden="true"></i>
                    </div>

                    <div>
                        <h2 class="text-base font-semibold text-white">
                            Request History
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Review the status and available actions for each request.
                        </p>
                    </div>
                </div>

                <span class="text-xs text-gray-500">
                    {{ $permissionRequests->firstItem() ?? 0 }}–{{ $permissionRequests->lastItem() ?? 0 }}
                    of {{ $permissionRequests->total() }} requests
                </span>
            </div>

            @if ($permissionRequests->isEmpty())
                <div class="px-6 py-16 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-white/[0.07] bg-white/[0.03] text-gray-500">
                        <i class="fa-solid fa-inbox text-xl" aria-hidden="true"></i>
                    </div>

                    <h3 class="mt-4 text-base font-semibold text-white">
                        No permission requests yet
                    </h3>

                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-gray-500">
                        You haven't submitted any permission requests. Create a request to get started.
                    </p>

                    <a
                        href="{{ route('permission.request.create') }}"
                        class="mt-5 inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-400"
                    >
                        <i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>
                        Create Request
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1050px] text-left">
                        <thead class="border-b border-white/[0.07] bg-white/[0.02]">
                            <tr class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <th class="px-5 py-4">Request</th>
                                <th class="px-5 py-4">Permission</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Submitted</th>
                                <th class="px-5 py-4">Justification</th>
                                <th class="px-5 py-4 text-right">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-white/[0.06]">
                            @foreach ($permissionRequests as $permissionRequest)
                                @php
                                    $rawStatus = $permissionRequest->status;

                                    $statusValue = $rawStatus instanceof \BackedEnum
                                        ? $rawStatus->value
                                        : (string) $rawStatus;

                                    $statusKey = strtolower($statusValue);

                                    $statusLabel = ucwords(
                                        str_replace(['_', '-'], ' ', $statusValue ?: 'pending')
                                    );

                                    $statusClasses = match ($statusKey) {
                                        'approved' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',
                                        'rejected' => 'border-red-500/20 bg-red-500/10 text-red-300',
                                        'pending' => 'border-amber-500/20 bg-amber-500/10 text-amber-300',
                                        default => 'border-white/10 bg-white/[0.04] text-gray-300',
                                    };

                                    $statusIcon = match ($statusKey) {
                                        'approved' => 'fa-solid fa-circle-check',
                                        'rejected' => 'fa-solid fa-circle-xmark',
                                        'pending' => 'fa-regular fa-clock',
                                        default => 'fa-solid fa-circle-info',
                                    };

                                    $isPending = $statusKey === 'pending';

                                    $permissionName = $permissionRequest->permission?->name
                                        ?? "Permission #{$permissionRequest->permission_id}";

                                    $canUpdateRequest = $isPending
                                        && auth()->user()->can('permission.request.update');

                                    $canDeleteRequest = $isPending
                                        && auth()->user()->can('permission.request.delete');

                                    $canMarkViewed = auth()->user()->can('permission.request.mark-viewed')
                                        && empty($permissionRequest->viewed_at);
                                @endphp

                                <tr class="align-top transition hover:bg-white/[0.02]">
                                    {{-- Request ID --}}
                                    <td class="whitespace-nowrap px-5 py-5">
                                        <span class="font-mono text-sm font-semibold text-gray-200">
                                            #{{ $permissionRequest->id }}
                                        </span>

                                        <p class="mt-1 text-xs text-gray-600">
                                            User #{{ $permissionRequest->user_id }}
                                        </p>
                                    </td>

                                    {{-- Permission --}}
                                    <td class="max-w-[220px] px-5 py-5">
                                        <div class="flex items-start gap-2.5">
                                            <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-indigo-400/15 bg-indigo-500/[0.07] text-indigo-300">
                                                <i class="fa-solid fa-key text-xs" aria-hidden="true"></i>
                                            </div>

                                            <div class="min-w-0">
                                                <p class="break-words text-sm font-medium text-gray-200">
                                                    {{ $permissionName }}
                                                </p>

                                                <p class="mt-1 text-xs text-gray-500">
                                                    Permission ID: {{ $permissionRequest->permission_id }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td class="whitespace-nowrap px-5 py-5">
                                        <span class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs font-semibold {{ $statusClasses }}">
                                            <i class="{{ $statusIcon }}" aria-hidden="true"></i>
                                            {{ $statusLabel }}
                                        </span>

                                        @if ($permissionRequest->reviewed_at)
                                            <p class="mt-2 text-xs text-gray-500">
                                                Reviewed
                                                {{ $permissionRequest->reviewed_at->format('d/m/Y') }}
                                            </p>
                                        @endif
                                    </td>

                                    {{-- Date --}}
                                    <td class="whitespace-nowrap px-5 py-5">
                                        <p class="text-sm text-gray-300">
                                            {{ $permissionRequest->created_at?->format('d/m/Y') ?? '—' }}
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $permissionRequest->created_at?->format('H:i') ?? '' }}
                                        </p>
                                    </td>

                                    {{-- Reason --}}
                                    <td class="max-w-[260px] px-5 py-5">
                                        <p class="whitespace-pre-line break-words text-sm leading-5 text-gray-400">
                                            {{ \Illuminate\Support\Str::limit($permissionRequest->reason, 100) }}
                                        </p>

                                        @if ($permissionRequest->review_comment)
                                            <p class="mt-2 border-l-2 border-indigo-400/30 pl-2 text-xs leading-5 text-gray-500">
                                                Review: {{ \Illuminate\Support\Str::limit($permissionRequest->review_comment, 80) }}
                                            </p>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-5 py-5">
                                        <div class="flex min-w-[170px] flex-wrap justify-end gap-2">

                                            {{-- View details --}}
                                            @can('permission.request.view')
                                                <a
                                                    href="{{ route('permission.request.show', ['id' => $permissionRequest->id]) }}"
                                                    title="View details"
                                                    class="inline-flex items-center gap-1.5 rounded-lg border border-sky-400/20 bg-sky-500/[0.07] px-2.5 py-2 text-xs font-medium text-sky-300 transition hover:border-sky-400/40 hover:bg-sky-500/[0.14]"
                                                >
                                                    <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                                    Details
                                                </a>
                                            @endcan

                                            {{-- Update --}}
                                            @if ($canUpdateRequest)
                                                <a
                                                    href="{{ route('permission.request.edit', ['id' => $permissionRequest->id]) }}"
                                                    title="Update request"
                                                    class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-400/20 bg-indigo-500/[0.07] px-2.5 py-2 text-xs font-medium text-indigo-300 transition hover:border-indigo-400/40 hover:bg-indigo-500/[0.14]"
                                                >
                                                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
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
                                                        title="Delete request"
                                                        class="inline-flex items-center gap-1.5 rounded-lg border border-red-400/20 bg-red-500/[0.07] px-2.5 py-2 text-xs font-medium text-red-300 transition hover:border-red-400/40 hover:bg-red-500/[0.14]"
                                                    >
                                                        <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                                        Delete
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Mark as viewed --}}
                                            @if ($canMarkViewed)
                                                <form
                                                    action="{{ route('permission.request.mark-viewed', ['id' => $permissionRequest->id]) }}"
                                                    method="POST"
                                                >
                                                    @csrf

                                                    <button
                                                        type="submit"
                                                        title="Mark as viewed"
                                                        class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/[0.03] px-2.5 py-2 text-xs font-medium text-gray-300 transition hover:bg-white/[0.08] hover:text-white"
                                                    >
                                                        <i class="fa-solid fa-check-double" aria-hidden="true"></i>
                                                        Mark viewed
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Change status: reviewers only --}}
                                            @can('permission.request.status.update')
                                                <a
                                                    href="{{ route('permission.request.change-status', ['id' => $permissionRequest->id]) }}"
                                                    title="Change request status"
                                                    class="inline-flex items-center gap-1.5 rounded-lg border border-amber-400/20 bg-amber-500/[0.07] px-2.5 py-2 text-xs font-medium text-amber-300 transition hover:border-amber-400/40 hover:bg-amber-500/[0.14]"
                                                >
                                                    <i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i>
                                                    Change status
                                                </a>
                                            @endcan

                                            {{-- No actions available --}}
                                            @if (
                                                !auth()->user()->can('permission.request.view')
                                                && !$canUpdateRequest
                                                && !$canDeleteRequest
                                                && !$canMarkViewed
                                                && !auth()->user()->can('permission.request.status.update')
                                            )
                                                <span class="text-xs text-gray-600">
                                                    No actions available
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($permissionRequests->hasPages())
                    <div class="flex flex-col gap-4 border-t border-white/[0.07] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <p class="text-xs text-gray-500">
                            Showing {{ $permissionRequests->firstItem() ?? 0 }}
                            to {{ $permissionRequests->lastItem() ?? 0 }}
                            of {{ $permissionRequests->total() }} results
                        </p>

                        <div>
                            {{ $permissionRequests->links() }}
                        </div>
                    </div>
                @endif
            @endif
        </section>
    </div>
@endsection