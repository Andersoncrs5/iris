@extends('layouts.base')

@section('title', 'Request a Permission - Iris')

@section('content')
    <div class="mx-auto w-full max-w-5xl space-y-6 pb-12">

        {{-- Page header --}}
        <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-xs font-medium text-gray-500">
                    <i class="fa-solid fa-route text-indigo-400" aria-hidden="true"></i>
                    <span>Permission Requests</span>
                    <i class="fa-solid fa-chevron-right text-[9px]" aria-hidden="true"></i>
                    <span>New Request</span>
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-white">
                    Request a Permission
                </h1>

                <p class="mt-1 text-sm text-gray-400">
                    Select the permission you need and explain why you require access.
                </p>
            </div>

            <a
                href="{{ route('permission.request.my') }}"
                class="inline-flex items-center justify-center gap-2 self-start rounded-lg border border-white/10 px-4 py-2.5 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:bg-white/[0.04] hover:text-white sm:self-center"
            >
                <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                My Requests
            </a>
        </header>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="flex items-center gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/[0.08] px-4 py-3 text-sm text-emerald-300">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 rounded-xl border border-red-500/20 bg-red-500/[0.08] px-4 py-3 text-sm text-red-300">
                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                {{ session('error') }}
            </div>
        @endif

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="rounded-xl border border-red-500/20 bg-red-500/[0.08] px-4 py-3 text-sm text-red-300">
                <div class="mb-2 flex items-center gap-2 font-semibold">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                    Please correct the following errors.
                </div>

                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Request form --}}
        <form
            action="{{ route('permission.request.create.form') }}"
            method="POST"
            class="space-y-6"
        >
            @csrf

            {{-- Permission selection --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-900/70 shadow-lg shadow-black/10">

                <div class="border-b border-white/[0.07] px-5 py-5 sm:px-6">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-indigo-400/15 bg-indigo-500/10 text-indigo-300">
                            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                        </div>

                        <div>
                            <h2 class="text-base font-semibold text-white">
                                Available Permissions
                            </h2>

                            <p class="mt-1 text-sm text-gray-500">
                                Choose the permission you want to request.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3 p-5 sm:p-6">

                    @forelse ($permissions as $permission)
                        <label class="block cursor-pointer">
                            <input
                                type="radio"
                                name="permission_id"
                                value="{{ $permission->id }}"
                                class="peer sr-only"
                                @checked((string) old('permission_id') === (string) $permission->id)
                                required
                            >

                            <div class="rounded-xl border border-white/10 bg-gray-950/40 p-4 transition hover:border-indigo-400/30 hover:bg-indigo-500/[0.03] peer-checked:border-indigo-400/60 peer-checked:bg-indigo-500/[0.08] peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-400/50">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-white/[0.07] bg-white/[0.03] text-gray-400 transition peer-checked:text-indigo-300">
                                        <i class="fa-solid fa-key text-sm" aria-hidden="true"></i>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <h3 class="break-words text-sm font-semibold text-gray-100">
                                                {{ $permission->name }}
                                            </h3>

                                            <span class="inline-flex items-center gap-1.5 text-xs text-gray-500">
                                                <i class="fa-regular fa-circle-check" aria-hidden="true"></i>
                                                Select
                                            </span>
                                        </div>

                                        @if (filled($permission->slug))
                                            <p class="mt-1 break-all font-mono text-xs text-indigo-300/80">
                                                {{ $permission->slug }}
                                            </p>
                                        @endif

                                        @if (filled($permission->description))
                                            <p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-gray-400">
                                                {{ $permission->description }}
                                            </p>
                                        @endif

                                        <p class="mt-2 text-xs text-gray-600">
                                            Permission ID: {{ $permission->id }}
                                        </p>
                                    </div>

                                    <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-white/20 text-transparent peer-checked:border-indigo-400 peer-checked:bg-indigo-500 peer-checked:text-white">
                                        <i class="fa-solid fa-check text-[10px]" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </label>
                    @empty
                        <div class="rounded-xl border border-dashed border-white/10 px-6 py-12 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white/[0.04] text-gray-500">
                                <i class="fa-solid fa-shield-halved text-lg" aria-hidden="true"></i>
                            </div>

                            <h3 class="mt-4 text-sm font-semibold text-white">
                                No permissions available
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                There are currently no permissions available to request.
                            </p>
                        </div>
                    @endforelse

                    @error('permission_id')
                        <p class="flex items-center gap-2 text-xs text-red-400">
                            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                            {{ $message }}
                        </p>
                    @enderror

                    {{-- Pagination --}}
                    @if ($permissions->hasPages())
                        <div class="border-t border-white/[0.07] pt-4">
                            {{ $permissions->links() }}
                        </div>
                    @endif
                </div>
            </section>

            {{-- Justification --}}
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-900/70 shadow-lg shadow-black/10">

                <div class="border-b border-white/[0.07] px-5 py-5 sm:px-6">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-indigo-400/15 bg-indigo-500/10 text-indigo-300">
                            <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                        </div>

                        <div>
                            <h2 class="text-base font-semibold text-white">
                                Request Justification
                            </h2>

                            <p class="mt-1 text-sm text-gray-500">
                                Explain why you need this permission.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-5 sm:p-6">
                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label for="reason" class="text-sm font-medium text-gray-200">
                                Reason <span class="text-red-400">*</span>
                            </label>

                            <span class="text-xs text-gray-600">
                                Max. 2,000 characters
                            </span>
                        </div>

                        <textarea
                            id="reason"
                            name="reason"
                            rows="5"
                            maxlength="2000"
                            required
                            placeholder="Describe why you need this permission and what you intend to use it for..."
                            class="block w-full resize-y rounded-xl border border-white/10 bg-gray-950/60 px-4 py-3.5 text-sm leading-6 text-gray-100 outline-none transition placeholder:text-gray-600 hover:border-white/15 focus:border-indigo-400/50 focus:ring-4 focus:ring-indigo-500/10 @error('reason') border-red-400/50 focus:border-red-400/60 focus:ring-red-500/10 @enderror"
                        >{{ old('reason') }}</textarea>

                        @error('reason')
                            <p class="mt-2 flex items-center gap-2 text-xs text-red-400">
                                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                                {{ $message }}
                            </p>
                        @enderror

                        <p class="mt-2 text-xs leading-5 text-gray-500">
                            Provide enough detail to help the responsible team evaluate your request.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Actions --}}
            <div class="flex flex-col-reverse gap-3 border-t border-white/[0.07] pt-5 sm:flex-row sm:items-center sm:justify-end">
                <a
                    href="{{ route('permission.request.my') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-white/10 px-5 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/[0.04] hover:text-white"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    @disabled($permissions->isEmpty())
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-500/10 transition hover:bg-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-indigo-500"
                >
                    <i class="fa-solid fa-paper-plane text-xs" aria-hidden="true"></i>
                    Submit Request
                </button>
            </div>
        </form>
    </div>
@endsection