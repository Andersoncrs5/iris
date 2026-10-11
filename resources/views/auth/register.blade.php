@extends('layouts.base')

@section('title', 'Sign Up | Iris')

@section('content')
{{-- Fonte do título (opcional: se o layout tiver @stack('head'), mova estas 3 linhas para lá) --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&display=swap" rel="stylesheet">

<style>
    [x-cloak] { display: none !important; }

    .iris-display { font-family: 'Bricolage Grotesque', ui-sans-serif, system-ui, sans-serif; }

    .iris-dots {
        background-image: radial-gradient(rgba(255, 255, 255, .07) 1px, transparent 1px);
        background-size: 26px 26px;
        -webkit-mask-image: radial-gradient(ellipse at 50% 45%, #000 20%, transparent 72%);
        mask-image: radial-gradient(ellipse at 50% 45%, #000 20%, transparent 72%);
    }

    .iris-stage {
        background:
            radial-gradient(60% 50% at 72% 28%, rgba(99, 102, 241, .30), transparent 70%),
            radial-gradient(50% 45% at 18% 88%, rgba(168, 85, 247, .22), transparent 70%);
        background-color: #0a0a14;
    }

    .iris-input:-webkit-autofill,
    .iris-input:-webkit-autofill:hover,
    .iris-input:-webkit-autofill:focus {
        -webkit-text-fill-color: #fff;
        caret-color: #fff;
        -webkit-box-shadow: 0 0 0 1000px #11111d inset;
        transition: background-color 9999s ease-out 0s;
    }

    .iris-ring-a, .iris-ring-b { transform-origin: 50% 50%; transform-box: view-box; }
    .iris-ring-a { animation: iris-turn 150s linear infinite; }
    .iris-ring-b { animation: iris-turn 95s linear infinite reverse; }
    @keyframes iris-turn { to { transform: rotate(360deg); } }

    .iris-rise { animation: iris-rise .7s cubic-bezier(.2, .7, .2, 1) both; }
    @keyframes iris-rise {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .iris-ring-a, .iris-ring-b, .iris-rise { animation: none; }
    }
</style>

<div class="relative isolate min-h-screen bg-[#07070d] text-gray-100 [color-scheme:dark] lg:grid lg:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)]">

    {{-- ============================== FORM ============================== --}}
    <section class="relative flex min-h-screen flex-col px-6 py-8 sm:px-12 lg:px-16 xl:px-24">

        <div class="iris-dots pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -left-40 -top-40 h-96 w-96 rounded-full bg-indigo-600/10 blur-3xl" aria-hidden="true"></div>

        {{-- Brand --}}
        <header class="relative z-10 flex items-center gap-3">
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
            <span class="iris-display text-xl font-bold tracking-tight text-white">Iris</span>
        </header>

        <main class="iris-rise relative z-10 mx-auto flex w-full max-w-md flex-1 flex-col justify-center py-10">

            <div class="mb-8">
                <h1 class="iris-display text-4xl font-bold tracking-tight text-white sm:text-[2.75rem] sm:leading-[1.05]">
                    Create your account
                </h1>
                <p class="mt-3 text-[15px] leading-6 text-gray-400">
                    Start handling support requests with your team in Iris.
                </p>
            </div>

            {{-- Validation summary --}}
            @if ($errors->any())
                <div
                    role="alert"
                    class="mb-6 flex items-start gap-3 rounded-xl border border-red-500/25 bg-red-500/10 p-3.5 text-sm text-red-300"
                >
                    <i class="fa-solid fa-circle-exclamation mt-0.5" aria-hidden="true"></i>

                    <div>
                        <p class="font-medium">We couldn't create your account.</p>
                        <p class="mt-1 text-red-300/80">
                            Fix the highlighted fields and try again.
                        </p>
                    </div>
                </div>
            @endif

            <form
                action="{{ route('registerHandle') }}"
                method="POST"
                class="space-y-4"
                x-data="{
                    showPassword: false,
                    capsLock: false,
                    loading: false,
                    password: '',
                    get score() {
                        const p = this.password;
                        if (!p.length) return 0;
                        let s = 0;
                        if (p.length >= 8) s++;
                        if (p.length >= 12) s++;
                        if (/[a-z]/.test(p) && /[A-Z]/.test(p)) s++;
                        if (/\d/.test(p) && /[^A-Za-z0-9]/.test(p)) s++;
                        return Math.max(1, s);
                    },
                    get label() { return ['', 'Weak', 'Fair', 'Good', 'Strong'][this.score]; },
                    get color() { return ['', 'bg-red-400', 'bg-amber-400', 'bg-emerald-400', 'bg-emerald-400'][this.score]; }
                }"
                @submit="loading = true"
                @pageshow.window="loading = false"
            >
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">

                    {{-- Username --}}
                    <div>
                        <label for="name" class="mb-2 block text-sm font-medium text-gray-200">
                            Username
                        </label>

                        <div class="group relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                <i class="fa-solid fa-at text-gray-500 transition-colors group-focus-within:text-indigo-400" aria-hidden="true"></i>
                            </div>

                            <input
                                type="text"
                                name="name"
                                id="name"
                                value="{{ old('name') }}"
                                placeholder="johndoe"
                                autocomplete="username"
                                autocapitalize="off"
                                spellcheck="false"
                                required
                                autofocus
                                @class([
                                    'iris-input block h-12 w-full rounded-xl border bg-white/[0.04] pl-11 pr-4 text-sm text-white',
                                    'placeholder:text-gray-500',
                                    'transition duration-200',
                                    'hover:border-white/20',
                                    'focus:outline-none focus:border-indigo-400 focus:bg-white/[0.06] focus:ring-4 focus:ring-indigo-500/15',
                                    'border-red-500/60' => $errors->has('name'),
                                    'border-white/10' => ! $errors->has('name'),
                                ])
                                @if ($errors->has('name'))
                                    aria-invalid="true"
                                    aria-describedby="name-error"
                                @endif
                            />
                        </div>

                        @error('name')
                            <p id="name-error" class="mt-2 flex items-center gap-1.5 text-xs text-red-400">
                                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Full name --}}
                    <div>
                        <label for="full_name" class="mb-2 block text-sm font-medium text-gray-200">
                            Full name
                        </label>

                        <div class="group relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                <i class="fa-regular fa-id-card text-gray-500 transition-colors group-focus-within:text-indigo-400" aria-hidden="true"></i>
                            </div>

                            <input
                                type="text"
                                name="full_name"
                                id="full_name"
                                value="{{ old('full_name') }}"
                                placeholder="John Doe"
                                autocomplete="name"
                                required
                                @class([
                                    'iris-input block h-12 w-full rounded-xl border bg-white/[0.04] pl-11 pr-4 text-sm text-white',
                                    'placeholder:text-gray-500',
                                    'transition duration-200',
                                    'hover:border-white/20',
                                    'focus:outline-none focus:border-indigo-400 focus:bg-white/[0.06] focus:ring-4 focus:ring-indigo-500/15',
                                    'border-red-500/60' => $errors->has('full_name'),
                                    'border-white/10' => ! $errors->has('full_name'),
                                ])
                                @if ($errors->has('full_name'))
                                    aria-invalid="true"
                                    aria-describedby="full_name-error"
                                @endif
                            />
                        </div>

                        @error('full_name')
                            <p id="full_name-error" class="mt-2 flex items-center gap-1.5 text-xs text-red-400">
                                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-gray-200">
                        Email address
                    </label>

                    <div class="group relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <i class="fa-regular fa-envelope text-gray-500 transition-colors group-focus-within:text-indigo-400" aria-hidden="true"></i>
                        </div>

                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            placeholder="you@example.com"
                            autocomplete="email"
                            autocapitalize="off"
                            spellcheck="false"
                            required
                            @class([
                                'iris-input block h-12 w-full rounded-xl border bg-white/[0.04] pl-11 pr-4 text-sm text-white',
                                'placeholder:text-gray-500',
                                'transition duration-200',
                                'hover:border-white/20',
                                'focus:outline-none focus:border-indigo-400 focus:bg-white/[0.06] focus:ring-4 focus:ring-indigo-500/15',
                                'border-red-500/60' => $errors->has('email'),
                                'border-white/10' => ! $errors->has('email'),
                            ])
                            @if ($errors->has('email'))
                                aria-invalid="true"
                                aria-describedby="email-error"
                            @endif
                        />
                    </div>

                    @error('email')
                        <p id="email-error" class="mt-2 flex items-center gap-1.5 text-xs text-red-400">
                            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-gray-200">
                        Password
                    </label>

                    <div class="group relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <i class="fa-solid fa-lock text-gray-500 transition-colors group-focus-within:text-indigo-400" aria-hidden="true"></i>
                        </div>

                        <input
                            type="password"
                            :type="showPassword ? 'text' : 'password'"
                            name="password"
                            id="password"
                            x-model="password"
                            placeholder="Create a password"
                            autocomplete="new-password"
                            required
                            @keyup="capsLock = $event.getModifierState && $event.getModifierState('CapsLock')"
                            @keydown="capsLock = $event.getModifierState && $event.getModifierState('CapsLock')"
                            @blur="capsLock = false"
                            @class([
                                'iris-input block h-12 w-full rounded-xl border bg-white/[0.04] pl-11 pr-12 text-sm text-white',
                                'placeholder:text-gray-500',
                                'transition duration-200',
                                'hover:border-white/20',
                                'focus:outline-none focus:border-indigo-400 focus:bg-white/[0.06] focus:ring-4 focus:ring-indigo-500/15',
                                'border-red-500/60' => $errors->has('password'),
                                'border-white/10' => ! $errors->has('password'),
                            ])
                            aria-describedby="password-hint{{ $errors->has('password') ? ' password-error' : '' }}"
                            @if ($errors->has('password'))
                                aria-invalid="true"
                            @endif
                        />

                        {{-- Mostrar / ocultar senha --}}
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-1.5 right-1.5 flex w-9 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-white/5 hover:text-indigo-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400/60"
                            :class="showPassword ? 'text-indigo-300' : ''"
                            :aria-label="showPassword ? 'Hide password' : 'Show password'"
                            :aria-pressed="showPassword.toString()"
                            :title="showPassword ? 'Hide password' : 'Show password'"
                        >
                            <i
                                class="fa-regular text-[15px]"
                                :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"
                                aria-hidden="true"
                            ></i>
                        </button>
                    </div>

                    {{-- Força da senha --}}
                    <div x-show="password.length" x-cloak x-transition.opacity class="mt-3 flex items-center gap-3" aria-hidden="true">
                        <div class="grid flex-1 grid-cols-4 gap-1.5">
                            <template x-for="n in 4" :key="n">
                                <span
                                    class="h-1 rounded-full transition-colors duration-300"
                                    :class="score >= n ? color : 'bg-white/10'"
                                ></span>
                            </template>
                        </div>
                        <span class="w-12 text-right text-xs font-medium text-gray-400" x-text="label"></span>
                    </div>

                    <p id="password-hint" class="mt-2 text-xs leading-5 text-gray-500">
                        Use 8 or more characters with a mix of letters, numbers and symbols.
                    </p>

                    <p
                        x-show="capsLock"
                        x-cloak
                        x-transition.opacity
                        role="status"
                        class="mt-1 flex items-center gap-1.5 text-xs text-amber-300"
                    >
                        <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
                        Caps Lock is on.
                    </p>

                    @error('password')
                        <p id="password-error" class="mt-2 flex items-center gap-1.5 text-xs text-red-400">
                            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    :disabled="loading"
                    class="group relative mt-2 flex h-12 w-full items-center justify-center gap-2 overflow-hidden rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 text-sm font-semibold text-white shadow-lg shadow-indigo-950/40 transition duration-200 hover:shadow-indigo-500/25 focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/40 active:scale-[0.99] disabled:cursor-wait disabled:opacity-80"
                >
                    <span
                        class="pointer-events-none absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/20 to-transparent transition-transform duration-700 group-hover:translate-x-full"
                        aria-hidden="true"
                    ></span>

                    <span x-show="!loading" class="relative flex items-center gap-2">
                        Create account
                        <i class="fa-solid fa-arrow-right text-xs transition-transform group-hover:translate-x-1" aria-hidden="true"></i>
                    </span>

                    <span x-show="loading" x-cloak class="relative flex items-center gap-2">
                        <i class="fa-solid fa-circle-notch fa-spin text-xs" aria-hidden="true"></i>
                        Creating account…
                    </span>
                </button>
            </form>

            {{-- Login link --}}
            <p class="mt-8 text-center text-sm text-gray-400">
                Already have an account?
                <a
                    href="{{ route('login') }}"
                    class="ml-1 font-semibold text-indigo-400 transition-colors hover:text-indigo-300 focus:outline-none focus-visible:underline"
                >
                    Sign in
                </a>
            </p>
        </main>

        <footer class="relative z-10 text-center text-xs text-gray-600 lg:text-left">
            <i class="fa-solid fa-shield-halved mr-1" aria-hidden="true"></i>
            Secure access to your support workspace.
        </footer>
    </section>

    {{-- ============================== SHOWCASE ============================== --}}
    <aside
        class="iris-stage relative hidden flex-col justify-between overflow-hidden border-l border-white/[0.06] p-12 lg:flex xl:p-16"
        aria-hidden="true"
        x-data="{ px: 0, py: 0 }"
        @mousemove.window="
            const r = $el.getBoundingClientRect();
            const dx = ($event.clientX - (r.left + r.width / 2)) / r.width;
            const dy = ($event.clientY - (r.top + r.height / 2)) / r.height;
            px = Math.max(-1, Math.min(1, dx * 2)) * 16;
            py = Math.max(-1, Math.min(1, dy * 2)) * 16;
        "
    >
        {{-- Iris --}}
        <div class="relative mx-auto mt-6 aspect-square w-[min(78%,30rem)]">

            <svg viewBox="0 0 400 400" class="h-full w-full" fill="none">
                <defs>
                    <radialGradient id="irisGlow" cx="50%" cy="50%" r="50%">
                        <stop offset="0" stop-color="#818cf8" stop-opacity=".0"/>
                        <stop offset=".6" stop-color="#6366f1" stop-opacity=".18"/>
                        <stop offset="1" stop-color="#8b5cf6" stop-opacity="0"/>
                    </radialGradient>
                    <linearGradient id="irisRing" x1="0" y1="0" x2="400" y2="400" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#818cf8"/>
                        <stop offset=".55" stop-color="#a78bfa"/>
                        <stop offset="1" stop-color="#e879f9"/>
                    </linearGradient>
                    <radialGradient id="irisInner" cx="50%" cy="50%" r="50%">
                        <stop offset=".35" stop-color="#05050b" stop-opacity="0"/>
                        <stop offset="1" stop-color="#4f46e5" stop-opacity=".35"/>
                    </radialGradient>
                </defs>

                <circle cx="200" cy="200" r="198" fill="url(#irisGlow)"/>
                <circle cx="200" cy="200" r="188" stroke="#fff" stroke-opacity=".08"/>

                <g class="iris-ring-a">
                    <circle cx="200" cy="200" r="172" stroke="url(#irisRing)" stroke-width="1.5" stroke-dasharray="2 8" opacity=".7"/>
                    <circle cx="200" cy="200" r="148" stroke="#818cf8" stroke-opacity=".38" stroke-width="40" stroke-dasharray="1 5"/>
                </g>

                <g class="iris-ring-b">
                    <circle cx="200" cy="200" r="108" stroke="url(#irisRing)" stroke-opacity=".6" stroke-width="56" stroke-dasharray="1.2 4.6"/>
                    <circle cx="200" cy="200" r="82" stroke="#fff" stroke-opacity=".12"/>
                </g>

                <circle cx="200" cy="200" r="78" fill="url(#irisInner)"/>

                {{-- Pupila acompanha o cursor --}}
                <g :style="`transform: translate(${px}px, ${py}px); transition: transform .25s ease-out;`">
                    <circle cx="200" cy="200" r="42" fill="#05050b" stroke="#a5b4fc" stroke-opacity=".55" stroke-width="1.5"/>
                    <circle cx="187" cy="187" r="7.5" fill="#fff" opacity=".9"/>
                    <circle cx="214" cy="213" r="3" fill="#fff" opacity=".4"/>
                </g>
            </svg>

            {{-- Chips --}}
            <div class="absolute -left-6 top-[16%] flex items-center gap-3 rounded-2xl border border-white/10 bg-gray-950/70 py-2.5 pl-2.5 pr-4 shadow-2xl shadow-black/40 backdrop-blur-xl">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-white">
                    <i class="fa-solid fa-user-plus text-sm"></i>
                </span>
                <div class="leading-tight">
                    <p class="text-[13px] font-medium text-white">Ticket assigned to you</p>
                    <p class="mt-0.5 text-[11px] text-indigo-300">Billing · High priority</p>
                </div>
            </div>

            <div class="absolute -right-6 bottom-[12%] flex items-center gap-3 rounded-2xl border border-white/10 bg-gray-950/70 py-2.5 pl-2.5 pr-4 shadow-2xl shadow-black/40 backdrop-blur-xl">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-300">
                    <i class="fa-solid fa-check text-sm"></i>
                </span>
                <div class="leading-tight">
                    <p class="text-[13px] font-medium text-white">Handoff accepted</p>
                    <p class="mt-0.5 text-[11px] text-emerald-300">Marina took over · just now</p>
                </div>
            </div>
        </div>

        {{-- Copy --}}
        <div class="relative max-w-md">
            <h2 class="iris-display text-4xl font-bold leading-[1.05] tracking-tight text-white text-balance xl:text-5xl">
                Give every request a clear owner.
            </h2>
            <p class="mt-4 text-[15px] leading-6 text-gray-400">
                Create your account to triage, reply and hand off tickets with your team, all in one place.
            </p>
        </div>
    </aside>
</div>
@endsection