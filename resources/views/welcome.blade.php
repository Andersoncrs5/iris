<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#07070d">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|bricolage-grotesque:600,700" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }
        .iris-display { font-family: 'Bricolage Grotesque', 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }

        /* Entrada única da página, em sequência */
        .iris-rise { animation: iris-rise .8s cubic-bezier(.2, .7, .2, 1) both; }
        @keyframes iris-rise {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: none; }
        }

        @media (prefers-reduced-motion: reduce) {
            .iris-rise { animation: none; }
        }
    </style>
</head>
<body class="relative flex min-h-screen flex-col overflow-x-hidden bg-black font-sans text-white antialiased">

    {{-- ============================== BACKGROUND ============================== --}}
    <div class="fixed inset-0 -z-10 h-full w-full overflow-hidden bg-[#07070d]" aria-hidden="true">
        {{-- O vídeo é escondido para quem prefere menos movimento --}}
        <video autoplay loop muted playsinline class="absolute inset-0 h-full w-full object-cover motion-reduce:hidden">
            <source src="{{ asset('videos/singularity-cosmic-vortex-moewalls-com.mp4') }}" type="video/mp4">
        </video>

        {{-- Escurece o vídeo e protege a leitura do texto --}}
        <div class="absolute inset-0 bg-gradient-to-b from-black/75 via-black/45 to-black/85"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_50%_40%,rgba(7,7,13,.55),transparent_70%)]"></div>
        <div class="absolute inset-0 bg-indigo-950/20 mix-blend-multiply"></div>
    </div>

    {{-- ============================== HEADER ============================== --}}
    <header class="relative z-10 mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-6 sm:px-10">
        <a href="{{ url('/') }}" class="flex items-center gap-3 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400/60 rounded-lg">
            <svg viewBox="0 0 32 32" class="h-9 w-9" fill="none" aria-hidden="true">
                <defs>
                    <linearGradient id="markGrad" x1="0" y1="0" x2="32" y2="32" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#818cf8"/>
                        <stop offset="1" stop-color="#e879f9"/>
                    </linearGradient>
                </defs>
                <circle cx="16" cy="16" r="14" stroke="url(#markGrad)" stroke-width="2" stroke-dasharray="2 3.2"/>
                <circle cx="16" cy="16" r="8.5" stroke="url(#markGrad)" stroke-width="1.5" opacity=".7"/>
                <circle cx="16" cy="16" r="3.6" fill="#fff"/>
            </svg>
            <span class="iris-display text-xl font-bold tracking-tight text-white">{{ config('app.name', 'Laravel') }}</span>
        </a>

        @if (Route::has('login'))
            @auth
                <a
                    href="{{ url('/dashboard') }}"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-gray-300 transition-colors hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400/60"
                >
                    Dashboard
                </a>
            @else
                <a
                    href="{{ route('login') }}"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-gray-300 transition-colors hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400/60"
                >
                    Sign in
                </a>
            @endauth
        @endif
    </header>

    {{-- ============================== HERO ============================== --}}
    <main class="relative z-10 flex flex-1 flex-col items-center justify-center px-6 py-10 text-center sm:px-10">

        <h1
            class="iris-display iris-rise max-w-4xl text-5xl font-bold leading-[1.02] tracking-tight text-white text-balance drop-shadow-[0_2px_30px_rgba(0,0,0,.6)] sm:text-7xl"
        >
            Every conversation, in focus.
        </h1>

        <p
            class="iris-rise mt-6 max-w-xl text-lg leading-relaxed text-gray-300 text-balance sm:text-xl"
            style="animation-delay: .1s"
        >
            Sign in to {{ config('app.name') }} to manage tickets, replies and handoffs in one clear view.
        </p>

        @if (Route::has('login'))
            <div class="iris-rise mt-10 flex w-full flex-col items-center justify-center gap-3 sm:w-auto sm:flex-row sm:gap-4" style="animation-delay: .2s">
                @auth
                    <a
                        href="{{ url('/dashboard') }}"
                        class="group inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-white px-7 text-sm font-semibold text-gray-950 shadow-xl shadow-black/30 transition hover:bg-gray-100 focus:outline-none focus-visible:ring-4 focus-visible:ring-white/30 active:scale-[0.99] sm:w-auto"
                    >
                        Go to dashboard
                        <i class="fa-solid fa-arrow-right text-xs transition-transform group-hover:translate-x-1" aria-hidden="true"></i>
                    </a>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="group relative inline-flex h-12 w-full items-center justify-center gap-2 overflow-hidden rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-7 text-sm font-semibold text-white shadow-lg shadow-indigo-950/50 transition duration-200 hover:shadow-indigo-500/30 focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/40 active:scale-[0.99] sm:w-auto"
                    >
                        <span
                            class="pointer-events-none absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-700 group-hover:translate-x-full"
                            aria-hidden="true"
                        ></span>
                        <span class="relative">Sign in</span>
                        <i class="fa-solid fa-arrow-right relative text-xs transition-transform group-hover:translate-x-1" aria-hidden="true"></i>
                    </a>

                    @if (Route::has('register'))
                        <a
                            href="{{ route('register') }}"
                            class="inline-flex h-12 w-full items-center justify-center rounded-xl border border-white/20 bg-white/[0.06] px-7 text-sm font-semibold text-white backdrop-blur-md transition hover:border-white/30 hover:bg-white/[0.12] focus:outline-none focus-visible:ring-4 focus-visible:ring-white/20 active:scale-[0.99] sm:w-auto"
                        >
                            Create account
                        </a>
                    @endif
                @endauth
            </div>
        @endif

        {{-- Prévia da caixa de entrada (decorativa) --}}
        <div
            class="iris-rise relative mt-14 w-full max-w-2xl overflow-hidden rounded-2xl border border-white/10 bg-black/40 text-left shadow-2xl shadow-black/50 backdrop-blur-xl"
            style="animation-delay: .35s"
            aria-hidden="true"
        >
            <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-indigo-300/40 to-transparent"></div>

            <div class="flex items-center justify-between border-b border-white/[0.07] px-5 py-3.5">
                <div class="flex items-center gap-2.5">
                    <i class="fa-solid fa-inbox text-sm text-indigo-300"></i>
                    <span class="text-sm font-semibold text-white">Inbox</span>
                </div>
                <span class="text-xs text-gray-400">3 open</span>
            </div>

            <ul class="divide-y divide-white/[0.06]">
                <li class="flex items-center gap-3.5 px-5 py-3.5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-xs font-bold text-white">MR</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-white">Refund for order #4821</p>
                        <p class="mt-0.5 text-xs text-gray-400">Maria Ribeiro · 2 min ago</p>
                    </div>
                    <span class="rounded-full bg-indigo-500/15 px-2.5 py-1 text-xs font-medium text-indigo-300">New</span>
                </li>

                <li class="flex items-center gap-3.5 px-5 py-3.5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-sky-500 to-indigo-500 text-xs font-bold text-white">LK</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-white">Can't reset my password</p>
                        <p class="mt-0.5 text-xs text-gray-400">Luke Kim · 14 min ago</p>
                    </div>
                    <span class="rounded-full bg-emerald-500/15 px-2.5 py-1 text-xs font-medium text-emerald-300">Replied</span>
                </li>

                <li class="flex items-center gap-3.5 px-5 py-3.5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-fuchsia-500 to-violet-500 text-xs font-bold text-white">TS</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-white">Invoice shows the wrong address</p>
                        <p class="mt-0.5 text-xs text-gray-400">Tara Singh · 1 hour ago</p>
                    </div>
                    <span class="rounded-full bg-amber-500/15 px-2.5 py-1 text-xs font-medium text-amber-300">Waiting</span>
                </li>
            </ul>
        </div>
    </main>

    {{-- ============================== FOOTER ============================== --}}
    <footer class="relative z-10 w-full px-6 py-6 text-center text-xs text-gray-500">
        &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
    </footer>

</body>
</html>