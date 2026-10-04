<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>@yield('title', config('app.name', 'Iris App'))</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <!-- FontAwesome -->
        <link rel="stylesheet" href="{{ asset('assets/fontawesome-free-6.7.2-web/css/all.min.css') }}">

        <!-- Alpine.js (Necessário para os componentes interativos) -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Tailwind CSS & JS via Vite -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
        @endif

        @stack('styles')

        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>

    <body class="bg-black text-white antialiased relative min-h-screen flex flex-col justify-between overflow-x-hidden font-sans">

        <!-- Background Video -->
        <div class="fixed inset-0 w-full h-full -z-10 overflow-hidden">
            <video autoplay loop muted playsinline class="absolute inset-0 w-full h-full object-cover">
                <source src="{{ Vite::asset('resources/videos/singularity-cosmic-vortex-moewalls-com.mp4') }}" type="video/mp4">
            </video>
            <!-- Dark Overlay for contrast -->
            <div class="absolute inset-0 bg-black/60 backdrop-blur-[2px]"></div>
        </div>

        @auth
            <header class="w-full max-w-7xl mx-auto px-6 py-6 flex justify-between items-center z-10 relative">
                <div class="flex items-center gap-3">
                    <span class="text-2xl font-bold tracking-wider text-white uppercase">{{ config('app.name', 'Laravel') }}</span>
                </div>

                <button 
                    x-data 
                    @click="$dispatch('toggle-sidebar')" 
                    type="button"
                    class="rounded-lg border border-white/10 bg-white/10 px-3.5 py-2 text-white transition hover:bg-white/20 active:scale-95 flex items-center gap-2 text-sm font-medium cursor-pointer"
                    title="Abrir Menu"
                >
                    <i class="fa-solid fa-bars text-base"></i>
                    <span>Menu</span>
                </button>
            </header>

            <!-- Sidebar Component -->
            <x-sidebar-common />
        @endauth

        @yield('navbar')

        <!-- Global Alerts -->
        @include('alerts.alert')

        <!-- Main Page Content -->
        <main class="flex-1 flex flex-col items-center justify-start relative z-10 w-full p-6">
            @yield('content')
        </main>

        <!-- Footer -->
        @if(View::exists('components.footer'))
            @include('components.footer')
        @else
            <footer class="w-full text-center py-6 text-sm text-gray-400 z-10 relative">
                &copy; {{ date('Y') }} {{ config('app.name', 'Iris App') }}. All rights reserved.
            </footer>
        @endif

        @stack('scripts')
    </body>
</html>