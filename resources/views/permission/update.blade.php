@extends('layouts.base')

@section('title', 'Update Permission - Iris App')

@section('content')

<div
    x-data="{
        permissionName: @js(old('name', $permission->name)),
        slug: @js(old('slug', $permission->slug)),
        module: @js(old('module', $permission->module ?? 'system')),
        description: @js(old('description', $permission->description ?? '')),
        slugManuallyEdited: true,
        slugify(value) {
            return value.toLowerCase().normalize('NFD')
                .replace(/[\\u0300-\\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        },
        updateSlug() {
            if (!this.slugManuallyEdited) this.slug = this.slugify(this.permissionName);
        },
        regenerateSlug() {
            this.slug = this.slugify(this.permissionName);
            this.slugManuallyEdited = false;
        }
    }"
    class="min-h-full"
>

    {{-- PAGE HEADER --}}
    <div class="mb-8">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="mb-3 flex items-center gap-2">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400 ring-1 ring-indigo-500/20">
                        <i class="fa-solid fa-shield-halved"></i>
                    </span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-400">
                        Access Control
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-3xl font-black tracking-tight text-white sm:text-4xl">
                        Update Permission
                    </h1>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-1 text-xs font-bold text-amber-300">
                        <i class="fa-solid fa-pen-to-square"></i>
                        Editing
                    </span>
                </div>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                    Update this permission's name, identifier and module while keeping its access rule consistent across Iris.
                </p>
            </div>

            @if(Route::has('permission.list'))
                <a
                    href="{{ route('permission.list') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-gray-300 transition hover:bg-white/10 hover:text-white active:scale-[0.98]"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Permissions
                </a>
            @endif
        </div>

        {{-- FEEDBACK --}}
        @if(session('error'))
            <div class="mt-6 flex items-start gap-3 rounded-xl border border-red-500/20 bg-red-500/10 p-4">
                <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-400"></i>
                <p class="text-sm text-red-300">{{ session('error') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="mt-6 flex items-start gap-3 rounded-xl border border-red-500/20 bg-red-500/10 p-4">
                <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-400"></i>
                <div>
                    <p class="text-sm font-semibold text-red-300">Please review the fields below.</p>
                    <p class="mt-1 text-xs text-red-300/80">Some information could not be saved.</p>
                </div>
            </div>
        @endif
    </div>

    {{-- FORM LAYOUT --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">

        {{-- MAIN FORM --}}
        <div>
            <form
                action="{{ route('permission.update', $permission->id) }}"
                method="POST"
                class="overflow-hidden rounded-2xl border border-white/5 bg-gray-900/80 shadow-2xl shadow-black/10"
            >
                @csrf
                @method('PUT')

                {{-- BASIC INFORMATION --}}
                <section>
                    <div class="border-b border-white/5 px-6 py-5 sm:px-7">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400">
                                <i class="fa-solid fa-circle-info"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-white">Permission information</h2>
                                <p class="mt-0.5 text-xs text-gray-500">Review and update the identity of this permission.</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-5 p-6 sm:p-7">

                        {{-- NAME --}}
                        <div>
                            <label for="name" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">
                                Permission name <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-shield-halved pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-500"></i>
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    x-model="permissionName"
                                    @input="updateSlug()"
                                    value="{{ old('name', $permission->name) }}"
                                    required
                                    maxlength="150"
                                    autocomplete="off"
                                    placeholder="e.g. Create Ticket"
                                    class="w-full rounded-xl border border-white/10 bg-gray-950/60 py-3 pl-10 pr-4 text-sm text-white placeholder:text-gray-600 outline-none transition focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                                />
                            </div>
                            @error('name')
                                <p class="mt-2 flex items-center gap-1.5 text-xs text-red-400">
                                    <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- SLUG --}}
                        <div>
                            <div class="mb-2 flex flex-wrap items-center justify-between gap-3">
                                <label for="slug" class="block text-xs font-semibold uppercase tracking-wider text-gray-400">
                                    Permission slug <span class="text-red-400">*</span>
                                </label>
                                <button
                                    type="button"
                                    @click="regenerateSlug()"
                                    class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-indigo-400 transition hover:text-indigo-300"
                                >
                                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                                    Regenerate from name
                                </button>
                            </div>
                            <div class="relative">
                                <i class="fa-solid fa-code pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-500"></i>
                                <input
                                    type="text"
                                    name="slug"
                                    id="slug"
                                    x-model="slug"
                                    @input="slugManuallyEdited = true"
                                    value="{{ old('slug', $permission->slug) }}"
                                    required
                                    maxlength="200"
                                    autocomplete="off"
                                    placeholder="e.g. ticket.create"
                                    class="w-full rounded-xl border border-white/10 bg-gray-950/60 py-3 pl-10 pr-4 font-mono text-sm text-white placeholder:text-gray-600 outline-none transition focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                                />
                            </div>
                            <p class="mt-2 text-xs leading-5 text-gray-600">
                                This identifier may be referenced in routes, gates and application logic. Change it carefully.
                            </p>
                            @error('slug')
                                <p class="mt-2 flex items-center gap-1.5 text-xs text-red-400">
                                    <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- MODULE --}}
                        <div>
                            <label for="module" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-gray-400">
                                Module
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-cubes pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-500"></i>
                                <input
                                    type="text"
                                    name="module"
                                    id="module"
                                    x-model="module"
                                    value="{{ old('module', $permission->module ?? 'system') }}"
                                    maxlength="100"
                                    autocomplete="off"
                                    placeholder="e.g. ticketing"
                                    class="w-full rounded-xl border border-white/10 bg-gray-950/60 py-3 pl-10 pr-4 text-sm text-white placeholder:text-gray-600 outline-none transition focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                                />
                            </div>
                            <p class="mt-2 text-xs leading-5 text-gray-600">
                                Group this permission by area, such as <span class="font-mono text-gray-500">ticketing</span>, <span class="font-mono text-gray-500">catalog</span> or <span class="font-mono text-gray-500">identity</span>.
                            </p>
                            @error('module')
                                <p class="mt-2 flex items-center gap-1.5 text-xs text-red-400">
                                    <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- DESCRIPTION --}}
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-gray-400">Description</label>
                                <span class="text-[11px] text-gray-600">Optional · 500 characters max</span>
                            </div>
                            <textarea
                                name="description"
                                id="description"
                                x-model="description"
                                rows="5"
                                maxlength="500"
                                placeholder="Explain what this permission allows a user to do..."
                                class="w-full resize-none rounded-xl border border-white/10 bg-gray-950/60 px-4 py-3 text-sm leading-6 text-white placeholder:text-gray-600 outline-none transition focus:border-indigo-500/50 focus:ring-4 focus:ring-indigo-500/10"
                            >{{ old('description', $permission->description) }}</textarea>
                            @error('description')
                                <p class="mt-2 flex items-center gap-1.5 text-xs text-red-400">
                                    <i class="fa-solid fa-circle-exclamation"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- CHANGE REMINDER --}}
                <section class="border-t border-white/5">
                    <div class="p-6 sm:p-7">
                        <div class="flex items-start gap-3 rounded-xl border border-amber-500/10 bg-amber-500/5 p-4">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-400">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-200">Review dependent access rules</p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    If you change the slug, update code, gates, tests and user or role assignments that rely on the previous identifier.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- FOOTER ACTIONS --}}
                <div class="flex flex-col-reverse gap-3 border-t border-white/5 bg-gray-950/30 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                    <p class="text-xs text-gray-600">
                        <i class="fa-solid fa-fingerprint mr-1"></i>
                        Permission ID: {{ $permission->id }}
                    </p>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                        @if(Route::has('permission.list'))
                            <a
                                href="{{ route('permission.list') }}"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-5 py-2.5 text-sm font-semibold text-gray-300 transition hover:bg-white/10 hover:text-white active:scale-[0.98]"
                            >
                                Cancel
                            </a>
                        @endif

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:-translate-y-0.5 hover:bg-indigo-500 active:translate-y-0"
                        >
                            <i class="fa-solid fa-floppy-disk"></i>
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- RIGHT SIDEBAR --}}
        <aside class="space-y-4">

            {{-- LIVE PREVIEW --}}
            <div class="overflow-hidden rounded-2xl border border-white/5 bg-gray-900/80 shadow-2xl shadow-black/10">
                <div class="border-b border-white/5 px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-400">
                            <i class="fa-solid fa-eye text-xs"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-white">Live preview</h2>
                            <p class="text-xs text-gray-500">Updated permission appearance</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 p-5">
                    <div class="rounded-xl border border-white/5 bg-gray-950/50 p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-indigo-500/20 bg-indigo-500/10 text-indigo-400">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="break-words text-sm font-bold text-white" x-text="permissionName || 'Permission name'"></p>
                                <div class="mt-2 inline-flex max-w-full items-center rounded-lg border border-white/5 bg-gray-900 px-2 py-1">
                                    <code class="break-all text-[11px] text-indigo-300" x-text="slug || 'permission-slug'"></code>
                                </div>
                                <p class="mt-3 break-words text-xs leading-5 text-gray-500" x-text="description || 'A short description of what this permission allows.'"></p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-white/5 bg-gray-950/40 p-4">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Module</p>
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/10 text-violet-400">
                                <i class="fa-solid fa-cubes text-xs"></i>
                            </span>
                            <span class="break-all text-sm font-semibold text-gray-200" x-text="module || 'system'"></span>
                        </div>
                    </div>

                    <div class="rounded-xl border border-white/5 bg-gray-950/40 p-4">
                        <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <i class="fa-solid fa-code text-indigo-400"></i>
                            Naming convention
                        </div>
                        <p class="text-xs leading-5 text-gray-500">Use consistent, action-oriented permission slugs. Examples:</p>
                        <div class="mt-3 space-y-2">
                            <code class="block rounded-lg border border-white/5 bg-gray-950/70 px-3 py-2 text-[11px] text-gray-300">ticket.create</code>
                            <code class="block rounded-lg border border-white/5 bg-gray-950/70 px-3 py-2 text-[11px] text-gray-300">ticket.status.update</code>
                            <code class="block rounded-lg border border-white/5 bg-gray-950/70 px-3 py-2 text-[11px] text-gray-300">user.delete</code>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RECORD INFORMATION --}}
            <div class="overflow-hidden rounded-2xl border border-white/5 bg-gray-900/80 shadow-2xl shadow-black/10">
                <div class="border-b border-white/5 px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10 text-sky-400">
                            <i class="fa-solid fa-database text-xs"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-white">Record information</h2>
                            <p class="text-xs text-gray-500">Current persisted record</p>
                        </div>
                    </div>
                </div>

                <dl class="divide-y divide-white/5 px-5">
                    <div class="flex items-center justify-between gap-4 py-3.5">
                        <dt class="text-xs text-gray-500">ID</dt>
                        <dd class="font-mono text-xs font-semibold text-gray-300">{{ $permission->id }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 py-3.5">
                        <dt class="text-xs text-gray-500">Version</dt>
                        <dd class="text-xs font-semibold text-gray-300">{{ $permission->version }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4 py-3.5">
                        <dt class="text-xs text-gray-500">Created at</dt>
                        <dd class="text-right text-xs font-semibold text-gray-300">{{ $permission->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4 py-3.5">
                        <dt class="text-xs text-gray-500">Last updated</dt>
                        <dd class="text-right text-xs font-semibold text-gray-300">{{ $permission->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- GUIDANCE --}}
            <div class="rounded-2xl border border-white/5 bg-gray-900/80 p-5 shadow-2xl shadow-black/10">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400">
                        <i class="fa-solid fa-lightbulb"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">Good practices</h3>
                        <ul class="mt-3 space-y-2.5 text-xs leading-5 text-gray-500">
                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-check mt-0.5 text-emerald-400"></i>
                                Keep permission names easy to understand.
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-check mt-0.5 text-emerald-400"></i>
                                Treat slug changes as potentially breaking changes.
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-check mt-0.5 text-emerald-400"></i>
                                Always enforce permissions in server-side authorization checks.
                            </li>
                        </ul>
                    </div>
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
