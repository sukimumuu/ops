@extends('layouts.app')
@section('page-title', 'Tambah Pengguna')
@section('content')
    <div class="mb-5">
        <a href="{{ route('superadmin.user-management') }}" class="text-sm font-semibold text-primary hover:text-primary-hover">
            &larr; Kembali ke Manajemen Pengguna
        </a>
    </div>

    <x-card-dashboard title="Tambah Pengguna" subtitle="Lengkapi informasi akun dan pilih peran pengguna.">
        <form action="{{ route('superadmin.user-management.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <x-form-text-input-dashboard
                    id="name"
                    name="name"
                    label="Nama Lengkap"
                    placeholder="Masukkan nama lengkap"
                    :value="old('name')"
                    :error="$errors->first('name')"
                    required
                />

                <x-form-text-input-dashboard
                    id="email"
                    name="email"
                    type="email"
                    label="Email"
                    placeholder="nama@example.com"
                    :value="old('email')"
                    :error="$errors->first('email')"
                />

                <x-form-text-input-dashboard
                    id="phone"
                    name="phone"
                    type="tel"
                    label="Nomor Telepon"
                    placeholder="08xxxxxxxxxx"
                    :value="old('phone')"
                    :error="$errors->first('phone')"
                    required
                />

                <x-form-select-dashboard
                    id="role"
                    name="role"
                    label="Peran"
                    :error="$errors->first('role')"
                    required
                >
                    <option value="">Pilih peran</option>
                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                    <option value="ppat" @selected(old('role') === 'ppat')>PPAT</option>
                    <option value="user" @selected(old('role') === 'user')>User</option>
                </x-form-select-dashboard>

                <x-form-text-input-dashboard
                    id="password"
                    name="password"
                    type="password"
                    label="Kata Sandi"
                    :error="$errors->first('password')"
                    hint="Minimal 8 karakter."
                    required
                />

                <x-form-text-input-dashboard
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    label="Konfirmasi Kata Sandi"
                    :error="$errors->first('password_confirmation')"
                    required
                />
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('superadmin.user-management') }}"
                    class="inline-flex items-center justify-center rounded-lg border-2 border-primary px-5 py-2.5 text-sm font-heading font-semibold tracking-tight text-primary transition hover:bg-primary-soft">
                    Batal
                </a>
                <x-button-dashboard type="submit" variant="primary">
                    Simpan Pengguna
                </x-button-dashboard>
            </div>
        </form>
    </x-card-dashboard>
@endsection
