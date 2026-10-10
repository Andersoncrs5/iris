<div
    x-data="{ open: false }"
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
        class="fixed right-0 top-0 z-50 flex
               h-dvh max-h-dvh w-64 flex-col
               overflow-hidden
               border-l border-white/10
               bg-black/90 p-5 text-white
               shadow-2xl backdrop-blur-md"
        x-cloak
    >

        {{-- HEADER + SCROLLABLE NAVIGATION --}}
        <div class="flex min-h-0 flex-1 flex-col">

            {{-- HEADER FIXO --}}
            <div
                class="mb-6 flex shrink-0 items-center
                       justify-between border-b
                       border-white/10 pb-4"
            >
                <div class="flex min-w-0 items-center gap-2">

                    <h2 class="truncate text-lg font-bold tracking-wide text-white">
                        {{ config('app.name') }}
                    </h2>

                    <span
                        class="shrink-0 rounded-full
                               bg-indigo-500/20 px-2.5 py-0.5
                               text-xs font-semibold text-indigo-300"
                    >
                        v1.0
                    </span>

                </div>

                {{-- CLOSE BUTTON --}}
                <button
                    @click="open = false"
                    type="button"
                    aria-label="Close sidebar"
                    class="ml-2 shrink-0 rounded-lg p-1.5
                           text-gray-400 transition
                           hover:bg-white/10 hover:text-white"
                >
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>

            </div>


            {{-- NAVIGATION SCROLLABLE --}}
            <nav
                class="sidebar-nav min-h-0 flex-1
                       space-y-1.5 overflow-y-auto
                       overscroll-contain pr-1"
            >

                {{-- DASHBOARD --}}
                <a
                    href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 rounded-xl
                           px-3.5 py-2.5 text-sm font-medium
                           text-gray-300 transition
                           hover:bg-white/10 hover:text-white"
                >
                    <i class="fa-solid fa-chart-pie w-5 text-indigo-400"></i>
                    <span>Dashboard</span>
                </a>


                {{-- PROFILE --}}
                @auth
                    <a
                        href="{{ route('user.profile') }}"
                        class="flex items-center gap-3 rounded-xl
                               px-3.5 py-2.5 text-sm font-medium
                               text-gray-300 transition
                               hover:bg-white/10 hover:text-white"
                    >
                        <i class="fa-solid fa-circle-user w-5 text-indigo-400"></i>
                        <span>Profile</span>
                    </a>
                @endauth


                {{-- TICKETS --}}
                @can('ticket.create')
                    <a
                        href="{{ route('ticket.create') }}"
                        class="flex items-center gap-3 rounded-xl
                               px-3.5 py-2.5 text-sm font-medium
                               text-gray-300 transition
                               hover:bg-white/10 hover:text-white"
                    >
                        <i class="fa-solid fa-circle-plus w-5 text-indigo-400"></i>
                        <span>Create Ticket</span>
                    </a>
                @endcan

                @can('ticket.list')
                    <a
                        href="#"
                        class="flex items-center gap-3 rounded-xl
                               px-3.5 py-2.5 text-sm font-medium
                               text-gray-300 transition
                               hover:bg-white/10 hover:text-white"
                    >
                        <i class="fa-solid fa-ticket w-5 text-indigo-400"></i>
                        <span>Tickets</span>
                    </a>
                @endcan


                {{-- TICKET STATUSES --}}
                @can('ticket.status.create')
                    <a
                        href="{{ route('ticket.status.create') }}"
                        class="flex items-center gap-3 rounded-xl
                               px-3.5 py-2.5 text-sm font-medium
                               text-gray-300 transition
                               hover:bg-white/10 hover:text-white"
                    >
                        <i class="fa-solid fa-circle-plus w-5 text-indigo-400"></i>
                        <span>Create Ticket Status</span>
                    </a>
                @endcan

                @can('ticket.status.list')
                    <a
                        href="{{ route('ticket.status.list') }}"
                        class="flex items-center gap-3 rounded-xl
                               px-3.5 py-2.5 text-sm font-medium
                               text-gray-300 transition
                               hover:bg-white/10 hover:text-white"
                    >
                        <i class="fa-solid fa-list-check w-5 text-indigo-400"></i>
                        <span>Ticket Statuses</span>
                    </a>
                @endcan


                {{-- CATEGORIES --}}
                @can('category.create')
                    <a
                        href="{{ route('category.create') }}"
                        class="flex items-center gap-3 rounded-xl
                               px-3.5 py-2.5 text-sm font-medium
                               text-gray-300 transition
                               hover:bg-white/10 hover:text-white"
                    >
                        <i class="fa-solid fa-circle-plus w-5 text-indigo-400"></i>
                        <span>Create Category</span>
                    </a>
                @endcan

                @can('category.list')
                    <a
                        href="{{ route('category.list') }}"
                        class="flex items-center gap-3 rounded-xl
                               px-3.5 py-2.5 text-sm font-medium
                               text-gray-300 transition
                               hover:bg-white/10 hover:text-white"
                    >
                        <i class="fa-solid fa-layer-group w-5 text-indigo-400"></i>
                        <span>Categories</span>
                    </a>
                @endcan


                {{-- TAGS --}}
                @can('tag.list')
                    <a
                        href="{{ route('tag.list') }}"
                        class="flex items-center gap-3 rounded-xl
                               px-3.5 py-2.5 text-sm font-medium
                               text-gray-300 transition
                               hover:bg-white/10 hover:text-white"
                    >
                        <i class="fa-solid fa-tags w-5 text-indigo-400"></i>
                        <span>Tags</span>
                    </a>
                @endcan

                @can('tag.create')
                    <a
                        href="{{ route('tag.create') }}"
                        class="flex items-center gap-3 rounded-xl
                               px-3.5 py-2.5 text-sm font-medium
                               text-gray-300 transition
                               hover:bg-white/10 hover:text-white"
                    >
                        <i class="fa-solid fa-circle-plus w-5 text-indigo-400"></i>
                        <span>Create Tag</span>
                    </a>
                @endcan


                {{-- USERS --}}
                @can('user.list')
                    <a
                        href="{{ route('user.list') }}"
                        class="flex items-center gap-3 rounded-xl
                               px-3.5 py-2.5 text-sm font-medium
                               text-gray-300 transition
                               hover:bg-white/10 hover:text-white"
                    >
                        <i class="fa-solid fa-users w-5 text-indigo-400"></i>
                        <span>Users</span>
                    </a>
                @endcan


                {{-- PERMISSIONS --}}
                @can('permission.list')
                    <a
                        href="#"
                        class="flex items-center gap-3 rounded-xl
                               px-3.5 py-2.5 text-sm font-medium
                               text-gray-300 transition
                               hover:bg-white/10 hover:text-white"
                    >
                        <i class="fa-solid fa-shield-halved w-5 text-indigo-400"></i>
                        <span>Permissions</span>
                    </a>
                @endcan

                @can('permission.create')
                    <a
                        href="{{ route("permission.create") }}"
                        class="flex items-center gap-3 rounded-xl
                               px-3.5 py-2.5 text-sm font-medium
                               text-gray-300 transition
                               hover:bg-white/10 hover:text-white"
                    >
                        <i class="fa-solid fa-circle-plus w-5 text-indigo-400"></i>
                        <span>Create Permissions</span>
                    </a>
                @endcan

            </nav>

        </div>


        {{-- USER PROFILE + LOGOUT FIXOS NO RODAPÉ --}}
        @auth
            <div
                class="shrink-0 border-t border-white/10
                       bg-black/90 pt-4"
            >

                <div class="flex items-center justify-between gap-2">

                    <div class="flex min-w-0 items-center gap-3">

                        {{-- AVATAR --}}
                        <div
                            class="flex h-9 w-9 shrink-0
                                   items-center justify-center
                                   rounded-full bg-indigo-600
                                   font-bold text-white
                                   shadow-md shadow-indigo-600/30"
                        >
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>

                        {{-- USER INFORMATION --}}
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
                            title="Sign Out"
                            aria-label="Sign out"
                            class="rounded-lg p-2 text-gray-400
                                   transition
                                   hover:bg-red-500/20 hover:text-red-400"
                        >
                            <i class="fa-solid fa-right-from-bracket"></i>
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
</style>