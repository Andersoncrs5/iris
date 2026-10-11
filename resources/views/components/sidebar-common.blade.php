@php
    /*
     * Menu definido em um só lugar.
     * - list:   link principal do item (route = null vira "#"; can = null significa sem checagem de permissão)
     * - create: atalho "+" ao lado do item (ou item "Create ..." quando o usuário só pode criar)
     * - active: padrões de rota para destacar o item na página atual
     */
    $navGroups = [
        'Workspace' => [
            [
                'label'  => 'Dashboard',
                'icon'   => 'fa-chart-pie',
                'list'   => ['route' => 'dashboard', 'can' => null],
                'active' => ['dashboard'],
            ],
            [
                'label'  => 'Profile',
                'icon'   => 'fa-circle-user',
                'auth'   => true,
                'list'   => ['route' => 'user.profile', 'can' => null],
                'active' => ['user.profile'],
            ],
        ],

        'Tickets' => [
            [
                'label'    => 'Tickets',
                'singular' => 'ticket',
                'icon'     => 'fa-ticket',
                'list'     => ['route' => null, 'can' => 'ticket.list'],
                'create'   => ['route' => 'ticket.create', 'can' => 'ticket.create'],
                'active'   => ['ticket.create'],
            ],
            [
                'label'    => 'Ticket statuses',
                'singular' => 'ticket status',
                'icon'     => 'fa-list-check',
                'list'     => ['route' => 'ticket.status.list', 'can' => 'ticket.status.list'],
                'create'   => ['route' => 'ticket.status.create', 'can' => 'ticket.status.create'],
                'active'   => ['ticket.status.*'],
            ],
            [
                'label'    => 'Ticket priorities',
                'singular' => 'ticket priority',
                'icon'     => 'fa-flag',
                'list'     => ['route' => 'ticket.priority.list', 'can' => 'ticket.priority.list'],
                'create'   => ['route' => 'ticket.priority.create', 'can' => 'ticket.priority.create'],
                'active'   => ['ticket.priority.*'],
            ],
            [
                'label'    => 'Categories',
                'singular' => 'category',
                'icon'     => 'fa-layer-group',
                'list'     => ['route' => 'category.list', 'can' => 'category.list'],
                'create'   => ['route' => 'category.create', 'can' => 'category.create'],
                'active'   => ['category.*'],
            ],
            [
                'label'    => 'Tags',
                'singular' => 'tag',
                'icon'     => 'fa-tags',
                'list'     => ['route' => 'tag.list', 'can' => 'tag.list'],
                'create'   => ['route' => 'tag.create', 'can' => 'tag.create'],
                'active'   => ['tag.*'],
            ],
        ],

        'Administration' => [
            [
                'label'  => 'Users',
                'icon'   => 'fa-users',
                'list'   => ['route' => 'user.list', 'can' => 'user.list'],
                'active' => ['user.list'],
            ],
            [
                'label'    => 'Permissions',
                'singular' => 'permission',
                'icon'     => 'fa-shield-halved',
                'list'     => ['route' => 'permission.list', 'can' => 'permission.list'],
                'create'   => ['route' => 'permission.create', 'can' => 'permission.create'],
                'active'   => ['permission.*'],
            ],
        ],
    ];

    // Mantém só o que o usuário pode ver (mesma lógica dos @can/@auth anteriores)
    $navVisible = [];

    foreach ($navGroups as $groupLabel => $items) {
        $rows = [];

        foreach ($items as $item) {
            if (($item['auth'] ?? false) && ! auth()->check()) {
                continue;
            }

            $canList = isset($item['list'])
                && (empty($item['list']['can']) || \Illuminate\Support\Facades\Gate::allows($item['list']['can']));

            $canCreate = isset($item['create'])
                && (empty($item['create']['can']) || \Illuminate\Support\Facades\Gate::allows($item['create']['can']));

            if (! $canList && ! $canCreate) {
                continue;
            }

            $rows[] = $item + ['canList' => $canList, 'canCreate' => $canCreate];
        }

        if ($rows) {
            $navVisible[$groupLabel] = $rows;
        }
    }
@endphp

<div
    x-data="{ open: false }"
    x-init="$watch('open', value => { if (value) $nextTick(() => $refs.close && $refs.close.focus()) })"
    x-on:toggle-sidebar.window="open = !open"
    x-on:close-sidebar.window="open = false"
    x-on:keydown.escape.window="open = false"
    class="relative z-50"
>
    {{-- BACKDROP --}}
    <div
        x-show="open"
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm"
        aria-hidden="true"
        x-cloak
    ></div>

    {{-- SIDEBAR --}}
    <aside
        x-show="open"
        x-transition:enter="transition transform ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition transform ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        role="dialog"
        aria-modal="true"
        aria-label="Main navigation"
        class="iris-sidebar fixed right-0 top-0 z-50 flex h-dvh max-h-dvh w-72 max-w-[88vw] flex-col
               overflow-hidden border-l border-white/10
               bg-[#0a0a13]/95 text-white shadow-2xl shadow-black/60 backdrop-blur-xl"
        x-cloak
    >
        {{-- Brilho suave no topo --}}
        <div
            class="pointer-events-none absolute inset-x-0 top-0 h-48 bg-[radial-gradient(ellipse_at_80%_0%,rgba(99,102,241,.22),transparent_70%)]"
            aria-hidden="true"
        ></div>

        {{-- HEADER --}}
        <div class="relative flex shrink-0 items-center justify-between border-b border-white/[0.08] px-5 py-4">
            <div class="flex min-w-0 items-center gap-3">
                <svg viewBox="0 0 32 32" class="h-8 w-8 shrink-0" fill="none" aria-hidden="true">
                    <defs>
                        <linearGradient id="sbMarkGrad" x1="0" y1="0" x2="32" y2="32" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#818cf8"/>
                            <stop offset="1" stop-color="#e879f9"/>
                        </linearGradient>
                    </defs>
                    <circle cx="16" cy="16" r="14" stroke="url(#sbMarkGrad)" stroke-width="2" stroke-dasharray="2 3.2"/>
                    <circle cx="16" cy="16" r="8.5" stroke="url(#sbMarkGrad)" stroke-width="1.5" opacity=".7"/>
                    <circle cx="16" cy="16" r="3.6" fill="#fff"/>
                </svg>

                <h2 class="truncate text-lg font-bold tracking-tight text-white">
                    {{ config('app.name') }}
                </h2>

                <span class="shrink-0 rounded-full bg-indigo-500/15 px-2 py-0.5 text-[11px] font-semibold text-indigo-300">
                    v1.0
                </span>
            </div>

            {{-- CLOSE BUTTON --}}
            <button
                x-ref="close"
                @click="open = false"
                type="button"
                aria-label="Close sidebar"
                class="ml-2 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-gray-400 transition
                       hover:bg-white/10 hover:text-white
                       focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400/60"
            >
                <i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i>
            </button>
        </div>

        {{-- NAVIGATION (rolável) --}}
        <nav
            class="sidebar-nav relative min-h-0 flex-1 space-y-6 overflow-y-auto overscroll-contain px-3 py-5"
            aria-label="Main"
        >
            @foreach ($navVisible as $groupLabel => $rows)
                <div>
                    <p class="mb-2 px-3.5 text-xs font-medium text-gray-500">
                        {{ $groupLabel }}
                    </p>

                    <ul class="space-y-1">
                        @foreach ($rows as $row)
                            @php
                                $isActive = request()->routeIs(...($row['active'] ?? []));

                                $href = $row['canList']
                                    ? ($row['list']['route'] ? route($row['list']['route']) : '#')
                                    : route($row['create']['route']);

                                $label = $row['canList']
                                    ? $row['label']
                                    : 'Create ' . ($row['singular'] ?? strtolower($row['label']));

                                $icon = $row['canList'] ? $row['icon'] : 'fa-circle-plus';
                            @endphp

                            <li class="relative flex items-center gap-1">
                                @if ($isActive)
                                    <span
                                        class="absolute -left-3 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-gradient-to-b from-indigo-400 to-violet-400"
                                        aria-hidden="true"
                                    ></span>
                                @endif

                                <a
                                    href="{{ $href }}"
                                    @if ($isActive) aria-current="page" @endif
                                    @class([
                                        'group flex min-w-0 flex-1 items-center gap-3 rounded-xl px-2.5 py-2 text-sm font-medium transition',
                                        'focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400/60',
                                        'bg-white/[0.08] text-white' => $isActive,
                                        'text-gray-300 hover:bg-white/[0.06] hover:text-white' => ! $isActive,
                                    ])
                                >
                                    <span
                                        @class([
                                            'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-sm transition',
                                            'bg-indigo-500/20 text-indigo-200' => $isActive,
                                            'bg-white/[0.04] text-indigo-300 group-hover:bg-indigo-500/15' => ! $isActive,
                                        ])
                                    >
                                        <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                                    </span>

                                    <span class="truncate">{{ $label }}</span>
                                </a>

                                {{-- Atalho para criar --}}
                                @if ($row['canList'] && $row['canCreate'])
                                    <a
                                        href="{{ route($row['create']['route']) }}"
                                        title="Create {{ $row['singular'] ?? strtolower($row['label']) }}"
                                        aria-label="Create {{ $row['singular'] ?? strtolower($row['label']) }}"
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-500 transition
                                               hover:bg-indigo-500/20 hover:text-indigo-200
                                               focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400/60"
                                    >
                                        <i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>

        {{-- USUÁRIO + LOGOUT --}}
        @auth
            <div class="relative shrink-0 border-t border-white/[0.08] p-3">
                <div class="flex items-center justify-between gap-2 rounded-2xl border border-white/[0.08] bg-white/[0.04] p-2.5">

                    <div class="flex min-w-0 items-center gap-3">
                        {{-- AVATAR --}}
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 font-bold text-white shadow-md shadow-indigo-950/40">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>

                        {{-- INFO --}}
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-white">
                                {{ Auth::user()->name }}
                            </p>
                            <p class="truncate text-xs text-gray-400">
                                {{ Auth::user()->email }}
                            </p>
                        </div>
                    </div>

                    {{-- LOGOUT --}}
                    <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                        @csrf

                        <button
                            type="submit"
                            title="Sign out"
                            aria-label="Sign out"
                            class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 transition
                                   hover:bg-red-500/20 hover:text-red-400
                                   focus:outline-none focus-visible:ring-2 focus-visible:ring-red-400/60"
                        >
                            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </div>
        @endauth
    </aside>
</div>

{{-- SIDEBAR STYLES --}}
<style>
    [x-cloak] {
        display: none !important;
    }

    .sidebar-nav {
        scrollbar-width: thin;
        scrollbar-color: rgb(255 255 255 / 0.15) transparent;
    }

    .sidebar-nav::-webkit-scrollbar {
        width: 5px;
    }

    .sidebar-nav::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar-nav::-webkit-scrollbar-thumb {
        border-radius: 9999px;
        background: rgb(255 255 255 / 0.15);
    }

    .sidebar-nav::-webkit-scrollbar-thumb:hover {
        background: rgb(255 255 255 / 0.3);
    }

    @media (prefers-reduced-motion: reduce) {
        .iris-sidebar {
            transition-duration: 0s !important;
        }
    }
</style>