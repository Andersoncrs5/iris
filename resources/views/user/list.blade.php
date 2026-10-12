@extends('layouts.base')

@section('title', 'Users | Iris')

@section('content')
<style>
    [x-cloak] { display: none !important; }
</style>

@php
    $statusLabels = [
        'ACTIVE' => 'Active',
        'INACTIVE' => 'Inactive',
        'SUSPENDED' => 'Suspended',
    ];

    $statusStyles = [
        'ACTIVE' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',
        'INACTIVE' => 'border-gray-500/20 bg-gray-500/10 text-gray-400',
        'SUSPENDED' => 'border-red-500/20 bg-red-500/10 text-red-300',
    ];

    $chips = [];

    if (filled(request('id'))) {
        $chips['id'] = 'ID: ' . request('id');
    }
    if (filled(request('name'))) {
        $chips['name'] = 'Name: ' . request('name');
    }
    if (filled(request('full_name'))) {
        $chips['full_name'] = 'Full name: ' . request('full_name');
    }
    if (filled(request('email'))) {
        $chips['email'] = 'Email: ' . request('email');
    }
    if (filled(request('status'))) {
        $chips['status'] = 'Status: ' . ($statusLabels[request('status')] ?? request('status'));
    }
    if (request()->has('email_verified') && request('email_verified') !== '') {
        $chips['email_verified'] = request('email_verified') === '1'
            ? 'Email verified'
            : 'Email not verified';
    }

    $dateFilters = [
        'email_verified_at_from' => 'Verified from',
        'email_verified_at_to' => 'Verified until',
        'last_login_at_from' => 'Last login from',
        'last_login_at_to' => 'Last login until',
        'created_at_from' => 'Created from',
        'created_at_to' => 'Created until',
        'updated_at_from' => 'Updated from',
        'updated_at_to' => 'Updated until',
    ];

    foreach ($dateFilters as $key => $label) {
        if (filled(request($key))) {
            $chips[$key] = $label . ': ' . request($key);
        }
    }

    if (in_array(request('trashed'), ['with', 'only'], true)) {
        $chips['trashed'] = request('trashed') === 'with'
            ? 'All records'
            : 'Deleted records only';
    }

    $advancedKeys = [
        'id', 'full_name', 'status', 'email_verified',
        'email_verified_at_from', 'email_verified_at_to',
        'last_login_at_from', 'last_login_at_to',
        'created_at_from', 'created_at_to',
        'updated_at_from', 'updated_at_to', 'trashed',
    ];

    $advancedCount = collect($advancedKeys)
        ->filter(fn ($key) => request()->has($key) && request($key) !== '')
        ->count();

    $sortBy = request('sort_by', 'id');
    $sortOrder = strtolower((string) request('sort_order', 'desc'));

    $sortUrl = fn (string $column) => route('user.list', array_merge(
        request()->except(['page']),
        [
            'sort_by' => $column,
            'sort_order' => ($sortBy === $column && $sortOrder === 'asc') ? 'desc' : 'asc',
        ]
    ));

    $ariaSort = fn (string $column) => $sortBy === $column
        ? ($sortOrder === 'asc' ? 'ascending' : 'descending')
        : 'none';

    $field = 'h-11 w-full rounded-xl border border-white/10 bg-white/[0.04] px-3.5 text-sm text-white placeholder:text-gray-500 transition hover:border-white/20 focus:border-indigo-400 focus:bg-white/[0.06] focus:outline-none focus:ring-4 focus:ring-indigo-500/15';
    $label = 'mb-1.5 block text-xs font-medium text-gray-400';
    $pageButton = 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-2.5 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400/60';
@endphp

<div
    class="relative isolate min-h-screen px-4 py-8 text-gray-100 [color-scheme:dark] sm:px-6 lg:px-8"
    x-data="{ advanced: @js($advancedCount > 0) }"
>
    <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-[radial-gradient(ellipse_at_50%_0%,rgba(99,102,241,.16),transparent_70%)]" aria-hidden="true"></div>

    <div class="mx-auto max-w-7xl">
        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <nav class="mb-3 flex items-center gap-2 text-sm text-gray-500" aria-label="Breadcrumb">
                    <a href="{{ route('dashboard') }}" class="transition-colors hover:text-gray-300" aria-label="Dashboard">
                        <i class="fa-solid fa-house text-xs" aria-hidden="true"></i>
                    </a>
                    <span aria-hidden="true">/</span>
                    <span>Administration</span>
                    <span aria-hidden="true">/</span>
                    <span class="text-indigo-400" aria-current="page">Users</span>
                </nav>

                <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    Users
                </h1>
                <p class="mt-2 max-w-xl text-sm leading-6 text-gray-400">
                    Manage user accounts, account status, verification, and recent activity.
                </p>
            </div>

            <div class="inline-flex h-11 items-center gap-2 self-start rounded-xl border border-white/10 bg-white/[0.03] px-4 text-sm text-gray-300 sm:self-auto">
                <i class="fa-solid fa-users text-indigo-300" aria-hidden="true"></i>
                <span>{{ number_format($users->total()) }} {{ \Illuminate\Support\Str::plural('user', $users->total()) }}</span>
            </div>
        </div>

        {{-- Feedback --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition.opacity role="status" class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">
                <i class="fa-solid fa-circle-check mt-0.5" aria-hidden="true"></i>
                <p class="flex-1">{{ session('success') }}</p>
                <button type="button" @click="show = false" aria-label="Dismiss" class="text-emerald-300/60 transition hover:text-emerald-200">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" x-transition.opacity role="alert" class="mb-5 flex items-start gap-3 rounded-xl border border-red-500/25 bg-red-500/10 p-4 text-sm text-red-300">
                <i class="fa-solid fa-circle-exclamation mt-0.5" aria-hidden="true"></i>
                <p class="flex-1">{{ session('error') }}</p>
                <button type="button" @click="show = false" aria-label="Dismiss" class="text-red-300/60 transition hover:text-red-200">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
        @endif

        {{-- Filters --}}
        <section class="mb-4 rounded-2xl border border-white/10 bg-gray-950/60 p-4 shadow-xl shadow-black/20 backdrop-blur-xl sm:p-5">
            <form id="user-filters" action="{{ route('user.list') }}" method="GET">
                @if (request()->filled('sort_by'))
                    <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                    <input type="hidden" name="sort_order" value="{{ $sortOrder }}">
                @endif

                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="group relative flex-1">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <i class="fa-solid fa-magnifying-glass text-sm text-gray-500 transition-colors group-focus-within:text-indigo-400" aria-hidden="true"></i>
                        </div>
                        <label for="name" class="sr-only">Search user name</label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ request('name') }}"
                            placeholder="Search by display name"
                            class="{{ $field }} pl-10"
                        />
                    </div>

                    <div class="group relative flex-1">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <i class="fa-regular fa-envelope text-sm text-gray-500 transition-colors group-focus-within:text-indigo-400" aria-hidden="true"></i>
                        </div>
                        <label for="email" class="sr-only">Search email</label>
                        <input
                            type="text"
                            name="email"
                            id="email"
                            value="{{ request('email') }}"
                            placeholder="Search by email"
                            class="{{ $field }} pl-10"
                        />
                    </div>

                    <div class="flex gap-2">
                        <button
                            type="button"
                            @click="advanced = !advanced"
                            :aria-expanded="advanced.toString()"
                            aria-controls="advanced-filters"
                            class="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-xl border border-white/10 px-4 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:bg-white/[0.05] hover:text-white focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/20 sm:flex-none"
                        >
                            <i class="fa-solid fa-sliders text-xs" aria-hidden="true"></i>
                            Filters
                            @if ($advancedCount > 0)
                                <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-indigo-500 px-1.5 text-[11px] font-semibold text-white">{{ $advancedCount }}</span>
                            @endif
                        </button>

                        <button type="submit" class="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 text-sm font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/40 active:scale-[0.99] sm:flex-none">
                            <i class="fa-solid fa-magnifying-glass text-xs" aria-hidden="true"></i>
                            Apply
                        </button>
                    </div>
                </div>

                {{-- Advanced filters --}}
                <div
                    id="advanced-filters"
                    x-show="advanced"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="mt-4 grid grid-cols-1 gap-4 border-t border-white/[0.08] pt-4 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <div>
                        <label for="id" class="{{ $label }}">User ID</label>
                        <input type="number" min="1" name="id" id="id" value="{{ request('id') }}" placeholder="Snowflake ID" class="{{ $field }}">
                    </div>

                    <div>
                        <label for="full_name" class="{{ $label }}">Full name</label>
                        <input type="text" name="full_name" id="full_name" value="{{ request('full_name') }}" placeholder="Search full name" class="{{ $field }}">
                    </div>

                    <div>
                        <label for="status" class="{{ $label }}">Account status</label>
                        <select name="status" id="status" class="{{ $field }} [&>option]:bg-gray-900">
                            <option value="">Any status</option>
                            <option value="ACTIVE" @selected(request('status') === 'ACTIVE')>Active</option>
                            <option value="INACTIVE" @selected(request('status') === 'INACTIVE')>Inactive</option>
                            <option value="SUSPENDED" @selected(request('status') === 'SUSPENDED')>Suspended</option>
                        </select>
                    </div>

                    <div>
                        <label for="email_verified" class="{{ $label }}">Email verification</label>
                        <select name="email_verified" id="email_verified" class="{{ $field }} [&>option]:bg-gray-900">
                            <option value="" @selected(request('email_verified', '') === '')>Any</option>
                            <option value="1" @selected(request('email_verified') === '1')>Verified</option>
                            <option value="0" @selected(request('email_verified') === '0')>Not verified</option>
                        </select>
                    </div>

                    <div>
                        <label for="email_verified_at_from" class="{{ $label }}">Verified from</label>
                        <input type="date" name="email_verified_at_from" id="email_verified_at_from" value="{{ request('email_verified_at_from') }}" class="{{ $field }}">
                    </div>
                    <div>
                        <label for="email_verified_at_to" class="{{ $label }}">Verified until</label>
                        <input type="date" name="email_verified_at_to" id="email_verified_at_to" value="{{ request('email_verified_at_to') }}" class="{{ $field }}">
                    </div>

                    <div>
                        <label for="last_login_at_from" class="{{ $label }}">Last login from</label>
                        <input type="date" name="last_login_at_from" id="last_login_at_from" value="{{ request('last_login_at_from') }}" class="{{ $field }}">
                    </div>
                    <div>
                        <label for="last_login_at_to" class="{{ $label }}">Last login until</label>
                        <input type="date" name="last_login_at_to" id="last_login_at_to" value="{{ request('last_login_at_to') }}" class="{{ $field }}">
                    </div>

                    <div>
                        <label for="created_at_from" class="{{ $label }}">Created from</label>
                        <input type="date" name="created_at_from" id="created_at_from" value="{{ request('created_at_from') }}" class="{{ $field }}">
                    </div>
                    <div>
                        <label for="created_at_to" class="{{ $label }}">Created until</label>
                        <input type="date" name="created_at_to" id="created_at_to" value="{{ request('created_at_to') }}" class="{{ $field }}">
                    </div>

                    <div>
                        <label for="updated_at_from" class="{{ $label }}">Updated from</label>
                        <input type="date" name="updated_at_from" id="updated_at_from" value="{{ request('updated_at_from') }}" class="{{ $field }}">
                    </div>
                    <div>
                        <label for="updated_at_to" class="{{ $label }}">Updated until</label>
                        <input type="date" name="updated_at_to" id="updated_at_to" value="{{ request('updated_at_to') }}" class="{{ $field }}">
                    </div>

                    <div>
                        <label for="trashed" class="{{ $label }}">Records</label>
                        <select name="trashed" id="trashed" class="{{ $field }} [&>option]:bg-gray-900">
                            <option value="" @selected(request('trashed', '') === '')>Active records only</option>
                            <option value="with" @selected(request('trashed') === 'with')>All records</option>
                            <option value="only" @selected(request('trashed') === 'only')>Deleted records only</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-4">
                        <p class="mb-2 text-xs font-medium text-gray-500">Text matching</p>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            @foreach ([
                                'name_ignore_case' => 'Display name',
                                'full_name_ignore_case' => 'Full name',
                                'email_ignore_case' => 'Email',
                            ] as $caseKey => $caseLabel)
                                <div>
                                    <label for="{{ $caseKey }}" class="{{ $label }}">{{ $caseLabel }}</label>
                                    <select name="{{ $caseKey }}" id="{{ $caseKey }}" class="{{ $field }} [&>option]:bg-gray-900">
                                        <option value="1" @selected(request($caseKey, '1') === '1')>Ignore case</option>
                                        <option value="0" @selected(request($caseKey) === '0')>Case-sensitive</option>
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </form>
        </section>

        {{-- Active filters --}}
        @if ($chips)
            <div class="mb-4 flex flex-wrap items-center gap-2" aria-label="Active filters">
                @foreach ($chips as $key => $chipLabel)
                    <a
                        href="{{ route('user.list', request()->except([$key, 'page'])) }}"
                        class="group inline-flex items-center gap-2 rounded-full border border-indigo-400/20 bg-indigo-500/10 py-1 pl-3 pr-2.5 text-xs font-medium text-indigo-200 transition hover:border-indigo-400/40 hover:bg-indigo-500/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400/60"
                        title="Remove this filter"
                    >
                        {{ $chipLabel }}
                        <i class="fa-solid fa-xmark text-[10px] text-indigo-300/70 group-hover:text-white" aria-hidden="true"></i>
                        <span class="sr-only">Remove filter</span>
                    </a>
                @endforeach

                <a href="{{ route('user.list') }}" class="ml-1 text-xs font-medium text-gray-400 transition hover:text-white focus:outline-none focus-visible:underline">
                    Clear all
                </a>
            </div>
        @endif

        {{-- Users table --}}
        <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-950/60 shadow-xl shadow-black/20 backdrop-blur-xl">
            <div class="flex items-center justify-between gap-4 border-b border-white/[0.08] px-5 py-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-300">
                        <i class="fa-solid fa-users" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-white">User directory</h2>
                        <p class="mt-0.5 text-xs text-gray-500">
                            {{ number_format($users->total()) }} {{ \Illuminate\Support\Str::plural('user', $users->total()) }} found
                        </p>
                    </div>
                </div>
                <span class="hidden text-xs text-gray-500 sm:block">
                    Page {{ $users->currentPage() }} of {{ max(1, $users->lastPage()) }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1050px] text-left text-sm">
                    <thead class="border-b border-white/[0.08] bg-white/[0.02] text-xs text-gray-500">
                        <tr>
                            @foreach ([
                                'id' => 'User',
                                'name' => 'Display name',
                                'full_name' => 'Full name',
                                'email' => 'Email',
                                'status' => 'Status',
                                'email_verified_at' => 'Verification',
                                'last_login_at' => 'Last login',
                            ] as $column => $heading)
                                <th scope="col" class="px-5 py-3.5 font-medium" aria-sort="{{ $ariaSort($column) }}">
                                    <a href="{{ $sortUrl($column) }}" class="inline-flex items-center gap-1.5 transition-colors hover:text-gray-200 focus:outline-none focus-visible:text-gray-200">
                                        {{ $heading }}
                                        <i @class([
                                            'fa-solid text-[10px]',
                                            'fa-sort' => $sortBy !== $column,
                                            'fa-sort-up text-indigo-300' => $sortBy === $column && $sortOrder === 'asc',
                                            'fa-sort-down text-indigo-300' => $sortBy === $column && $sortOrder !== 'asc',
                                        ]) aria-hidden="true"></i>
                                    </a>
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-white/[0.06]">
                        @forelse ($users as $user)
                            @php
                                $userStatus = strtoupper((string) $user->status);
                                $userStatusClass = $statusStyles[$userStatus] ?? 'border-gray-500/20 bg-gray-500/10 text-gray-400';
                                $initial = mb_strtoupper(mb_substr($user->name ?: $user->full_name, 0, 1));
                            @endphp

                            <tr @class([
                                'transition hover:bg-white/[0.03]',
                                'opacity-60' => method_exists($user, 'trashed') && $user->trashed(),
                            ])>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-indigo-500/20 bg-gradient-to-br from-indigo-500/20 to-violet-500/10 text-sm font-bold text-indigo-200">
                                            {{ $initial !== '' ? $initial : '?' }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-mono text-xs text-gray-300">{{ $user->id }}</p>
                                            <p class="mt-1 text-[11px] text-gray-600">v{{ $user->version }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="font-medium text-white">{{ $user->name }}</p>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="max-w-[16rem] truncate text-gray-300" title="{{ $user->full_name }}">{{ $user->full_name }}</p>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="max-w-[18rem]">
                                        <p class="truncate text-gray-300" title="{{ $user->email }}">{{ $user->email }}</p>
                                        @if (method_exists($user, 'trashed') && $user->trashed())
                                            <span class="mt-1 inline-flex items-center gap-1 text-[11px] text-red-300">
                                                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                                Deleted
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs font-medium {{ $userStatusClass }}">
                                        <span @class([
                                            'h-1.5 w-1.5 rounded-full',
                                            'bg-emerald-400' => $userStatus === 'ACTIVE',
                                            'bg-gray-500' => $userStatus === 'INACTIVE' || ! in_array($userStatus, ['ACTIVE', 'INACTIVE', 'SUSPENDED'], true),
                                            'bg-red-400' => $userStatus === 'SUSPENDED',
                                        ]) aria-hidden="true"></span>
                                        {{ $statusLabels[$userStatus] ?? $userStatus }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    @if ($user->email_verified_at)
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-300">
                                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                            Verified
                                        </span>
                                        <p class="mt-1 text-[11px] text-gray-600">{{ \Illuminate\Support\Carbon::parse($user->email_verified_at)->format('M j, Y') }}</p>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-300">
                                            <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                            Pending
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    @if ($user->last_login_at)
                                        <p class="text-gray-300">{{ \Illuminate\Support\Carbon::parse($user->last_login_at)->format('M j, Y') }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ \Illuminate\Support\Carbon::parse($user->last_login_at)->format('H:i') }}</p>
                                    @else
                                        <span class="text-xs text-gray-600">Never logged in</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-20 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-white/[0.03] text-gray-500">
                                        <i class="fa-solid fa-users text-2xl" aria-hidden="true"></i>
                                    </div>

                                    @if ($chips)
                                        <h3 class="mt-5 font-semibold text-white">No users match these filters</h3>
                                        <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500">Try removing a filter or searching for something broader.</p>
                                        <a href="{{ route('user.list') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-medium text-indigo-400 transition hover:text-indigo-300">
                                            <i class="fa-solid fa-rotate-left text-xs" aria-hidden="true"></i>
                                            Clear filters
                                        </a>
                                    @else
                                        <h3 class="mt-5 font-semibold text-white">No users found</h3>
                                        <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500">There are no user records to display.</p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($users->total() > 0)
                <div class="flex flex-col gap-4 border-t border-white/[0.08] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-gray-500">
                        <p>
                            Showing
                            <span class="font-medium text-gray-300">{{ $users->firstItem() }}–{{ $users->lastItem() }}</span>
                            of
                            <span class="font-medium text-gray-300">{{ $users->total() }}</span>
                        </p>

                        <label class="flex items-center gap-2">
                            Rows per page
                            <select
                                form="user-filters"
                                name="per_page"
                                @change="$el.form.submit()"
                                class="h-8 rounded-lg border border-white/10 bg-white/[0.04] px-2 text-xs text-gray-200 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 [&>option]:bg-gray-900"
                            >
                                @foreach ([15, 25, 50, 100] as $size)
                                    <option value="{{ $size }}" @selected((int) request('per_page', 15) === $size)>{{ $size }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    @if ($users->hasPages())
                        @php
                            $current = $users->currentPage();
                            $last = $users->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp

                        <nav class="flex flex-wrap items-center gap-1.5" aria-label="Pagination">
                            @if ($users->onFirstPage())
                                <span class="{{ $pageButton }} cursor-not-allowed border-white/[0.06] text-gray-700" aria-disabled="true">
                                    <i class="fa-solid fa-chevron-left text-xs" aria-hidden="true"></i>
                                    <span class="sr-only">Previous page</span>
                                </span>
                            @else
                                <a href="{{ $users->previousPageUrl() }}" rel="prev" class="{{ $pageButton }} border-white/10 text-gray-300 hover:bg-white/[0.06] hover:text-white">
                                    <i class="fa-solid fa-chevron-left text-xs" aria-hidden="true"></i>
                                    <span class="sr-only">Previous page</span>
                                </a>
                            @endif

                            @if ($start > 1)
                                <a href="{{ $users->url(1) }}" class="{{ $pageButton }} border-white/10 text-gray-300 hover:bg-white/[0.06] hover:text-white">1</a>
                                @if ($start > 2)
                                    <span class="px-1 text-gray-600" aria-hidden="true">…</span>
                                @endif
                            @endif

                            @foreach ($users->getUrlRange($start, $end) as $page => $url)
                                @if ($page === $current)
                                    <span class="{{ $pageButton }} border-indigo-500/40 bg-indigo-500/20 text-white" aria-current="page">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="{{ $pageButton }} border-white/10 text-gray-300 hover:bg-white/[0.06] hover:text-white" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                                @endif
                            @endforeach

                            @if ($end < $last)
                                @if ($end < $last - 1)
                                    <span class="px-1 text-gray-600" aria-hidden="true">…</span>
                                @endif
                                <a href="{{ $users->url($last) }}" class="{{ $pageButton }} border-white/10 text-gray-300 hover:bg-white/[0.06] hover:text-white">{{ $last }}</a>
                            @endif

                            @if ($users->hasMorePages())
                                <a href="{{ $users->nextPageUrl() }}" rel="next" class="{{ $pageButton }} border-white/10 text-gray-300 hover:bg-white/[0.06] hover:text-white">
                                    <i class="fa-solid fa-chevron-right text-xs" aria-hidden="true"></i>
                                    <span class="sr-only">Next page</span>
                                </a>
                            @else
                                <span class="{{ $pageButton }} cursor-not-allowed border-white/[0.06] text-gray-700" aria-disabled="true">
                                    <i class="fa-solid fa-chevron-right text-xs" aria-hidden="true"></i>
                                    <span class="sr-only">Next page</span>
                                </span>
                            @endif
                        </nav>
                    @endif
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
