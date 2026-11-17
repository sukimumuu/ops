@extends('layouts.app')
@section('page-title', 'Manajemen User')
@section('content')
    <div x-data="{
        confirmationOpen: false,
        confirmationTitle: '',
        confirmationMessage: '',
        confirmationAction: '',
        confirmationTone: 'warning',
        pendingForm: null,
        triggerButton: null,
        openConfirmation(button) {
            this.pendingForm = button.closest('form');
            if (!this.pendingForm) return;

            this.triggerButton = button;
            this.confirmationTitle = button.dataset.confirmTitle;
            this.confirmationMessage = button.dataset.confirmMessage;
            this.confirmationAction = button.dataset.confirmAction;
            this.confirmationTone = button.dataset.confirmTone;
            this.confirmationOpen = true;
            this.$nextTick(() => this.$refs.confirmationCancel.focus());
        },
        closeConfirmation() {
            const triggerButton = this.triggerButton;
            this.confirmationOpen = false;
            this.pendingForm = null;
            this.$nextTick(() => {
                if (triggerButton) triggerButton.focus();
            });
        },
        submitConfirmedAction() {
            if (this.pendingForm) this.pendingForm.requestSubmit();
        }
    }">
    <x-card-dashboard title="Daftar Pengguna / Akun" subtitle="Informasi tentang user yang terdaftar">
        <x-slot name="actions">
            <a href="{{ route('superadmin.user-management.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-3.5 py-2 text-xs font-heading font-semibold tracking-tight text-white shadow-lg shadow-primary/25 transition hover:bg-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30">
                Tambah Akun Baru
            </a>
        </x-slot>
        @if (session('status'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800" role="status">
                {{ session('status') }}
            </div>
        @endif
        <div class="space-y-4">
            <x-search-filter placeholder="Cari nama, email, atau telepon...">
                <div>
                    <label for="role-filter" class="mb-1 block text-xs font-semibold text-slate-600">Peran</label>
                    <select id="role-filter" name="role"
                        class="min-w-40 rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-secondary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        <option value="">Semua peran</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected(request('role') === $role)>{{ $role }}</option>
                        @endforeach
                    </select>
                </div>
            </x-search-filter>
            <x-table>
                <x-slot name="head">
                    <x-table.cell>Nama</x-table.cell>
                    <x-table.cell>Email</x-table.cell>
                    <x-table.cell>Nomor Telepon</x-table.cell>
                    <x-table.cell>Role</x-table.cell>
                    <x-table.cell>Status</x-table.cell>
                    <x-table.cell>Aksi</x-table.cell>
                </x-slot>
                @forelse($users as $user)
                    <x-table.row striped>
                        <x-table.data>
                            <p class="font-bold">{{ $user->name }}</p>
                        </x-table.data>
                        <x-table.data>
                            <p class="text-sm text-slate-500">{{ $user->email }}</p>
                        </x-table.data>
                        <x-table.data>
                            <span class="inline-flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="size-6">
                                    <path fill-rule="evenodd"
                                        d="M12 2.25c-2.429 0-4.817.178-7.152.521C2.87 3.061 1.5 4.795 1.5 6.741v6.018c0 1.946 1.37 3.68 3.348 3.97.877.129 1.761.234 2.652.316V21a.75.75 0 0 0 1.28.53l4.184-4.183a.39.39 0 0 1 .266-.112c2.006-.05 3.982-.22 5.922-.506 1.978-.29 3.348-2.023 3.348-3.97V6.741c0-1.947-1.37-3.68-3.348-3.97A49.145 49.145 0 0 0 12 2.25ZM8.25 8.625a1.125 1.125 0 1 0 0 2.25 1.125 1.125 0 0 0 0-2.25Zm2.625 1.125a1.125 1.125 0 1 1 2.25 0 1.125 1.125 0 0 1-2.25 0Zm4.875-1.125a1.125 1.125 0 1 0 0 2.25 1.125 1.125 0 0 0 0-2.25Z"
                                        clip-rule="evenodd" />
                                </svg>
                                <a href="https://wa.me/{{ '+' . $user->phone }}?text=Selamat%20pagi%20{{ $user->name }}"
                                    target="_blank" class="text-sm text-slate-500 hover:text-sky-700">
                                    {{ $user->phone }}
                                </a>
                            </span>
                        </x-table.data>
                        <x-table.data>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->getRoleNames()->first() == 'PPAT' ? 'bg-yellow-100' : 'bg-blue-100' }} {{ $user->getRoleNames()->first() == 'PPAT' ? 'text-yellow-800' : 'text-blue-800' }}">
                                {{ $user->getRoleNames()->first() ?? 'No Role' }}
                            </span>
                        </x-table.data>
                        <x-table.data>
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600' }}">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </x-table.data>
                        <x-table.data>
                            <x-button-dropdown variant="ghost" label="..." :position="$loop->last ? 'top-center' : 'bottom-middle'">
                                <x-item-dropdown href="{{ route('superadmin.user-management.edit', $user) }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="size-6">
                                        <path
                                            d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z" />
                                        <path
                                            d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z" />
                                    </svg>
                                    Edit
                                </x-item-dropdown>
                                <li role="none">
                                    <form method="POST"
                                        action="{{ route('superadmin.user-management.status.update', $user) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_active" value="{{ $user->is_active ? 0 : 1 }}">
                                        <button type="button" role="menuitem" @click="openConfirmation($el)"
                                            data-confirm-title="{{ $user->is_active ? 'Nonaktifkan akun?' : 'Aktifkan akun?' }}"
                                            data-confirm-message="{{ $user->is_active ? 'Akun '.$user->name.' akan dinonaktifkan dan tidak dapat masuk sampai diaktifkan kembali.' : 'Akun '.$user->name.' akan diaktifkan kembali dan dapat masuk.' }}"
                                            data-confirm-action="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                            data-confirm-tone="{{ $user->is_active ? 'warning' : 'success' }}"
                                            class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm font-semibold text-secondary transition hover:bg-primary-soft hover:text-primary focus:outline-none focus-visible:bg-primary-soft focus-visible:ring-2 focus-visible:ring-primary/30">
                                            @if ($user->is_active)
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                    fill="currentColor" class="size-6">
                                                    <path fill-rule="evenodd"
                                                        d="m6.72 5.66 11.62 11.62A8.25 8.25 0 0 0 6.72 5.66Zm10.56 12.68L5.66 6.72a8.25 8.25 0 0 0 11.62 11.62ZM5.105 5.106c3.807-3.808 9.98-3.808 13.788 0 3.808 3.807 3.808 9.98 0 13.788-3.807 3.808-9.98 3.808-13.788 0-3.808-3.807-3.808-9.98 0-13.788Z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Nonaktifkan
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                    fill="currentColor" class="size-6">
                                                    <path fill-rule="evenodd"
                                                        d="M12 2.25a9.75 9.75 0 1 0 0 19.5 9.75 9.75 0 0 0 0-19.5Zm4.03 7.28a.75.75 0 0 0-1.06-1.06l-4.72 4.72-1.22-1.22a.75.75 0 1 0-1.06 1.06l1.75 1.75a.75.75 0 0 0 1.06 0l5.25-5.25Z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Aktifkan
                                            @endif
                                        </button>
                                    </form>
                                </li>
                                <li role="none">
                                    <form method="POST"
                                        action="{{ route('superadmin.user-management.destroy', $user) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" role="menuitem" @click="openConfirmation($el)"
                                            data-confirm-title="Hapus akun?"
                                            data-confirm-message="Akun {{ $user->name }} akan dihapus dan tidak dapat masuk. Data properti dan transaksi tetap disimpan."
                                            data-confirm-action="Hapus akun"
                                            data-confirm-tone="danger"
                                            class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm font-semibold text-red-600 transition hover:bg-red-50 focus:outline-none focus-visible:bg-red-50 focus-visible:ring-2 focus-visible:ring-red-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                fill="currentColor" class="size-6">
                                                <path fill-rule="evenodd"
                                                    d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 0 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Hapus
                                        </button>
                                    </form>
                                </li>
                            </x-button-dropdown>
                        </x-table.data>
                    </x-table.row>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <p class="font-heading font-bold text-slate-400">No users found</p>
                            <p class="mt-1 text-sm text-slate-400">Create your first user to get started.</p>
                        </td>
                    </tr>
                @endforelse
            </x-table>
            {{-- Pagination --}}
            <x-table.pagination :paginator="$users" />
        </div>
    </x-card-dashboard>

        <div x-cloak x-show="confirmationOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            @keydown.escape.window="if (confirmationOpen) closeConfirmation()"
            @click.self="closeConfirmation()"
            class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
            role="presentation">
            <div role="dialog" aria-modal="true" aria-labelledby="user-confirmation-title"
                aria-describedby="user-confirmation-message"
                x-transition:enter="transition ease-out duration-300 delay-100"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                class="w-full max-w-[400px] rounded-[20px] bg-white px-8 py-10 text-center shadow-[0_20px_60px_rgba(0,0,0,0.15)]">
                <div class="mx-auto mb-5 flex size-20 items-center justify-center rounded-full"
                    :class="{
                        'bg-red-100 text-red-600': confirmationTone === 'danger',
                        'bg-amber-100 text-amber-600': confirmationTone === 'warning',
                        'bg-green-100 text-green-600': confirmationTone === 'success'
                    }">
                    <svg class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                    </svg>
                </div>
                <h3 id="user-confirmation-title" class="font-heading text-xl font-bold text-secondary"
                    x-text="confirmationTitle"></h3>
                <p id="user-confirmation-message" class="mt-2 text-sm leading-relaxed text-slate-500"
                    x-text="confirmationMessage"></p>
                <div class="mt-6 flex flex-col-reverse justify-center gap-3 sm:flex-row">
                    <button type="button" x-ref="confirmationCancel" @click="closeConfirmation()"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-secondary transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-200">
                        Batal
                    </button>
                    <button type="button" @click="submitConfirmedAction()"
                        :class="{
                            'bg-red-600 hover:bg-red-700 focus-visible:ring-red-200': confirmationTone === 'danger',
                            'bg-amber-600 hover:bg-amber-700 focus-visible:ring-amber-200': confirmationTone === 'warning',
                            'bg-green-600 hover:bg-green-700 focus-visible:ring-green-200': confirmationTone === 'success'
                        }"
                        class="inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold text-white transition focus-visible:outline-none focus-visible:ring-2">
                        <span x-text="confirmationAction"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
