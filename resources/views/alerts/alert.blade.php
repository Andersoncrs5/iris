<div id="alert-container" class="fixed top-6 left-1/2 -translate-x-1/2 z-50 w-full max-w-md px-4 space-y-3 pointer-events-none">
    @php
        $alerts = [
            'success' => [
                'bg' => 'bg-emerald-950/90 border-emerald-500/40 text-emerald-200',
                'title' => 'Success!',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />'
            ],
            'error' => [
                'bg' => 'bg-rose-950/90 border-rose-500/40 text-rose-200',
                'title' => 'Error!',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
            ],
            'warning' => [
                'bg' => 'bg-amber-950/90 border-amber-500/40 text-amber-200',
                'title' => 'Warning!',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />'
            ],
        ];
    @endphp

    @foreach ($alerts as $key => $config)
        @if (session($key))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-transition:leave="transition ease-in duration-300 transform opacity-0 scale-95"
                 class="pointer-events-auto flex items-center justify-between p-4 rounded-xl border shadow-2xl backdrop-blur-md {{ $config['bg'] }}">
                
                <div class="flex items-center gap-3">
                    {{-- Ícone SVG --}}
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $config['icon'] !!}
                    </svg>

                    {{-- Mensagem --}}
                    <div>
                        <h4 class="font-bold text-sm leading-none mb-1">{{ $config['title'] }}</h4>
                        <p class="text-xs opacity-90 leading-tight">{{ session($key) }}</p>
                    </div>
                </div>

                {{-- Botão Fechar --}}
                <button @click="show = false" type="button" class="text-current opacity-60 hover:opacity-100 transition p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif
    @endforeach

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const container = document.getElementById("alert-container");
            if (container) {
                setTimeout(() => {
                    container.style.transition = "opacity 0.5s ease";
                    container.style.opacity = "0";
                    setTimeout(() => container.remove(), 500);
                }, 4000);
            }
        });
    </script>
</div>