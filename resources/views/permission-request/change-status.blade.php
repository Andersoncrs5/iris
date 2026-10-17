@extends('layouts.base')

@section('title', 'Change Request Status - Iris')

@section('content')
    @php
        $permission = $permissionRequest->permission;

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

        $statusIcon = match ($statusValue) {
            'approved' => 'fa-solid fa-circle-check',
            'rejected' => 'fa-solid fa-circle-xmark',
            'pending' => 'fa-regular fa-clock',
            default => 'fa-solid fa-circle-info',
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

                    <span>Change Status #{{ $permissionRequest->id }}</span>
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-white">
                    Change Request Status
                </h1>

                <p class="mt-1 text-sm text-gray-400">
                    Review the request and record your decision.
                </p>
            </div>

            <span class="inline-flex w-fit shrink-0 items-center gap-2 rounded-lg border px-3 py-2 text-xs font-semibold {{ $statusClasses }}">
                <i class="{{ $statusIcon }}" aria-hidden="true"></i>
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

        {{-- Back navigation --}}
        <div>
            <a
                href="{{ route('permission.request.show', ['id' => $permissionRequest->id]) }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-white/10 px-4 py-2.5 text-sm font-medium text-gray-300 transition hover:border-white/20 hover:bg-white/[0.04] hover:text-white"
            >
                <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                Back to Request Details
            </a>
        </div>

        {{-- Request summary --}}
        <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-900/70 shadow-lg shadow-black/10">

            <div class="flex flex-col gap-4 border-b border-white/[0.07] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="flex min-w-0 items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-indigo-400/15 bg-indigo-500/10 text-indigo-300">
                        <i class="fa-solid fa-file-circle-question text-lg" aria-hidden="true"></i>
                    </div>

                    <div class="min-w-0">
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            Request #{{ $permissionRequest->id }}
                        </p>

                        <h2 class="mt-1 break-words text-lg font-semibold text-white">
                            {{ $permission?->name ?? "Permission #{$permissionRequest->permission_id}" }}
                        </h2>

                        <p class="mt-1 text-xs text-gray-500">
                            Submitted
                            {{ $permissionRequest->created_at?->format('d/m/Y \a\t H:i') ?? '—' }}
                        </p>
                    </div>
                </div>

                <span class="inline-flex w-fit shrink-0 items-center gap-2 rounded-lg border px-3 py-2 text-xs font-semibold {{ $statusClasses }}">
                    <i class="{{ $statusIcon }}" aria-hidden="true"></i>
                    Current: {{ $statusLabel }}
                </span>
            </div>

            <div class="grid grid-cols-1 divide-y divide-white/[0.06] sm:grid-cols-2 sm:divide-y-0">

                {{-- Permission --}}
                <div class="flex items-start gap-3 px-5 py-4 sm:border-r sm:border-white/[0.06] sm:px-6">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-indigo-400/15 bg-indigo-500/[0.07] text-indigo-300">
                        <i class="fa-solid fa-key" aria-hidden="true"></i>
                    </div>

                    <div class="min-w-0">
                        <p class="text-xs text-gray-500">Requested permission</p>

                        <p class="mt-1 break-words text-sm font-semibold text-gray-200">
                            {{ $permission?->name ?? "Permission #{$permissionRequest->permission_id}" }}
                        </p>

                        @if ($permission && filled($permission->slug))
                            <p class="mt-1 break-all font-mono text-xs text-gray-500">
                                {{ $permission->slug }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Request date --}}
                <div class="flex items-start gap-3 px-5 py-4 sm:px-6">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-white/[0.07] bg-white/[0.03] text-gray-400">
                        <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Last updated</p>

                        <p class="mt-1 text-sm font-semibold text-gray-200">
                            {{ $permissionRequest->updated_at?->format('d/m/Y H:i') ?? '—' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Request justification --}}
            <div class="border-t border-white/[0.07] px-5 py-5 sm:px-6">
                <div class="mb-2 flex items-center gap-2">
                    <i class="fa-regular fa-file-lines text-xs text-gray-500" aria-hidden="true"></i>

                    <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Request Justification
                    </h3>
                </div>

                <p class="whitespace-pre-line break-words text-sm leading-6 text-gray-300">{{ $permissionRequest->reason }}</p>
            </div>

            @if (filled($permissionRequest->review_comment))
                <div class="border-t border-white/[0.07] px-5 py-5 sm:px-6">
                    <h3 class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Previous Review Comment
                    </h3>

                    <p class="whitespace-pre-line break-words text-sm leading-6 text-gray-400">{{ $permissionRequest->review_comment }}</p>
                </div>
            @endif
        </section>

        {{-- Change status form --}}
        <section class="overflow-hidden rounded-2xl border border-white/10 bg-gray-900/70 shadow-lg shadow-black/10">

            <div class="border-b border-white/[0.07] px-5 py-5 sm:px-6">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-amber-400/15 bg-amber-500/10 text-amber-300">
                        <i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i>
                    </div>

                    <div>
                        <h2 class="text-base font-semibold text-white">
                            Review Decision
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Select the new status and provide a review comment if necessary.
                        </p>
                    </div>
                </div>
            </div>

            <form
                action="{{ route('permission.request.change-status.form', ['id' => $permissionRequest->id]) }}"
                method="POST"
                class="space-y-6 p-5 sm:p-6"
            >
                @csrf

                {{-- Status --}}
                <div>
                    <label for="status" class="mb-2 block text-sm font-medium text-gray-200">
                        New Status <span class="text-red-400">*</span>
                    </label>

                    <div class="relative">
                        <select
                            id="status"
                            name="status"
                            required
                            class="block w-full appearance-none rounded-xl border border-white/10 bg-gray-950/60 px-4 py-3.5 pr-10 text-sm text-gray-100 outline-none transition hover:border-white/15 focus:border-indigo-400/50 focus:ring-4 focus:ring-indigo-500/10 @error('status') border-red-400/50 focus:border-red-400/60 focus:ring-red-500/10 @enderror"
                        >
                            <option value="" disabled @selected(old('status') === null)>
                                Select a status
                            </option>

                            @foreach (\App\Utils\Enums\PermissionRequestStatus\PermissionRequestStatus::cases() as $status)
                                <option
                                    value="{{ $status->value }}"
                                    @selected(
                                        old('status', $statusValue) === $status->value
                                    )
                                >
                                    {{ ucwords(str_replace(['_', '-'], ' ', $status->value)) }}
                                </option>
                            @endforeach
                        </select>

                        <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-gray-500" aria-hidden="true"></i>
                    </div>

                    @error('status')
                        <p class="mt-2 flex items-center gap-2 text-xs text-red-400">
                            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                            {{ $message }}
                        </p>
                    @enderror

                    <p class="mt-2 text-xs leading-5 text-gray-500">
                        Changing the status records the review date and the user who reviewed the request.
                    </p>
                </div>

                {{-- Review comment --}}
                <div>
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <label for="review_comment" class="text-sm font-medium text-gray-200">
                            Review Comment
                        </label>

                        <span class="text-xs text-gray-600">
                            Max. 5,000 characters
                        </span>
                    </div>

                    <textarea
                        id="review_comment"
                        name="review_comment"
                        rows="5"
                        maxlength="5000"
                        placeholder="Explain your decision or provide feedback to the requester..."
                        class="block w-full resize-y rounded-xl border border-white/10 bg-gray-950/60 px-4 py-3.5 text-sm leading-6 text-gray-100 outline-none transition placeholder:text-gray-600 hover:border-white/15 focus:border-indigo-400/50 focus:ring-4 focus:ring-indigo-500/10 @error('review_comment') border-red-400/50 focus:border-red-400/60 focus:ring-red-500/10 @enderror"
                    >{{ old('review_comment', $permissionRequest->review_comment) }}</textarea>

                    @error('review_comment')
                        <p class="mt-2 flex items-center gap-2 text-xs text-red-400">
                            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                            {{ $message }}
                        </p>
                    @enderror

                    <p class="mt-2 text-xs leading-5 text-gray-500">
                        A clear comment helps the requester understand the outcome.
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
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-semibold text-gray-950 shadow-md shadow-amber-500/10 transition hover:bg-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-500/20 active:scale-[0.98]"
                    >
                        <i class="fa-solid fa-floppy-disk text-xs" aria-hidden="true"></i>
                        Save Status
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection