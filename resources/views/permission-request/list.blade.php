@extends('layouts.base')

@section('title', 'Permission Requests - Iris')

@section('content')
    @php
        $selectedStatuses = request()->query('status', []);
        $selectedStatuses = is_array($selectedStatuses)
            ? $selectedStatuses
            : [$selectedStatuses];

        $statusClasses = [
            'pending' => 'border-amber-500/20 bg-amber-500/10 text-amber-300',
            'approved' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',
            'rejected' => 'border-red-500/20 bg-red-500/10 text-red-300',
        ];

        $inputClass = 'block w-full rounded-lg border border-white/10 bg-gray-950/60 px-3 py-2.5 text-sm text-gray-100 outline-none placeholder:text-gray-600 focus:border-indigo-400/50 focus:ring-4 focus:ring-indigo-500/10';
        $selectClass = $inputClass;
        $labelClass = 'mb-1.5 block text-xs font-medium text-gray-400';
    @endphp

    <div class="mx-auto w-full max-w-7xl space-y-6 pb-12">

        {{-- Header --}}
        <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-xs font-medium text-gray-500">
                    <i class="fa-solid fa-shield-halved text-indigo-400" aria-hidden="true"></i>
                    <span>Permissions</span>
                    <i class="fa-solid fa-chevron-right text-[9px]" aria-hidden="true"></i>
                    <span>All Requests</span>
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-white">
                    Permission Requests
                </h1>

                <p class="mt-1 text-sm text-gray-400">
                    Search, filter and review permission requests.
                </p>
            </div>

            <a
                href="{{ route('permission.request.my') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-white/10 px-4 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/[0.04] hover:text-white"
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

        {{-- Filters --}}
        <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-900/70 shadow-lg shadow-black/10">
            <div class="flex items-center gap-3 border-b border-white/[0.07] px-5 py-5 sm:px-6">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-indigo-400/15 bg-indigo-500/10 text-indigo-300">
                    <i class="fa-solid fa-filter" aria-hidden="true"></i>
                </div>

                <div>
                    <h2 class="text-base font-semibold text-white">Filters</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Combine multiple criteria to narrow the results.
                    </p>
                </div>
            </div>

            <form
                action="{{ route('permission.request.list') }}"
                method="GET"
                class="space-y-6 p-5 sm:p-6"
            >
                {{-- User filters --}}
                <div>
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Requester
                    </h3>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <label for="user_id" class="{{ $labelClass }}">User ID</label>
                            <input
                                id="user_id"
                                type="number"
                                name="user_id"
                                min="1"
                                value="{{ request('user_id') }}"
                                placeholder="User ID"
                                class="{{ $inputClass }}"
                            >
                        </div>

                        <div>
                            <label for="user_name" class="{{ $labelClass }}">User name</label>
                            <input
                                id="user_name"
                                type="text"
                                name="user_name"
                                value="{{ request('user_name') }}"
                                placeholder="Search by name"
                                class="{{ $inputClass }}"
                            >
                            <select name="user_name_ignore_case" class="{{ $selectClass }} mt-2">
                                <option value="1" @selected(request('user_name_ignore_case', '1') === '1')>Ignore case</option>
                                <option value="0" @selected(request('user_name_ignore_case') === '0')>Match case</option>
                            </select>
                        </div>

                        <div>
                            <label for="user_email" class="{{ $labelClass }}">User email</label>
                            <input
                                id="user_email"
                                type="text"
                                name="user_email"
                                value="{{ request('user_email') }}"
                                placeholder="Search by email"
                                class="{{ $inputClass }}"
                            >
                            <select name="user_email_ignore_case" class="{{ $selectClass }} mt-2">
                                <option value="1" @selected(request('user_email_ignore_case', '1') === '1')>Ignore case</option>
                                <option value="0" @selected(request('user_email_ignore_case') === '0')>Match case</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="border-t border-white/[0.07]"></div>

                {{-- Permission filters --}}
                <div>
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Permission
                    </h3>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <label for="permission_id" class="{{ $labelClass }}">Permission</label>
                            <select id="permission_id" name="permission_id" class="{{ $selectClass }}">
                                <option value="">All permissions</option>

                                @foreach ($availablePermissions as $permission)
                                    <option
                                        value="{{ $permission->id }}"
                                        @selected((string) request('permission_id') === (string) $permission->id)
                                    >
                                        {{ $permission->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="permission_name" class="{{ $labelClass }}">Permission name</label>
                            <input
                                id="permission_name"
                                type="text"
                                name="permission_name"
                                value="{{ request('permission_name') }}"
                                placeholder="Search permission name"
                                class="{{ $inputClass }}"
                            >
                            <select name="permission_name_ignore_case" class="{{ $selectClass }} mt-2">
                                <option value="1" @selected(request('permission_name_ignore_case', '1') === '1')>Ignore case</option>
                                <option value="0" @selected(request('permission_name_ignore_case') === '0')>Match case</option>
                            </select>
                        </div>

                        <div>
                            <label for="permission_slug" class="{{ $labelClass }}">Permission slug</label>
                            <input
                                id="permission_slug"
                                type="text"
                                name="permission_slug"
                                value="{{ request('permission_slug') }}"
                                placeholder="e.g. ticket.create"
                                class="{{ $inputClass }}"
                            >
                            <select name="permission_slug_ignore_case" class="{{ $selectClass }} mt-2">
                                <option value="1" @selected(request('permission_slug_ignore_case', '1') === '1')>Ignore case</option>
                                <option value="0" @selected(request('permission_slug_ignore_case') === '0')>Match case</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="border-t border-white/[0.07]"></div>

                {{-- Request content and status --}}
                <div>
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Request Details
                    </h3>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <label for="status" class="{{ $labelClass }}">
                                Status (select multiple)
                            </label>

                            <select
                                id="status"
                                name="status[]"
                                multiple
                                size="4"
                                class="{{ $selectClass }}"
                            >
                                @foreach ($availableStatuses as $status)
                                    <option
                                        value="{{ $status }}"
                                        @selected(in_array($status, $selectedStatuses, true))
                                    >
                                        {{ ucwords(str_replace(['_', '-'], ' ', $status)) }}
                                    </option>
                                @endforeach
                            </select>

                            <p class="mt-1.5 text-xs text-gray-600">
                                Hold Ctrl or Cmd to select multiple statuses.
                            </p>
                        </div>

                        <div>
                            <label for="reason" class="{{ $labelClass }}">Justification</label>
                            <input
                                id="reason"
                                type="text"
                                name="reason"
                                value="{{ request('reason') }}"
                                placeholder="Search request justification"
                                class="{{ $inputClass }}"
                            >
                            <select name="reason_ignore_case" class="{{ $selectClass }} mt-2">
                                <option value="1" @selected(request('reason_ignore_case', '1') === '1')>Ignore case</option>
                                <option value="0" @selected(request('reason_ignore_case') === '0')>Match case</option>
                            </select>
                        </div>

                        <div>
                            <label for="viewed" class="{{ $labelClass }}">Viewed</label>
                            <select id="viewed" name="viewed" class="{{ $selectClass }}">
                                <option value="">All</option>
                                <option value="1" @selected(request('viewed') === '1')>Viewed</option>
                                <option value="0" @selected(request('viewed') === '0')>Not viewed</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="border-t border-white/[0.07]"></div>

                {{-- Review filters --}}
                <div>
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Review
                    </h3>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <label for="reviewed_by" class="{{ $labelClass }}">Reviewer user ID</label>
                            <input
                                id="reviewed_by"
                                type="number"
                                name="reviewed_by"
                                min="1"
                                value="{{ request('reviewed_by') }}"
                                placeholder="Reviewer ID"
                                class="{{ $inputClass }}"
                            >
                        </div>

                        <div>
                            <label for="review_comment" class="{{ $labelClass }}">Review comment</label>
                            <input
                                id="review_comment"
                                type="text"
                                name="review_comment"
                                value="{{ request('review_comment') }}"
                                placeholder="Search review comment"
                                class="{{ $inputClass }}"
                            >
                            <select name="review_comment_ignore_case" class="{{ $selectClass }} mt-2">
                                <option value="1" @selected(request('review_comment_ignore_case', '1') === '1')>Ignore case</option>
                                <option value="0" @selected(request('review_comment_ignore_case') === '0')>Match case</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="border-t border-white/[0.07]"></div>

                {{-- Date ranges --}}
                <div>
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Date Ranges
                    </h3>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label for="created_at_from" class="{{ $labelClass }}">Created from</label>
                            <input
                                id="created_at_from"
                                type="date"
                                name="created_at_from"
                                value="{{ request('created_at_from') }}"
                                class="{{ $inputClass }}"
                            >
                        </div>

                        <div>
                            <label for="created_at_to" class="{{ $labelClass }}">Created to</label>
                            <input
                                id="created_at_to"
                                type="date"
                                name="created_at_to"
                                value="{{ request('created_at_to') }}"
                                class="{{ $inputClass }}"
                            >
                        </div>

                        <div>
                            <label for="reviewed_at_from" class="{{ $labelClass }}">Reviewed from</label>
                            <input
                                id="reviewed_at_from"
                                type="date"
                                name="reviewed_at_from"
                                value="{{ request('reviewed_at_from') }}"
                                class="{{ $inputClass }}"
                            >
                        </div>

                        <div>
                            <label for="reviewed_at_to" class="{{ $labelClass }}">Reviewed to</label>
                            <input
                                id="reviewed_at_to"
                                type="date"
                                name="reviewed_at_to"
                                value="{{ request('reviewed_at_to') }}"
                                class="{{ $inputClass }}"
                            >
                        </div>
                    </div>
                </div>

                <div class="border-t border-white/[0.07]"></div>

                {{-- Sorting and pagination --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label for="sort_by" class="{{ $labelClass }}">Sort by</label>
                        <select id="sort_by" name="sort_by" class="{{ $selectClass }}">
                            @foreach ([
                                'id' => 'Request ID',
                                'user_id' => 'User ID',
                                'permission_id' => 'Permission ID',
                                'status' => 'Status',
                                'reviewed_by' => 'Reviewer ID',
                                'reviewed_at' => 'Review date',
                                'viewed_at' => 'Viewed date',
                                'created_at' => 'Creation date',
                                'updated_at' => 'Last updated',
                            ] as $column => $label)
                                <option
                                    value="{{ $column }}"
                                    @selected(request('sort_by', 'id') === $column)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="sort_order" class="{{ $labelClass }}">Sort order</label>
                        <select id="sort_order" name="sort_order" class="{{ $selectClass }}">
                            <option value="desc" @selected(request('sort_order', 'desc') === 'desc')>Descending</option>
                            <option value="asc" @selected(request('sort_order') === 'asc')>Ascending</option>
                        </select>
                    </div>

                    <div>
                        <label for="per_page" class="{{ $labelClass }}">Results per page</label>
                        <select id="per_page" name="per_page" class="{{ $selectClass }}">
                            @foreach ([15, 30, 50, 100] as $size)
                                <option
                                    value="{{ $size }}"
                                    @selected((int) request('per_page', 30) === $size)
                                >
                                    {{ $size }} results
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Filter actions --}}
                <div class="flex flex-col-reverse gap-3 border-t border-white/[0.07] pt-5 sm:flex-row sm:items-center sm:justify-end">
                    <a
                        href="{{ route('permission.request.list') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-white/10 px-5 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/[0.04] hover:text-white"
                    >
                        <i class="fa-solid fa-rotate-left text-xs" aria-hidden="true"></i>
                        Clear Filters
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/20"
                    >
                        <i class="fa-solid fa-magnifying-glass text-xs" aria-hidden="true"></i>
                        Apply Filters
                    </button>
                </div>
            </form>
        </section>

        {{-- Results --}}
        <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-900/70 shadow-lg shadow-black/10">
            <div class="flex flex-col gap-2 border-b border-white/[0.07] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 class="text-base font-semibold text-white">Request Results</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $permissionRequests->total() }} matching requests
                    </p>
                </div>

                <span class="text-xs text-gray-500">
                    Showing {{ $permissionRequests->firstItem() ?? 0 }}
                    – {{ $permissionRequests->lastItem() ?? 0 }}
                </span>
            </div>

            @if ($permissionRequests->isEmpty())
                <div class="px-6 py-14 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-white/[0.07] bg-white/[0.03] text-gray-500">
                        <i class="fa-solid fa-inbox text-xl" aria-hidden="true"></i>
                    </div>

                    <h3 class="mt-4 text-base font-semibold text-white">
                        No matching requests
                    </h3>

                    <p class="mt-2 text-sm text-gray-500">
                        Try changing or clearing some filters.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1150px] text-left">
                        <thead class="border-b border-white/[0.07] bg-white/[0.02]">
                            <tr class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <th class="px-5 py-4">Request</th>
                                <th class="px-5 py-4">Requester</th>
                                <th class="px-5 py-4">Permission</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Submitted</th>
                                <th class="px-5 py-4">Review</th>
                                <th class="px-5 py-4">Viewed</th>
                                <th class="px-5 py-4 text-right">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-white/[0.06]">
                            @foreach ($permissionRequests as $item)
                                @php
                                    $statusValue = strtolower(
                                        (string) $item->getRawOriginal('status')
                                    );

                                    $statusLabel = ucwords(
                                        str_replace(['_', '-'], ' ', $statusValue ?: 'pending')
                                    );

                                    $statusClass = $statusClasses[$statusValue]
                                        ?? 'border-white/10 bg-white/[0.04] text-gray-300';
                                @endphp

                                <tr class="align-top transition hover:bg-white/[0.02]">
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="font-mono text-sm font-semibold text-gray-200">
                                            #{{ $item->id }}
                                        </span>

                                        <p class="mt-1 text-xs text-gray-600">
                                            User #{{ $item->user_id }}
                                        </p>
                                    </td>

                                    <td class="max-w-[220px] px-5 py-4">
                                        <p class="break-words text-sm font-medium text-gray-200">
                                            {{ $item->user?->name ?? "User #{$item->user_id}" }}
                                        </p>

                                        <p class="mt-1 break-all text-xs text-gray-500">
                                            {{ $item->user?->email ?? '—' }}
                                        </p>
                                    </td>

                                    <td class="max-w-[220px] px-5 py-4">
                                        <p class="break-words text-sm text-gray-200">
                                            {{ $item->permission?->name ?? "Permission #{$item->permission_id}" }}
                                        </p>

                                        @if ($item->permission?->slug)
                                            <p class="mt-1 break-all font-mono text-xs text-gray-500">
                                                {{ $item->permission->slug }}
                                            </p>
                                        @endif
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs font-semibold {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4">
                                        <p class="text-sm text-gray-300">
                                            {{ $item->created_at?->format('d/m/Y') ?? '—' }}
                                        </p>

                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $item->created_at?->format('H:i') ?? '' }}
                                        </p>
                                    </td>

                                    <td class="max-w-[200px] px-5 py-4">
                                        @if ($item->reviewed_at)
                                            <p class="text-sm text-gray-300">
                                                {{ $item->reviewed_at->format('d/m/Y H:i') }}
                                            </p>

                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ $item->reviewer?->name ?? "User #{$item->reviewed_by}" }}
                                            </p>
                                        @else
                                            <span class="text-xs text-gray-600">Not reviewed</span>
                                        @endif

                                        @if ($item->review_comment)
                                            <p class="mt-2 break-words text-xs leading-5 text-gray-500">
                                                {{ \Illuminate\Support\Str::limit($item->review_comment, 90) }}
                                            </p>
                                        @endif
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4">
                                        @if ($item->viewed_at)
                                            <span class="inline-flex items-center gap-1.5 text-xs text-emerald-300">
                                                <i class="fa-solid fa-check" aria-hidden="true"></i>
                                                Viewed
                                            </span>

                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ $item->viewed_at->format('d/m/Y H:i') }}
                                            </p>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 text-xs text-gray-500">
                                                <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                                Not viewed
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="flex min-w-[110px] flex-wrap justify-end gap-2">
                                            <a
                                                href="{{ route('permission.request.show', ['id' => $item->id]) }}"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-sky-400/20 bg-sky-500/[0.07] px-2.5 py-2 text-xs font-medium text-sky-300 transition hover:bg-sky-500/[0.14]"
                                            >
                                                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                                Details
                                            </a>

                                            @can('permission.request.status.update')
                                                <a
                                                    href="{{ route('permission.request.change-status', ['id' => $item->id]) }}"
                                                    class="inline-flex items-center gap-1.5 rounded-lg border border-amber-400/20 bg-amber-500/[0.07] px-2.5 py-2 text-xs font-medium text-amber-300 transition hover:bg-amber-500/[0.14]"
                                                >
                                                    <i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i>
                                                    Status
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col gap-4 border-t border-white/[0.07] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <p class="text-xs text-gray-500">
                        Page {{ $permissionRequests->currentPage() }}
                        of {{ max(1, $permissionRequests->lastPage()) }}
                    </p>

                    {{ $permissionRequests->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection