@extends('layouts.base')

@section('title', 'Edit Permission Request - Iris')

@section('content')
    @php
        $statusValue = strtolower(
            (string) $permissionRequest->getRawOriginal('status')
        );

        $statusLabel = ucwords(
            str_replace(['_', '-'], ' ', $statusValue ?: 'pending')
        );

        $statusClasses = match ($statusValue) {
            'approved' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',
            'rejected' => 'border-red-500/20 bg-red-500/10 text-red-300',
            'pending' => 'border-amber-500/20 bg-amber-500/10 text-amber-300',
            default => 'border-white/10 bg-white/[0.04] text-gray-300',
        };
    @endphp

    <div class="mx-auto w-full max-w-5xl space-y-6 pb-12">

        {{-- Page header --}}
        <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="mb-2 flex flex-wrap items-center gap-2 text-xs font-medium text-gray-500">
                    <i class="fa-solid fa-shield-halved text-indigo-400" aria-hidden="true"></i>

                    <span>Permissions</span>

                    <i class="fa-solid fa-chevron-right text-[9px]" aria-hidden="true"></i>

                    <a
                        href="{{ route('permission.request.my') }}"
                        class="transition hover:text-gray-300"
                    >
                        My Requests
                    </a>

                    <i class="fa-solid fa-chevron-right text-[9px]" aria-hidden="true"></i>

                    <span>Edit #{{ $permissionRequest->id }}</span>
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-white">
                    Edit Permission Request
                </h1>

                <p class="mt-1 text-sm text-gray-400">
                    Update the requested permission or provide a clearer justification.
                </p>
            </div>

            <span class="inline-flex w-fit shrink-0 items-center gap-2 rounded-lg border px-3 py-2 text-xs font-semibold {{ $statusClasses }}">
                <i class="fa-regular fa-clock" aria-hidden="true"></i>
                {{ $statusLabel }}
            </span>
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

        {{-- Back button above the form --}}
        <div>
            <a
                href="{{ route('permission.request.show', ['id' => $permissionRequest->id]) }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-white/10 px-4 py-2.5 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:bg-white/[0.04] hover:text-white"
            >
                <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                Back to Details
            </a>
        </div>

        {{-- Request summary --}}
        <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-900/70 shadow-lg shadow-black/10">
            <div class="flex flex-col gap-4 border-b border-white/[0.07] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="flex min-w-0 items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-indigo-400/15 bg-indigo-500/10 text-indigo-300">
                        <i class="fa-solid fa-file-pen text-lg" aria-hidden="true"></i>
                    </div>

                    <div class="min-w-0">
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            Request #{{ $permissionRequest->id }}
                        </p>

                        <h2 class="mt-1 break-words text-lg font-semibold text-white">
                            {{ $permissionRequest->permission?->name ?? "Permission #{$permissionRequest->permission_id}" }}
                        </h2>

                        <p class="mt-1 text-xs text-gray-500">
                            Submitted
                            {{ $permissionRequest->created_at?->format('d/m/Y \a\t H:i') ?? '—' }}
                        </p>
                    </div>
                </div>

                <div class="text-xs text-gray-500">
                    Last updated
                    <p class="mt-1 text-sm font-medium text-gray-300">
                        {{ $permissionRequest->updated_at?->format('d/m/Y H:i') ?? '—' }}
                    </p>
                </div>
            </div>
        </section>

        {{-- Edit form --}}
        <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-900/70 shadow-lg shadow-black/10">

            <div class="border-b border-white/[0.07] px-5 py-5 sm:px-6">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-indigo-400/15 bg-indigo-500/10 text-indigo-300">
                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                    </div>

                    <div>
                        <h2 class="text-base font-semibold text-white">
                            Update Request Information
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Changes will be saved to this permission request.
                        </p>
                    </div>
                </div>
            </div>

            <form
                action="{{ route('permission.request.update', ['id' => $permissionRequest->id]) }}"
                method="POST"
                class="space-y-6 p-5 sm:p-6"
            >
                @csrf
                @method('PUT')

                {{-- Permission selection --}}
                <div>
                    <label for="permission_id" class="mb-2 block text-sm font-medium text-gray-200">
                        Requested Permission <span class="text-red-400">*</span>
                    </label>

                    <div class="relative">
                        <select
                            id="permission_id"
                            name="permission_id"
                            required
                            class="block w-full appearance-none rounded-xl border border-white/10 bg-gray-950/60 px-4 py-3.5 pr-10 text-sm text-gray-100 outline-none transition hover:border-white/15 focus:border-indigo-400/50 focus:ring-4 focus:ring-indigo-500/10 @error('permission_id') border-red-400/50 focus:border-red-400/60 focus:ring-red-500/10 @enderror"
                        >
                            <option value="" disabled>Select a permission</option>

                            @foreach ($permissions as $permission)
                                <option
                                    value="{{ $permission->id }}"
                                    @selected(
                                        (string) old('permission_id', $permissionRequest->permission_id)
                                        === (string) $permission->id
                                    )
                                >
                                    {{ $permission->name }}
                                    @if (filled($permission->slug))
                                        — {{ $permission->slug }}
                                    @endif
                                </option>
                            @endforeach
                        </select>

                        <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-gray-500" aria-hidden="true"></i>
                    </div>

                    @error('permission_id')
                        <p class="mt-2 flex items-center gap-2 text-xs text-red-400">
                            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                            {{ $message }}
                        </p>
                    @enderror

                    <p class="mt-2 text-xs leading-5 text-gray-500">
                        Choose the permission you need access to.
                    </p>
                </div>

                {{-- Justification --}}
                <div>
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <label for="reason" class="text-sm font-medium text-gray-200">
                            Justification <span class="text-red-400">*</span>
                        </label>

                        <span class="text-xs text-gray-600">
                            Max. 2,000 characters
                        </span>
                    </div>

                    <textarea
                        id="reason"
                        name="reason"
                        rows="7"
                        maxlength="2000"
                        required
                        autofocus
                        placeholder="Explain why you need this permission..."
                        class="block w-full resize-y rounded-xl border border-white/10 bg-gray-950/60 px-4 py-3.5 text-sm leading-6 text-gray-100 outline-none transition placeholder:text-gray-600 hover:border-white/15 focus:border-indigo-400/50 focus:ring-4 focus:ring-indigo-500/10 @error('reason') border-red-400/50 focus:border-red-400/60 focus:ring-red-500/10 @enderror"
                    >{{ old('reason', $permissionRequest->reason) }}</textarea>

                    @error('reason')
                        <p class="mt-2 flex items-center gap-2 text-xs text-red-400">
                            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                            {{ $message }}
                        </p>
                    @enderror

                    <p class="mt-2 text-xs leading-5 text-gray-500">
                        Explain the business need or task that requires this permission.
                    </p>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col-reverse gap-3 border-t border-white/[0.07] pt-5 sm:flex-row sm:items-center sm:justify-end">

                    <a
                        href="{{ route('permission.request.show', ['id' => $permissionRequest->id]) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-white/10 px-5 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/[0.04] hover:text-white"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-500/10 transition hover:bg-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 active:scale-[0.98]"
                    >
                        <i class="fa-solid fa-floppy-disk text-xs" aria-hidden="true"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </section>

        {{-- Review information --}}
        @if ($permissionRequest->reviewed_at || $permissionRequest->review_comment)
            <section class="rounded-2xl border border-white/10 bg-gray-900/70 p-5 sm:p-6">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/[0.07] bg-white/[0.03] text-gray-400">
                        <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                    </div>

                    <div class="min-w-0">
                        <h2 class="text-base font-semibold text-white">
                            Previous Review
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Review information associated with this request.
                        </p>

                        @if ($permissionRequest->reviewed_at)
                            <p class="mt-3 text-sm text-gray-300">
                                Reviewed on
                                {{ $permissionRequest->reviewed_at->format('d/m/Y H:i') }}
                            </p>
                        @endif

                        @if (filled($permissionRequest->review_comment))
                            <p class="mt-3 whitespace-pre-line break-words text-sm leading-6 text-gray-400">
                                {{ $permissionRequest->review_comment }}
                            </p>
                        @endif
                    </div>
                </div>
            </section>
        @endif
    </div>
@endsection