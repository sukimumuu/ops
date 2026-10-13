@extends('layouts.app')
@section('page-title', 'Profil & Verifikasi (KYC)')
@section('content')
@php
    $user = $user ?? auth()->user();
@endphp

@if (! $user)
    <div class="bg-white rounded-xl shadow-sm border border-red-100 p-8 text-center my-8">
        <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-800 mb-2">Profil Tidak Tersedia</h2>
        <p class="text-gray-500 mb-6 max-w-md mx-auto text-sm">
            Sesi masuk Anda mungkin telah berakhir atau Anda belum terautentikasi. Silakan masuk terlebih dahulu untuk mengakses profil Anda.
        </p>
        <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-6 py-2.5 bg-primary text-white font-medium rounded-lg hover:bg-orange-600 transition shadow-sm">
            Masuk ke Akun
        </a>
    </div>
@else
@php
    $roleDisplayName = $roleDisplayName ?? ($user->roles->first()?->name === 'Superadmin' ? 'Super Admin' : ($user->roles->first()?->name ?? 'Pengguna'));
    $idLabel = $idLabel ?? ($user->hasRole(['Superadmin', 'Admin', 'admin']) ? 'ID Admin' : 'ID Pengguna');
    $formattedId = $formattedId ?? ($user->hasRole(['Superadmin', 'Admin', 'admin']) ? 'ADM-'.str_pad((string)$user->id, 3, '0', STR_PAD_LEFT) : 'USR-'.str_pad((string)$user->id, 3, '0', STR_PAD_LEFT));
    $formattedPhone = $formattedPhone ?? ('+' . $user->phone);
    $maskedPhone = $maskedPhone ?? ('+62 ' . substr($user->phone, 2, 3) . '-••••-' . substr($user->phone, -4));
    $devices = $devices ?? [
        [
            'name' => 'Peramban Web saat ini',
            'is_mobile' => false,
            'is_current' => true,
            'ip_address' => request()->ip() ?: '127.0.0.1',
            'last_active' => 'Aktif sekarang',
        ]
    ];
@endphp

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Profil Saya</h1>
        <p class="text-gray-500 mt-1">Kelola informasi pribadi, keamanan akun, dan perangkat yang terhubung</p>
    </div>

    <!-- Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_2.5fr] gap-6 items-start">

        <!-- Left Column: Profile Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">

            <!-- ─── Avatar Upload Widget ─────────────────────────────── -->
            <div
                id="avatar-widget"
                x-data="avatarUploader({
                    currentUrl: '{{ $user->avatar_url }}',
                    uploadUrl: '{{ route('profile.avatar.update') }}',
                    deleteUrl: '{{ route('profile.avatar.delete') }}',
                    hasCustomAvatar: {{ $user->avatar_path ? 'true' : 'false' }},
                    csrfToken: '{{ csrf_token() }}'
                })"
                class="flex flex-col items-center text-center"
            >
                <!-- Hidden file input -->
                <input
                    type="file"
                    id="avatar-file-input"
                    x-ref="fileInput"
                    @change="onFileSelected"
                    accept="image/jpeg,image/jpg,image/png,image/webp"
                    class="hidden"
                >

                <!-- Drop zone + Avatar preview -->
                <div
                    class="relative mb-4 group cursor-pointer"
                    @click="$refs.fileInput.click()"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="onDrop"
                    :class="isDragging ? 'ring-2 ring-primary ring-offset-2 rounded-full' : ''"
                    title="Klik atau seret foto ke sini"
                >
                    <!-- Avatar image -->
                    <img
                        :src="previewUrl"
                        alt="{{ $user->name }}"
                        class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-sm"
                        :class="uploading ? 'opacity-50' : ''"
                    >

                    <!-- Upload overlay (hover) -->
                    <div
                        class="absolute inset-0 rounded-full bg-black/40 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                        :class="uploading ? 'opacity-100' : ''"
                    >
                        <!-- Spinner while uploading -->
                        <template x-if="uploading">
                            <svg class="w-7 h-7 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                            </svg>
                        </template>

                        <!-- Camera icon at rest -->
                        <template x-if="!uploading">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-white text-[10px] font-semibold mt-1">Ubah Foto</span>
                        </template>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="flex items-center gap-3 mb-3">
                    <button
                        type="button"
                        @click="$refs.fileInput.click()"
                        :disabled="uploading"
                        class="bg-orange-50 hover:bg-orange-100 text-primary font-medium text-sm px-4 py-2 rounded-lg transition border border-orange-100 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Ganti Foto
                    </button>
                    <button
                        type="button"
                        x-show="hasCustomAvatar"
                        @click="deleteAvatar"
                        :disabled="uploading"
                        class="bg-white hover:bg-red-50 text-red-500 hover:text-red-600 font-medium text-sm px-4 py-2 rounded-lg transition border border-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Hapus
                    </button>
                </div>

                <p class="text-xs text-gray-400 mb-4 px-4">
                    JPG, PNG, atau WebP · Maks. 2 MB · Gunakan foto wajah yang jelas.
                </p>

                <!-- Inline toast notification -->
                <div
                    x-show="toast.visible"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-1"
                    :class="toast.type === 'success'
                        ? 'bg-green-50 border-green-200 text-green-700'
                        : 'bg-red-50 border-red-200 text-red-700'"
                    class="w-full rounded-lg border px-4 py-2.5 text-xs font-medium flex items-center gap-2"
                    x-cloak
                >
                    <!-- Success icon -->
                    <template x-if="toast.type === 'success'">
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </template>
                    <!-- Error icon -->
                    <template x-if="toast.type === 'error'">
                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </template>
                    <span x-text="toast.message"></span>
                </div>
            </div>
            <!-- ─── End Avatar Widget ───────────────────────────────── -->

            <hr class="border-gray-100 my-6">


            <!-- User Info Summary -->
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold text-gray-800">{{ $user->name }}</h2>
                <p class="text-gray-500 text-sm mb-1">{{ '@'.$user->username }}</p>
                <p class="text-gray-400 text-xs mb-3">{{ $user->email ?? 'Belum ada email' }}</p>
                <span
                    class="inline-block bg-orange-50 text-primary text-xs font-semibold px-3 py-1 rounded-full border border-orange-100">
                    {{ $roleDisplayName }}
                </span>
            </div>

            <!-- Details List -->
            <div class="space-y-4 text-sm">
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">{{ $idLabel }}</span>
                    <span class="font-semibold text-gray-800">{{ $formattedId }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Bergabung</span>
                    <span class="font-medium text-gray-800">{{ $user->created_at ? $user->created_at->locale('id')->translatedFormat('d F Y') : '-' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Status akun</span>
                    <div class="flex items-center gap-1.5 font-medium text-green-600">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        Aktif
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Status KYC</span>
                    @if ($user->kyc_status === 'verified')
                        <div class="flex items-center gap-1.5 font-medium text-green-600">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            Terverifikasi
                        </div>
                    @elseif ($user->kyc_status === 'pending')
                        <div class="flex items-center gap-1.5 font-medium text-amber-600">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            Menunggu Tinjauan
                        </div>
                    @else
                        <div class="flex items-center gap-1.5 font-medium text-slate-500">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                            Belum Verifikasi
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Main Content Cards -->
        <div class="space-y-6">

            <!-- Card 1: Informasi Pribadi -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-800">Informasi Pribadi</h3>
                    <p class="text-sm text-gray-500 mt-1">Pastikan data ini selalu terbaru agar komunikasi
                        operasional berjalan lancar.</p>
                </div>

                <form class="space-y-6">
                    <!-- Grid for inputs -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nama Lengkap -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1.5">Nama Lengkap</label>
                            <input type="text" value="{{ $user->name }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        </div>

                        <!-- Username -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1.5">Username</label>
                            <input type="text" value="{{ $user->username }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <p class="text-xs text-gray-400 mt-1.5">Username unik akun Anda</p>
                        </div>

                        <!-- Peran Akun -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1.5">Peran Akun</label>
                            <input type="text" value="{{ $roleDisplayName }}" disabled
                                class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-2.5 text-gray-500 cursor-not-allowed">
                            <p class="text-xs text-gray-400 mt-1.5">Peran akun ditentukan oleh sistem dan administrator.</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1.5">Email</label>
                            <input type="email" value="{{ $user->email ?? '' }}" placeholder="Belum mengisi email"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            @if ($user->email_verified_at)
                                <p class="text-xs text-green-600 mt-1.5 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Email telah terverifikasi
                                </p>
                            @elseif ($user->email)
                                <p class="text-xs text-amber-600 mt-1.5">Email belum terverifikasi</p>
                            @else
                                <p class="text-xs text-gray-400 mt-1.5">Email belum ditambahkan</p>
                            @endif
                        </div>

                        <!-- Nomor Telepon -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-1.5">Nomor Telepon</label>
                            <input type="tel" value="{{ $formattedPhone }}"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <p class="text-xs text-gray-400 mt-1.5">Digunakan untuk OTP dan notifikasi keamanan</p>
                        </div>
                    </div>

                    <!-- Alamat -->
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1.5">Alamat</label>
                        <input type="text"
                            value="{{ $user->address ?? '' }}"
                            placeholder="Belum ada data alamat"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                    </div>

                    <!-- Footer Actions -->
                    <div
                        class="flex flex-col sm:flex-row justify-between items-center pt-4 border-t border-gray-100 gap-4 mt-8">
                        <span class="text-sm text-gray-400">Terakhir diperbarui {{ $user->updated_at ? $user->updated_at->locale('id')->diffForHumans() : 'baru saja' }}</span>
                        <button type="button"
                            class="bg-primary hover:bg-orange-600 text-white font-medium rounded-lg px-6 py-2.5 transition w-full sm:w-auto shadow-sm">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Card 2: Keamanan Akun -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Keamanan Akun</h3>
                        <p class="text-sm text-gray-500 mt-1">Password, autentikasi dua langkah, dan sesi perangkat
                            aktif.</p>
                    </div>
                    <div
                        class="bg-green-50 text-green-600 border border-green-100 px-3 py-1.5 rounded-md flex items-center gap-1.5 text-sm font-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 256 256"
                            fill="currentColor">
                            <path
                                d="M224,64V128c0,42-22.1,80-60.6,104.6a32.3,32.3,0,0,1-34.8,0C90.1,208,68,170,68,128V64a16,16,0,0,1,16-16h88A16,16,0,0,1,224,64Zm-16,0H84v64c0,35.2,18.5,67,50.7,87.6a16.2,16.2,0,0,0,17.4,0C184.3,195,208,163.2,208,128ZM179.3,108.7a8,8,0,0,0-11.3-11.4l-40,40-20-20a8,8,0,0,0-11.3,11.4l25.6,25.6a8,8,0,0,0,11.3,0Z">
                            </path>
                        </svg>
                        Keamanan baik
                    </div>
                </div>

                <form
                    x-data="passwordChangeForm(
                        @js(route('profile.password.update')),
                        @js(csrf_token())
                    )"
                    @submit.prevent="submit"
                    class="space-y-5 mb-8"
                    novalidate
                >
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Password Saat Ini
                        </label>
                        <div class="relative">
                            <input
                                id="current_password"
                                name="current_password"
                                x-model="currentPassword"
                                :type="show.current ? 'text' : 'password'"
                                :aria-invalid="Boolean(errors.current_password)"
                                aria-describedby="current-password-error"
                                autocomplete="current-password"
                                required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 pr-20 text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                            >
                            <button type="button" @click="show.current = !show.current"
                                class="absolute inset-y-0 right-3 text-sm text-gray-500 hover:text-gray-800"
                                :aria-label="show.current ? 'Sembunyikan password saat ini' : 'Tampilkan password saat ini'">
                                <span x-text="show.current ? 'Sembunyikan' : 'Tampilkan'"></span>
                            </button>
                        </div>
                        <p id="current-password-error" x-show="errors.current_password" x-text="errors.current_password"
                            class="mt-1 text-sm text-red-600" role="alert"></p>
                    </div>

                    <div class="flex justify-end">
                        <div x-show="!changeFormOpen" class="flex flex-wrap justify-end gap-3">
                            <button type="button" @click="openChangeForm()"
                                class="border border-primary text-primary hover:bg-orange-50 font-medium rounded-lg px-6 py-2.5 transition">
                                Ubah Password
                            </button>
                        </div>
                    </div>

                    <div x-show="changeFormOpen" x-transition x-cloak class="space-y-5">
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Password Baru
                            </label>
                            <div class="relative">
                                <input
                                    id="new_password"
                                    name="new_password"
                                    x-model="newPassword"
                                    :type="show.new ? 'text' : 'password'"
                                    :aria-invalid="Boolean(errors.new_password)"
                                    aria-describedby="new-password-guidance new-password-error"
                                    autocomplete="new-password"
                                    minlength="12"
                                    required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 pr-20 text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                                >
                                <button type="button" @click="show.new = !show.new"
                                    class="absolute inset-y-0 right-3 text-sm text-gray-500 hover:text-gray-800"
                                    :aria-label="show.new ? 'Sembunyikan password baru' : 'Tampilkan password baru'">
                                    <span x-text="show.new ? 'Sembunyikan' : 'Tampilkan'"></span>
                                </button>
                            </div>
                            <p id="new-password-guidance" class="mt-1 text-xs text-gray-500">
                                Minimal 12 karakter dan harus mencakup huruf besar, huruf kecil, serta angka.
                            </p>
                            <ul class="mt-2 grid grid-cols-2 gap-1 text-xs text-gray-500" aria-live="polite">
                                <li :class="checks.length ? 'text-green-700' : ''" x-text="`${checks.length ? '✓' : '○'} Minimal 12 karakter`"></li>
                                <li :class="checks.upper ? 'text-green-700' : ''" x-text="`${checks.upper ? '✓' : '○'} Huruf besar`"></li>
                                <li :class="checks.lower ? 'text-green-700' : ''" x-text="`${checks.lower ? '✓' : '○'} Huruf kecil`"></li>
                                <li :class="checks.number ? 'text-green-700' : ''" x-text="`${checks.number ? '✓' : '○'} Angka`"></li>
                            </ul>
                            <p id="new-password-error" x-show="errors.new_password" x-text="errors.new_password"
                                class="mt-1 text-sm text-red-600" role="alert"></p>
                        </div>

                        <div>
                            <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Konfirmasi Password Baru
                            </label>
                            <div class="relative">
                                <input
                                    id="new_password_confirmation"
                                    name="new_password_confirmation"
                                    x-model="confirmation"
                                    :type="show.confirm ? 'text' : 'password'"
                                    :aria-invalid="Boolean(errors.new_password_confirmation) || Boolean(confirmation && confirmation !== newPassword)"
                                    aria-describedby="confirmation-error"
                                    autocomplete="new-password"
                                    required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 pr-20 text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                                >
                                <button type="button" @click="show.confirm = !show.confirm"
                                    class="absolute inset-y-0 right-3 text-sm text-gray-500 hover:text-gray-800"
                                    :aria-label="show.confirm ? 'Sembunyikan konfirmasi password' : 'Tampilkan konfirmasi password'">
                                    <span x-text="show.confirm ? 'Sembunyikan' : 'Tampilkan'"></span>
                                </button>
                            </div>
                            <p id="confirmation-error"
                                x-show="errors.new_password_confirmation || (confirmation && confirmation !== newPassword)"
                                x-text="errors.new_password_confirmation || 'Konfirmasi password belum cocok.'"
                                class="mt-1 text-sm text-red-600" role="alert"></p>
                        </div>

                        <p x-show="globalError" x-text="globalError" class="text-sm text-red-600" role="alert"></p>
                        <p x-show="successMessage" x-text="successMessage" class="text-sm text-green-700" role="status" aria-live="polite"></p>

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <p class="text-xs text-gray-400">
                                Mengganti password akan mengeluarkan akun dari perangkat lain.
                            </p>
                            <div class="flex gap-3">
                                <button type="button" @click="closeChangeForm()"
                                    class="border border-gray-300 text-gray-600 hover:bg-gray-50 font-medium rounded-lg px-6 py-2.5 transition">
                                    Batal
                                </button>
                                <button type="submit" :disabled="loading"
                                    class="border border-primary text-primary hover:bg-orange-50 disabled:opacity-50 disabled:cursor-not-allowed font-medium rounded-lg px-6 py-2.5 transition">
                                    <span x-show="!loading">Perbarui Password</span>
                                    <span x-show="loading" x-cloak>Memperbarui...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <p class="text-xs text-gray-400 mb-8 -mt-6">Terakhir diperbarui {{ $user->updated_at ? $user->updated_at->locale('id')->diffForHumans() : 'baru saja' }}</p>

                <!-- Two-Factor Authentication -->
                <div class="flex justify-between items-center py-5 border-y border-gray-100 mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-800">Autentikasi Dua Langkah</h4>
                        <p class="text-sm text-gray-500 mt-0.5">Kode OTP dikirim ke {{ $maskedPhone }}</p>
                    </div>

                    <!-- Toggle Switch -->
                    <label class="flex items-center cursor-pointer relative">
                        <input type="checkbox" checked class="sr-only toggle-checkbox">
                        <div class="toggle-label block bg-gray-300 w-11 h-6 rounded-full"></div>
                        <div class="toggle-circle absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition">
                        </div>
                    </label>
                </div>

                <!-- Connected Devices -->
                <div>
                    <h4 class="text-sm font-bold text-gray-800 mb-4">Perangkat Terhubung</h4>

                    <div class="space-y-4">
                        @forelse ($devices as $device)
                            <div
                                class="flex items-center justify-between p-4 border border-gray-100 rounded-lg {{ $device['is_current'] ? 'bg-gray-50/50' : '' }} hover:bg-gray-50 transition">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-10 h-10 bg-white border border-gray-200 rounded-lg flex items-center justify-center text-gray-600 shadow-sm">
                                        @if ($device['is_mobile'])
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 256 256"
                                                fill="currentColor">
                                                <path
                                                    d="M176,16H80A24,24,0,0,0,56,40V216a24,24,0,0,0,24,24h96a24,24,0,0,0,24-24V40A24,24,0,0,0,176,16Zm8,200a8,8,0,0,1-8,8H80a8,8,0,0,1-8-8V40a8,8,0,0,1,8-8h96a8,8,0,0,1,8,8ZM128,192a12,12,0,1,0,12,12A12,12,0,0,0,128,192Z">
                                                </path>
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 256 256"
                                                fill="currentColor">
                                                <path
                                                    d="M224,64V160a16,16,0,0,1-16,16H48a16,16,0,0,1-16-16V64A16,16,0,0,1,48,48H208A16,16,0,0,1,224,64Zm-16,0H48V160H208ZM232,208v16H24v-16a16,16,0,0,1,16-16H216A16,16,0,0,1,232,208Z">
                                                </path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-semibold text-gray-800">{{ $device['name'] }}</p>
                                            @if ($device['is_current'])
                                                <span
                                                    class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded">Saat
                                                    ini</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            {{ $device['ip_address'] ?? 'IP tersamar' }} • {{ $device['last_active'] }}
                                        </p>
                                    </div>
                                </div>
                                @if ($device['is_current'])
                                    <span class="text-sm font-medium text-gray-400">Aktif</span>
                                @else
                                    <button type="button"
                                        class="text-sm font-medium text-red-500 hover:text-red-700 transition">Keluar</button>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-400">Tidak ada informasi sesi perangkat.</p>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
function passwordChangeForm(updateUrl, csrfToken) {
    return {
        currentPassword: '',
        newPassword: '',
        confirmation: '',
        changeFormOpen: false,
        show: { current: false, new: false, confirm: false },
        errors: {},
        globalError: '',
        successMessage: '',
        loading: false,

        get checks() {
            return {
                length: this.newPassword.length >= 12,
                upper: /[A-Z]/.test(this.newPassword),
                lower: /[a-z]/.test(this.newPassword),
                number: /\d/.test(this.newPassword),
            };
        },

        get valid() {
            return Boolean(this.currentPassword)
                && this.checks.length
                && this.checks.upper
                && this.checks.lower
                && this.checks.number
                && this.newPassword === this.confirmation
                && this.newPassword !== this.currentPassword;
        },

        openChangeForm() {
            this.changeFormOpen = true;
            this.errors = {};
            this.globalError = '';
            this.successMessage = '';
        },

        closeChangeForm() {
            this.changeFormOpen = false;
            this.currentPassword = '';
            this.newPassword = '';
            this.confirmation = '';
            this.errors = {};
            this.globalError = '';
            this.show = { current: false, new: false, confirm: false };
        },

        async submit() {
            if (this.loading) {
                return;
            }

            if (!this.valid) {
                this.errors = {};
                if (!this.currentPassword) {
                    this.errors.current_password = 'Password saat ini wajib diisi.';
                }
                if (!this.checks.length || !this.checks.upper || !this.checks.lower || !this.checks.number) {
                    this.errors.new_password = 'Password baru belum memenuhi semua persyaratan.';
                } else if (this.newPassword === this.currentPassword) {
                    this.errors.new_password = 'Password baru harus berbeda dari password saat ini.';
                }
                if (this.newPassword !== this.confirmation) {
                    this.errors.new_password_confirmation = 'Konfirmasi password belum cocok.';
                }

                return;
            }

            this.loading = true;
            this.errors = {};
            this.globalError = '';
            this.successMessage = '';

            try {
                const response = await fetch(updateUrl, {
                    method: 'PUT',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        current_password: this.currentPassword,
                        new_password: this.newPassword,
                        new_password_confirmation: this.confirmation,
                    }),
                });
                const data = await response.json();

                if (!response.ok) {
                    this.errors = Object.fromEntries(
                        Object.entries(data.errors ?? {}).map(([field, messages]) => [field, messages[0]])
                    );
                    this.globalError = data.message ?? 'Password tidak dapat diperbarui.';
                    return;
                }

                this.currentPassword = '';
                this.newPassword = '';
                this.confirmation = '';
                this.successMessage = data.message;
            } catch {
                this.globalError = 'Terjadi kesalahan jaringan. Silakan coba lagi.';
            } finally {
                this.loading = false;
            }
        },
    };
}

/**
 * Alpine.js component for avatar upload / delete.
 *
 * Security layers applied:
 *  - Client: MIME type and 2 MB size check before any network request.
 *  - Server: `UpdateAvatarRequest` validates file, MIME, and size server-side.
 *  - Auth:   Routes are inside the `auth` middleware group; controller reads
 *            the authenticated user from the request, never from user input.
 */
function avatarUploader({ currentUrl, uploadUrl, deleteUrl, hasCustomAvatar, csrfToken }) {
    return {
        previewUrl: currentUrl,
        hasCustomAvatar,
        uploading: false,
        isDragging: false,
        toast: { visible: false, message: '', type: 'success', _timer: null },

        // ── Validation ───────────────────────────────────────────────────────
        validateFile(file) {
            const allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!allowedMimes.includes(file.type)) {
                this.showToast('Format foto harus JPG, PNG, atau WebP.', 'error');
                return false;
            }
            if (file.size > 2 * 1024 * 1024) {
                this.showToast('Ukuran foto tidak boleh melebihi 2 MB.', 'error');
                return false;
            }
            return true;
        },

        // ── File selection via <input> ────────────────────────────────────────
        onFileSelected(event) {
            const file = event.target.files[0];
            if (file) { this.processFile(file); }
            // Reset so the same file can be re-selected after an error
            event.target.value = '';
        },

        // ── Drag-and-drop ────────────────────────────────────────────────────
        onDrop(event) {
            this.isDragging = false;
            const file = event.dataTransfer.files[0];
            if (file) { this.processFile(file); }
        },

        // ── Core upload flow ─────────────────────────────────────────────────
        processFile(file) {
            if (!this.validateFile(file)) { return; }

            // Show instant preview using FileReader
            const reader = new FileReader();
            reader.onload = (e) => { this.previewUrl = e.target.result; };
            reader.readAsDataURL(file);

            this.upload(file);
        },

        async upload(file) {
            this.uploading = true;

            const body = new FormData();
            body.append('avatar', file);
            body.append('_token', csrfToken);

            try {
                const response = await fetch(uploadUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body,
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.previewUrl = data.avatar_url;
                    this.hasCustomAvatar = true;
                    this.showToast(data.message ?? 'Foto profil berhasil diperbarui.', 'success');
                } else {
                    // Revert preview on failure
                    this.previewUrl = currentUrl;
                    const msg = data.errors?.avatar?.[0]
                        ?? data.message
                        ?? 'Gagal mengunggah foto. Coba lagi.';
                    this.showToast(msg, 'error');
                }
            } catch {
                this.previewUrl = currentUrl;
                this.showToast('Terjadi kesalahan jaringan. Coba lagi.', 'error');
            } finally {
                this.uploading = false;
            }
        },

        // ── Delete flow ──────────────────────────────────────────────────────
        async deleteAvatar() {
            if (this.uploading) { return; }
            this.uploading = true;

            try {
                const response = await fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.previewUrl = data.avatar_url;
                    this.hasCustomAvatar = false;
                    currentUrl = data.avatar_url; // update baseline for error revert
                    this.showToast(data.message ?? 'Foto profil berhasil dihapus.', 'success');
                } else {
                    this.showToast(data.message ?? 'Gagal menghapus foto. Coba lagi.', 'error');
                }
            } catch {
                this.showToast('Terjadi kesalahan jaringan. Coba lagi.', 'error');
            } finally {
                this.uploading = false;
            }
        },

        // ── Toast helper ─────────────────────────────────────────────────────
        showToast(message, type = 'success') {
            clearTimeout(this.toast._timer);
            this.toast = { visible: true, message, type, _timer: null };
            this.toast._timer = setTimeout(() => { this.toast.visible = false; }, 4000);
        },
    };
}
</script>
@endpush