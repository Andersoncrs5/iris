
@extends('layouts.base')

@section('title', 'Update Password - Iris App')

@section('content')

<div
    x-data="{
        showPassword: false,
        showConfirmation: false,
    }"
    class="min-h-full"
>


{{-- =========================================================
     PAGE HEADER
========================================================== --}}
<div class="mb-8">

    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

        <div>

            <div class="mb-3 flex items-center gap-2">

                <span
                    class="inline-flex h-9 w-9 items-center justify-center
                           rounded-xl bg-amber-500/10
                           text-amber-400
                           ring-1 ring-amber-500/20"
                >
                    <i class="fa-solid fa-key"></i>
                </span>

                <span
                    class="text-xs font-semibold uppercase
                           tracking-[0.18em] text-amber-400"
                >
                    Security
                </span>

            </div>


            <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                Update Password
            </h1>

            <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                Change your account password to keep your Iris account secure.
            </p>

        </div>


        {{-- BACK --}}
        @if(Route::has('user.profile'))

            <a
                href="{{ route('user.profile') }}"
                class="inline-flex items-center justify-center gap-2
                       rounded-xl border border-white/10
                       bg-white/5 px-4 py-2.5
                       text-sm font-semibold text-gray-300
                       transition
                       hover:bg-white/10 hover:text-white
                       active:scale-[0.98]"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Back to Profile
            </a>

        @endif

    </div>

</div>


{{-- =========================================================
     MAIN LAYOUT
========================================================== --}}
<div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">


    {{-- =====================================================
         FORM
    ====================================================== --}}
    <div>

        <form
            action="{{ route('user.update.password') }}"
            method="POST"
            class="overflow-hidden rounded-2xl
                   border border-white/5
                   bg-gray-900/80
                   shadow-2xl shadow-black/10"
        >

            @csrf
            @method('PUT')


            {{-- =================================================
                 PASSWORD
            ================================================== --}}
            <section>

                <div
                    class="border-b border-white/5
                           px-6 py-5 sm:px-7"
                >

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-9 w-9 items-center justify-center
                                   rounded-xl bg-amber-500/10
                                   text-amber-400"
                        >
                            <i class="fa-solid fa-lock"></i>
                        </div>

                        <div>

                            <h2 class="text-sm font-bold text-white">
                                New password
                            </h2>

                            <p class="mt-0.5 text-xs text-gray-500">
                                Choose a strong password for your account.
                            </p>

                        </div>

                    </div>

                </div>


                <div class="space-y-5 p-6 sm:p-7">

                    {{-- PASSWORD --}}
                    <div>

                        <label
                            for="password"
                            class="mb-2 block text-xs
                                   font-semibold uppercase
                                   tracking-wider text-gray-400"
                        >
                            New password
                            <span class="text-red-400">*</span>
                        </label>


                        <div class="relative">

                            <i
                                class="fa-solid fa-key
                                       pointer-events-none
                                       absolute left-3.5 top-1/2
                                       -translate-y-1/2
                                       text-sm text-gray-500"
                            ></i>


                            <input
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                id="password"
                                required
                                autocomplete="new-password"
                                placeholder="Enter your new password"
                                class="w-full rounded-xl
                                       border border-white/10
                                       bg-gray-950/60
                                       py-3 pl-10 pr-12
                                       text-sm text-white
                                       placeholder:text-gray-600
                                       outline-none transition
                                       focus:border-amber-500/50
                                       focus:ring-4 focus:ring-amber-500/10"
                            />


                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3.5 top-1/2
                                       -translate-y-1/2
                                       text-gray-500 transition
                                       hover:text-gray-200"
                                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                            >
                                <i
                                    class="fa-solid"
                                    :class="showPassword
                                        ? 'fa-eye-slash'
                                        : 'fa-eye'"
                                ></i>
                            </button>

                        </div>


                        <p class="mt-2 text-xs text-gray-600">
                            Use at least 8 characters.
                        </p>


                        @error('password')

                            <p
                                class="mt-2 flex items-center gap-1.5
                                       text-xs text-red-400"
                            >
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ $message }}
                            </p>

                        @enderror

                    </div>


                    {{-- CONFIRM PASSWORD --}}
                    <div>

                        <label
                            for="password_confirmation"
                            class="mb-2 block text-xs
                                   font-semibold uppercase
                                   tracking-wider text-gray-400"
                        >
                            Confirm password
                            <span class="text-red-400">*</span>
                        </label>


                        <div class="relative">

                            <i
                                class="fa-solid fa-shield-check
                                       pointer-events-none
                                       absolute left-3.5 top-1/2
                                       -translate-y-1/2
                                       text-sm text-gray-500"
                            ></i>


                            <input
                                :type="showConfirmation ? 'text' : 'password'"
                                name="password_confirmation"
                                id="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Repeat your new password"
                                class="w-full rounded-xl
                                       border border-white/10
                                       bg-gray-950/60
                                       py-3 pl-10 pr-12
                                       text-sm text-white
                                       placeholder:text-gray-600
                                       outline-none transition
                                       focus:border-amber-500/50
                                       focus:ring-4 focus:ring-amber-500/10"
                            />


                            <button
                                type="button"
                                @click="showConfirmation = !showConfirmation"
                                class="absolute right-3.5 top-1/2
                                       -translate-y-1/2
                                       text-gray-500 transition
                                       hover:text-gray-200"
                                :aria-label="showConfirmation ? 'Hide password confirmation' : 'Show password confirmation'"
                            >
                                <i
                                    class="fa-solid"
                                    :class="showConfirmation
                                        ? 'fa-eye-slash'
                                        : 'fa-eye'"
                                ></i>
                            </button>

                        </div>


                        @error('password_confirmation')

                            <p
                                class="mt-2 flex items-center gap-1.5
                                       text-xs text-red-400"
                            >
                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ $message }}
                            </p>

                        @enderror

                    </div>


                    {{-- WARNING --}}
                    <div
                        class="rounded-xl
                               border border-amber-500/10
                               bg-amber-500/5
                               p-4"
                    >

                        <div class="flex gap-3">

                            <i
                                class="fa-solid fa-triangle-exclamation
                                       mt-0.5 text-amber-400"
                            ></i>

                            <div>

                                <p class="text-sm font-semibold text-gray-200">
                                    Important
                                </p>

                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Once your password is changed, use the new
                                    password the next time you sign in.
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </section>


            {{-- =================================================
                 FOOTER
            ================================================== --}}
            <div
                class="flex flex-col-reverse gap-3
                       border-t border-white/5
                       bg-gray-950/30
                       px-6 py-5
                       sm:flex-row sm:items-center
                       sm:justify-end sm:px-7"
            >

                @if(Route::has('user.profile'))

                    <a
                        href="{{ route('user.profile') }}"
                        class="inline-flex items-center justify-center
                               gap-2 rounded-xl
                               border border-white/10
                               bg-white/5 px-5 py-2.5
                               text-sm font-semibold text-gray-300
                               transition
                               hover:bg-white/10 hover:text-white
                               active:scale-[0.98]"
                    >
                        Cancel
                    </a>

                @endif


                <button
                    type="submit"
                    class="inline-flex items-center justify-center
                           gap-2 rounded-xl
                           bg-amber-600 px-6 py-2.5
                           text-sm font-bold text-white
                           shadow-lg shadow-amber-600/20
                           transition
                           hover:-translate-y-0.5
                           hover:bg-amber-500
                           active:scale-[0.98]"
                >
                    <i class="fa-solid fa-key"></i>
                    Update Password
                </button>

            </div>

        </form>

    </div>


    {{-- =====================================================
         SIDEBAR
    ====================================================== --}}
    <aside class="space-y-4">


        {{-- =================================================
             SECURITY OVERVIEW
        ================================================== --}}
        <div
            class="overflow-hidden rounded-2xl
                   border border-white/5
                   bg-gray-900/80
                   shadow-2xl shadow-black/10"
        >

            <div
                class="border-b border-white/5
                       px-5 py-4"
            >

                <div class="flex items-center gap-3">

                    <div
                        class="flex h-8 w-8
                               items-center justify-center
                               rounded-lg bg-amber-500/10
                               text-amber-400"
                    >
                        <i class="fa-solid fa-shield-halved text-xs"></i>
                    </div>

                    <div>

                        <h2 class="text-sm font-bold text-white">
                            Account security
                        </h2>

                        <p class="text-xs text-gray-500">
                            Password protection
                        </p>

                    </div>

                </div>

            </div>


            <div class="p-5">

                <div
                    class="rounded-xl
                           border border-white/5
                           bg-gray-950/50 p-4"
                >

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-10 w-10
                                   items-center justify-center
                                   rounded-xl
                                   bg-emerald-500/10
                                   text-emerald-400"
                        >
                            <i class="fa-solid fa-lock"></i>
                        </div>

                        <div>

                            <p class="text-sm font-semibold text-white">
                                Password protected
                            </p>

                            <p class="mt-1 text-xs text-gray-500">
                                Your password is securely stored.
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- =================================================
             PASSWORD GUIDELINES
        ================================================== --}}
        <div
            class="rounded-2xl
                   border border-white/5
                   bg-gray-900/80
                   p-5
                   shadow-2xl shadow-black/10"
        >

            <div class="flex items-start gap-3">

                <div
                    class="flex h-9 w-9 flex-shrink-0
                           items-center justify-center
                           rounded-xl bg-indigo-500/10
                           text-indigo-400"
                >
                    <i class="fa-solid fa-list-check"></i>
                </div>

                <div>

                    <h3 class="text-sm font-bold text-white">
                        Password guidelines
                    </h3>

                    <ul class="mt-3 space-y-2 text-xs text-gray-500">

                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-emerald-400"></i>
                            At least 8 characters
                        </li>

                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-emerald-400"></i>
                            Avoid easily guessed passwords
                        </li>

                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-emerald-400"></i>
                            Do not reuse old passwords
                        </li>

                    </ul>

                </div>

            </div>

        </div>


        {{-- =================================================
             ACCOUNT
        ================================================== --}}
        <div
            class="rounded-2xl
                   border border-white/5
                   bg-gray-900/80
                   p-5
                   shadow-2xl shadow-black/10"
        >

            <div class="flex items-start gap-3">

                <div
                    class="flex h-9 w-9 flex-shrink-0
                           items-center justify-center
                           rounded-xl bg-violet-500/10
                           text-violet-400"
                >
                    <i class="fa-solid fa-user"></i>
                </div>

                <div class="min-w-0">

                    <h3 class="text-sm font-bold text-white">
                        Account
                    </h3>

                    <p class="mt-2 truncate text-sm font-semibold text-gray-300">
                        {{ $user->name }}
                    </p>

                    <p class="mt-1 truncate text-xs text-gray-500">
                        {{ $user->email }}
                    </p>

                </div>

            </div>

        </div>


        {{-- ACCOUNT ID --}}
        <div
            class="rounded-2xl
                   border border-white/5
                   bg-gray-900/60
                   px-5 py-4"
        >

            <div class="flex items-center justify-between gap-4">

                <span
                    class="text-[10px] font-semibold
                           uppercase tracking-wider
                           text-gray-600"
                >
                    Account ID
                </span>

                <span
                    class="truncate font-mono text-[11px]
                           text-gray-500"
                >
                    {{ $user->id }}
                </span>

            </div>

        </div>

    </aside>

</div>

</div>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>

@endsection
