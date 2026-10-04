<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="bg-black text-white antialiased relative min-h-screen flex flex-col justify-between overflow-hidden font-sans">

    <div class="fixed inset-0 w-full h-full -z-10 overflow-hidden">
        <video autoplay loop muted playsinline class="absolute inset-0 w-full h-full object-cover">
            <source src="{{ Vite::asset('resources/videos/singularity-cosmic-vortex-moewalls-com.mp4') }}" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-black/60 backdrop-blur-[2px]"></div>
    </div>

    <header class="w-full max-w-7xl mx-auto px-6 py-6 flex justify-between items-center z-10">
        <div class="flex items-center gap-3">
            <span class="text-2xl font-bold tracking-wider text-white uppercase">{{ config('app.name', 'Laravel') }}</span>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="flex-1 flex items-center justify-center px-6 z-10">
        <div class="max-w-2xl text-center backdrop-blur-xl bg-white/10 p-8 sm:p-12 rounded-2xl border border-white/20 shadow-2xl">
            <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-white mb-4 drop-shadow-md">
                Welcome to the {{ config('app.name') }}
            </h1>
            <p class="text-lg sm:text-xl text-gray-200 mb-8 leading-relaxed font-light">
                Access your account to utilizar the system
            </p>

            @if (Route::has('login'))
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-8 py-3.5 rounded-xl bg-white text-gray-900 font-bold hover:bg-gray-100 transition shadow-xl">
                            Go to the Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-8 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold transition shadow-xl shadow-indigo-600/40">
                            Make Login
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-8 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/30 text-white font-semibold transition backdrop-blur-md">
                                Register
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </div>
    </main>

    <!-- Rodapé -->
    <footer class="w-full text-center py-6 text-sm text-gray-400 z-10">
        &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. Todos os direitos reservados.
    </footer>

</body>
</html>