@extends('layouts.base')

@section('title', 'User Details | Iris')

@section('content')
    @php
        $currentUser = auth()->user();
        $canViewPermissions = $currentUser !== null
            && ($currentUser->can('super.adm') || $currentUser->can('simple.adm'));

        $displayName = $user->name ?? 'Unknown user';
        $fullName = $user->full_name ?: $displayName;
        $email = $user->email ?? '—';
        $rawStatus = $user->status instanceof \BackedEnum
            ? $user->status->value
            : (string) ($user->status ?? 'UNKNOWN');
        $status = strtoupper($rawStatus);
        $isActive = $status === 'ACTIVE';
        $canToggleStatus = $canViewPermissions && (int) $currentUser->getKey() !== (int) $user->getKey();
        $targetStatus = $isActive ? 'INACTIVE' : 'ACTIVE';

        $initials = collect(preg_split('/\s+/', trim($displayName)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('') ?: '?';

        $permissionRelations = $canViewPermissions
            ? $user->permissionRelations()->orderBy('module')->orderBy('name')->get()
            : collect();

        $permissionGroups = $permissionRelations->groupBy('module');
        $permissionCount = $permissionRelations->count();
        $isSuperAdmin = $canViewPermissions && $user->isSuperAdmin();

        $moduleIcons = [
            'auth' => 'fa-shield-halved',
            'users' => 'fa-users',
            'permissions' => 'fa-key',
            'categories' => 'fa-folder',
            'tags' => 'fa-tags',
            'ticket_statuses' => 'fa-list-check',
            'ticket_priorities' => 'fa-arrow-up-wide-short',
            'tickets' => 'fa-ticket',
            'ticket_messages' => 'fa-comments',
            'ticket_attachments' => 'fa-paperclip',
            'ticket_watchers' => 'fa-eye',
        ];

        $moduleLabels = [
            'auth' => 'Authentication',
            'users' => 'Users',
            'permissions' => 'Permissions',
            'categories' => 'Categories',
            'tags' => 'Tags',
            'ticket_statuses' => 'Ticket Statuses',
            'ticket_priorities' => 'Ticket Priorities',
            'tickets' => 'Tickets',
            'ticket_messages' => 'Ticket Messages',
            'ticket_attachments' => 'Ticket Attachments',
            'ticket_watchers' => 'Ticket Watchers',
        ];
    @endphp

    <div class="mx-auto min-h-screen w-full max-w-7xl space-y-8 px-4 py-8 text-gray-100 sm:px-6 lg:px-8">
        {{-- Page header --}}
        <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-xs font-medium text-gray-500">
                    <i class="fa-solid fa-users text-indigo-400" aria-hidden="true"></i>
                    <span>User management</span>
                    <i class="fa-solid fa-chevron-right text-[9px]" aria-hidden="true"></i>
                    <span class="text-indigo-300" aria-current="page">User details</span>
                </div>

                <h1 class="flex items-center gap-3 text-3xl font-extrabold tracking-tight text-white">
                    <i class="fa-solid fa-circle-user text-indigo-400" aria-hidden="true"></i>
                    User Profile
                </h1>
                <p class="mt-2 text-sm text-gray-400">
                    Account information and activity for this user.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                @if ($canToggleStatus)
                    <form
                        action="{{ route('user.toggle.active', ['id' => $user->id, 'status' => $targetStatus]) }}"
                        method="POST"
                    >
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 self-start rounded-xl border px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-4 {{ $isActive ? 'border-amber-500/25 bg-amber-500/10 text-amber-300 hover:bg-amber-500/20 focus-visible:ring-amber-500/20' : 'border-emerald-500/25 bg-emerald-500/10 text-emerald-300 hover:bg-emerald-500/20 focus-visible:ring-emerald-500/20' }}"
                        >
                            <i class="fa-solid {{ $isActive ? 'fa-user-slash' : 'fa-user-check' }} text-xs" aria-hidden="true"></i>
                            {{ $isActive ? 'Deactivate account' : 'Activate account' }}
                        </button>
                    </form>
                @endif

                <a
                    href="{{ url()->previous() }}"
                    class="inline-flex items-center justify-center gap-2 self-start rounded-xl border border-white/10 px-4 py-2.5 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:bg-white/[0.04] hover:text-white sm:self-center"
                >
                    <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                    Back
                </a>
            </div>
        </header>

        {{-- Flash messages --}}
        @if (session('success'))
            <div role="status" class="flex items-start gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">
                <i class="fa-solid fa-circle-check mt-0.5" aria-hidden="true"></i>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div role="alert" class="flex items-start gap-3 rounded-xl border border-red-500/25 bg-red-500/10 p-4 text-sm text-red-300">
                <i class="fa-solid fa-circle-exclamation mt-0.5" aria-hidden="true"></i>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        {{-- Profile hero --}}
        <section class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-zinc-900/90 via-black/80 to-zinc-950/90 p-6 shadow-2xl backdrop-blur-xl md:p-8">
            <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-indigo-600/20 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-purple-600/15 blur-3xl" aria-hidden="true"></div>

            <div class="relative z-10 flex flex-col items-center gap-6 md:flex-row md:items-start md:gap-8">
                <div class="h-28 w-28 shrink-0 rounded-2xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-pink-500 p-1 shadow-xl md:h-32 md:w-32">
                    <div class="flex h-full w-full items-center justify-center rounded-[14px] bg-zinc-950 text-4xl font-black uppercase tracking-wider text-white">
                        {{ $initials }}
                    </div>
                </div>

                <div class="min-w-0 flex-1 space-y-3 text-center md:text-left">
                    <div class="flex flex-wrap items-center justify-center gap-3 md:justify-start">
                        <h2 class="break-words text-3xl font-bold tracking-tight text-white">
                            {{ $displayName }}
                        </h2>

                        @if ($isSuperAdmin)
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-purple-500/30 bg-purple-500/15 px-3 py-1 text-xs font-semibold text-purple-300">
                                <i class="fa-solid fa-crown text-[11px]" aria-hidden="true"></i>
                                Super Admin
                            </span>
                        @endif

                        <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold {{ $isActive ? 'border-emerald-500/30 bg-emerald-500/15 text-emerald-300' : 'border-red-500/30 bg-red-500/15 text-red-300' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $isActive ? 'bg-emerald-400' : 'bg-red-400' }}" aria-hidden="true"></span>
                            {{ $status }}
                        </span>
                    </div>

                    <p class="flex items-center justify-center gap-2 text-sm text-gray-300 md:justify-start">
                        <i class="fa-regular fa-id-card text-indigo-400" aria-hidden="true"></i>
                        {{ $fullName }}
                    </p>

                    <p class="flex flex-wrap items-center justify-center gap-2 text-sm text-gray-300 md:justify-start">
                        <i class="fa-regular fa-envelope text-indigo-400" aria-hidden="true"></i>
                        <span class="break-all">{{ $email }}</span>
                        @if ($user->email_verified_at)
                            <span class="inline-flex items-center gap-1 text-xs text-emerald-400" title="Email verified">
                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                Verified
                            </span>
                        @endif
                    </p>

                    <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 border-t border-white/10 pt-4 text-xs text-gray-400 md:justify-start">
                        <span class="inline-flex items-center gap-2">
                            <i class="fa-regular fa-id-card text-gray-500" aria-hidden="true"></i>
                            User ID: <strong class="break-all font-mono text-gray-200">{{ $user->id }}</strong>
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <i class="fa-regular fa-calendar-check text-gray-500" aria-hidden="true"></i>
                            Member since: <strong class="text-gray-200">{{ $user->created_at?->format('M d, Y') ?? '—' }}</strong>
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <i class="fa-regular fa-clock text-gray-500" aria-hidden="true"></i>
                            Last login:
                            <strong class="text-gray-200">
                                {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                            </strong>
                        </span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Main content --}}
        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
            {{-- Account details --}}
            <div class="space-y-6 lg:col-span-1">
                <section class="space-y-5 rounded-2xl border border-white/10 bg-black/40 p-6 shadow-2xl backdrop-blur-md">
                    <div class="flex items-center gap-2.5 border-b border-white/10 pb-3">
                        <i class="fa-solid fa-user-gear text-indigo-400" aria-hidden="true"></i>
                        <h3 class="text-lg font-bold text-white">Account Details</h3>
                    </div>

                    <dl class="space-y-4 text-sm">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-gray-500">Full name</dt>
                            <dd class="mt-1 break-words font-semibold text-gray-200">{{ $fullName ?: 'Not provided' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-gray-500">Username</dt>
                            <dd class="mt-1 break-words font-semibold text-gray-200">{{ $displayName }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-gray-500">Email address</dt>
                            <dd class="mt-1 break-all font-semibold text-gray-200">{{ $email }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-gray-500">Status</dt>
                            <dd class="mt-2">
                                <span class="inline-flex items-center gap-2 rounded-md border px-2.5 py-1 text-xs font-medium {{ $isActive ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300' : 'border-red-500/20 bg-red-500/10 text-red-300' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $isActive ? 'bg-emerald-400' : 'bg-red-400' }}" aria-hidden="true"></span>
                                    {{ $status }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-gray-500">Account version</dt>
                            <dd class="mt-1 font-semibold text-gray-200">v{{ $user->version ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-gray-500">Email verification</dt>
                            <dd class="mt-1 font-semibold {{ $user->email_verified_at ? 'text-emerald-300' : 'text-gray-400' }}">
                                {{ $user->email_verified_at ? 'Verified' : 'Not verified' }}
                            </dd>
                        </div>
                    </dl>
                </section>

                {{-- Privileged account metrics: never disclose permission counts to regular users. --}}
                @if ($canViewPermissions)
                <section class="space-y-4 rounded-2xl border border-white/10 bg-black/40 p-6 shadow-2xl backdrop-blur-md">
                    <div class="flex items-center gap-2.5 border-b border-white/10 pb-3">
                        <i class="fa-solid fa-chart-line text-indigo-400" aria-hidden="true"></i>
                        <h3 class="text-lg font-bold text-white">Account Overview</h3>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-xl border border-white/10 bg-white/[0.04] p-4 text-center">
                            <span class="block text-2xl font-black text-white">{{ $isSuperAdmin ? 'All' : $permissionCount }}</span>
                            <span class="mt-1 block text-xs text-gray-400">Access level</span>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-white/[0.04] p-4 text-center">
                            <span class="block text-2xl font-black text-indigo-300">{{ $permissionCount }}</span>
                            <span class="mt-1 block text-xs text-gray-400">Listed permissions</span>
                        </div>
                    </div>

                    @if ($isSuperAdmin)
                        <div class="flex items-start gap-3 rounded-xl border border-purple-500/20 bg-purple-500/[0.06] p-4">
                            <i class="fa-solid fa-crown mt-0.5 text-purple-300" aria-hidden="true"></i>
                            <div>
                                <p class="text-sm font-semibold text-purple-200">Super Administrator</p>
                                <p class="mt-1 text-xs leading-5 text-gray-400">This account has elevated administrative access.</p>
                            </div>
                        </div>
                    @endif
                </section>
                @endif
            </div>

            {{-- Permissions are disclosed only to users with super.adm or simple.adm. --}}
            @if ($canViewPermissions)
            <section class="space-y-6 rounded-2xl border border-white/10 bg-black/40 p-6 shadow-2xl backdrop-blur-md md:p-8 lg:col-span-2">
                <div class="flex flex-col gap-3 border-b border-white/10 pb-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="flex items-center gap-2.5 text-xl font-bold text-white">
                            <i class="fa-solid fa-key text-indigo-400" aria-hidden="true"></i>
                            Granted Permissions
                        </h3>
                        <p class="mt-1 text-xs text-gray-400">Permissions associated with this account, grouped by module.</p>
                    </div>

                    <span class="inline-flex w-fit items-center gap-1.5 rounded-full border border-indigo-500/20 bg-indigo-500/10 px-3 py-1 text-xs font-semibold text-indigo-300">
                        <i class="fa-solid fa-lock-open text-[10px]" aria-hidden="true"></i>
                        {{ $permissionCount }} listed
                    </span>
                </div>

                @if ($isSuperAdmin)
                    <div class="flex items-start gap-3 rounded-xl border border-purple-500/20 bg-purple-500/[0.05] p-5">
                        <i class="fa-solid fa-crown mt-0.5 text-purple-300" aria-hidden="true"></i>
                        <div>
                            <h4 class="text-sm font-bold text-purple-200">Super Administrator</h4>
                            <p class="mt-1 text-xs leading-5 text-gray-400">This account is recognized as a super administrator. The permissions listed below show its explicit permission records, if any.</p>
                        </div>
                    </div>
                @endif

                @if ($permissionGroups->isNotEmpty())
                    <div class="space-y-5">
                        @foreach ($permissionGroups as $module => $groupPermissions)
                            <section class="space-y-3 rounded-xl border border-white/10 bg-white/[0.03] p-4 md:p-5">
                                <div class="flex items-center justify-between gap-3">
                                    <h4 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-indigo-300">
                                        <i class="fa-solid {{ $moduleIcons[$module] ?? 'fa-layer-group' }}" aria-hidden="true"></i>
                                        {{ $moduleLabels[$module] ?? str_replace('_', ' ', $module) }}
                                    </h4>
                                    <span class="rounded-full bg-white/[0.07] px-2.5 py-1 font-mono text-xs text-gray-400">
                                        {{ $groupPermissions->count() }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    @foreach ($groupPermissions as $permission)
                                        <div class="flex min-w-0 items-start gap-3 rounded-lg border border-emerald-500/15 bg-emerald-500/[0.04] p-3">
                                            <span class="mt-0.5 text-emerald-400">
                                                <i class="fa-solid fa-check text-xs" aria-hidden="true"></i>
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <p class="break-words text-xs font-semibold text-gray-200">{{ $permission->name }}</p>
                                                <code class="mt-1 block break-all text-[10px] text-gray-500">{{ $permission->slug }}</code>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-white/10 bg-white/[0.03] px-6 py-12 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl border border-white/10 bg-white/[0.04] text-gray-500">
                            <i class="fa-solid fa-key text-xl" aria-hidden="true"></i>
                        </div>
                        <h4 class="mt-4 text-sm font-semibold text-gray-200">No explicit permissions listed</h4>
                        <p class="mx-auto mt-1 max-w-sm text-xs leading-5 text-gray-500">
                            There are no permission records to display for this account.
                        </p>
                    </div>
                @endif
            </section>
            @endif
        </div>
    </div>
@endsection
