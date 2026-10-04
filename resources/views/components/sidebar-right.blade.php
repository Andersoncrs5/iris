<aside class="fixed top-0 right-0 z-40 flex h-screen w-64 flex-col justify-between border-l border-white/10 bg-black/40 p-5 text-white backdrop-blur-md transition-transform">
    <div>
        <!-- App Header / Logo -->
        <div class="mb-6 flex items-center justify-between border-b border-white/10 pb-4">
            <h2 class="text-lg font-bold tracking-wide text-white">{{ config('app.name') }}</h2>
            <span class="rounded-full bg-indigo-500/20 px-2.5 py-0.5 text-xs font-semibold text-indigo-300">
                v1.0
            </span>
        </div>

        <!-- Navigation Links -->
        <nav class="space-y-1.5">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/10 hover:text-white">
                <i class="fa-solid fa-chart-pie w-5 text-indigo-400"></i>
                <span>Dashboard</span>
            </a>

            @can('ticket.list')
            <a href="#" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/10 hover:text-white">
                <i class="fa-solid fa-ticket w-5 text-indigo-400"></i>
                <span>Tickets</span>
            </a>
            @endcan

            @can('category.list')
            <a href="#" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/10 hover:text-white">
                <i class="fa-solid fa-layer-group w-5 text-indigo-400"></i>
                <span>Categories</span>
            </a>
            @endcan

            @can('tag.list')
            <a href="#" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/10 hover:text-white">
                <i class="fa-solid fa-tags w-5 text-indigo-400"></i>
                <span>Tags</span>
            </a>
            @endcan

            @can('user.list')
            <a href="#" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/10 hover:text-white">
                <i class="fa-solid fa-users w-5 text-indigo-400"></i>
                <span>Users</span>
            </a>
            @endcan

            @can('permission.list')
            <a href="#" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/10 hover:text-white">
                <i class="fa-solid fa-shield-halved w-5 text-indigo-400"></i>
                <span>Permissions</span>
            </a>
            @endcan
        </nav>
    </div>

    <!-- User Profile & Logout -->
    @auth
    <div class="border-t border-white/10 pt-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-600 font-bold text-white shadow-md shadow-indigo-600/30">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="truncate">
                    <p class="truncate text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                    <p class="truncate text-xs text-gray-400">{{ Auth::user()->email }}</p>
                </div>
            </div>

            <!-- Logout Button -->
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" title="Sign Out" class="rounded-lg p-2 text-gray-400 hover:bg-red-500/20 hover:text-red-400 transition">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </div>
    @endauth
</aside>