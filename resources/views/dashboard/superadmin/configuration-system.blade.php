@extends('layouts.app')
@section('page-title', 'Konfigurasi Sistem')
@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <header class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-wide text-primary">Sistem & konfigurasi</p>
            <h1 class="font-heading text-2xl font-bold tracking-tight text-secondary">Pengaturan role & permission</h1>
            <p class="max-w-3xl text-sm text-slate-500">
                Atur akses setiap role tanpa mengubah akun pengguna atau data properti. Perubahan hanya memengaruhi
                permission yang melekat pada role.
            </p>
        </header>

        @if (session('status'))
            <div role="status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div role="alert"
                class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                Perubahan tidak disimpan. Periksa kembali permission yang dipilih.
            </div>
        @endif

        <div class="space-y-3">
            @forelse ($roles as $role)
                @if ($role->name === 'Superadmin')
                    <details class="group/role overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4">
                            <div class="flex items-center gap-3">
                                <h2 class="font-heading text-lg font-bold text-secondary">{{ $role->name }}</h2>
                                <span class="text-xs text-slate-500">
                                    {{ $role->permissions->count() }} permission aktif
                                </span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800">
                                    Akses penuh · dilindungi
                                </span>
                                <svg class="size-5 shrink-0 text-slate-400 transition-transform group-open/role:rotate-180"
                                    viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M5.22 7.47a.75.75 0 0 1 1.06 0L10 11.19l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0l-4.25-4.25a.75.75 0 0 1 0-1.06Z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </summary>
                        <p class="border-t border-slate-100 px-5 py-5 text-sm text-slate-600">
                            Role ini memiliki semua permission dan tidak dapat diubah dari halaman ini agar akses
                            administrator utama tetap terlindungi.
                        </p>
                    </details>
                @else
                    <form method="POST" action="{{ route('superadmin.configuration-system.permissions.update', $role) }}">
                        @csrf
                        @method('PUT')
                        <details class="group/role overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <h2 class="font-heading text-lg font-bold text-secondary">{{ $role->name }}</h2>
                                    <span class="text-xs text-slate-500">
                                        {{ $role->permissions->count() }} permission aktif
                                    </span>
                                </div>
                                <svg class="size-5 shrink-0 text-slate-400 transition-transform group-open/role:rotate-180"
                                    viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M5.22 7.47a.75.75 0 0 1 1.06 0L10 11.19l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0l-4.25-4.25a.75.75 0 0 1 0-1.06Z"
                                        clip-rule="evenodd" />
                                </svg>
                            </summary>

                            <div class="space-y-4 border-t border-slate-100 px-5 py-5">
                                @foreach ($groupLabels as $groupKey => $groupLabel)
                                    @php($permissions = $permissionGroups->get($groupKey, collect()))
                                    @if ($permissions->isNotEmpty())
                                        <details class="group/permission rounded-xl border border-slate-200">
                                            <summary
                                                class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3">
                                                <span class="text-sm font-bold text-slate-800">{{ $groupLabel }}</span>
                                                <div class="flex items-center gap-3">
                                                    <span class="text-xs text-slate-500">
                                                        {{ $role->permissions->whereIn('name', $permissions->pluck('name'))->count() }}
                                                        / {{ $permissions->count() }} aktif
                                                    </span>
                                                    <svg class="size-4 shrink-0 text-slate-400 transition-transform group-open/permission:rotate-180"
                                                        viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd"
                                                            d="M5.22 7.47a.75.75 0 0 1 1.06 0L10 11.19l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0l-4.25-4.25a.75.75 0 0 1 0-1.06Z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </summary>
                                            <div class="grid gap-3 border-t border-slate-100 px-4 py-4 sm:grid-cols-2">
                                                @foreach ($permissions as $permission)
                                                    <label class="flex items-start gap-2.5 text-sm text-slate-600">
                                                        <input type="checkbox" name="permissions[]"
                                                            value="{{ $permission->name }}"
                                                            @checked($role->permissions->contains('name', $permission->name))
                                                            class="mt-0.5 size-4 rounded border-slate-300 text-primary focus:ring-primary/30">
                                                        <span>
                                                            <span class="block font-medium text-slate-700">
                                                                {{ $permissionLabels[$permission->name] ?? str($permission->name)->replace('.', ' ')->replace('_', ' ')->ucfirst() }}
                                                            </span>
                                                            <span
                                                                class="block break-all text-xs text-slate-400">{{ $permission->name }}</span>
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </details>
                                    @endif
                                @endforeach
                                <button type="submit"
                                    class="rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white transition hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary/30">
                                    Simpan akses
                                </button>
                            </div>
                        </details>
                    </form>
                @endif
            @empty
                <p class="rounded-xl border border-slate-200 bg-white p-6 text-sm text-slate-500">
                    Belum ada role yang dapat dikonfigurasi.
                </p>
            @endforelse
        </div>
    </div>
@endsection
