@extends('layouts.app')
@section('page-title', 'Manajemen User')
@section('content')
    <x-card-dashboard title="Daftar Pengguna / Akun" subtitle="Informasi tentang user yang terdaftar">
        <x-slot name="actions">
            <x-button-dashboard variant="primary" size="sm">
                <a href="">Tambah Akun Baru </a>
            </x-button-dashboard>
        </x-slot>
        <div class="space-y-4">
            <x-table>
                <x-slot name="head">
                    <x-table.cell>Nama</x-table.cell>
                    <x-table.cell>Email</x-table.cell>
                    <x-table.cell>Nomor Telepon</x-table.cell>
                    <x-table.cell>Role</x-table.cell>
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
                            <x-button-dropdown variant="ghost" label="..." :position="$loop->last ? 'top-center' : 'bottom-middle'">
                                <x-item-dropdown href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="size-6">
                                        <path
                                            d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z" />
                                        <path
                                            d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z" />
                                    </svg>
                                    Edit
                                </x-item-dropdown>
                                <x-item-dropdown href="#">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="size-6">
                                        <path fill-rule="evenodd"
                                            d="m6.72 5.66 11.62 11.62A8.25 8.25 0 0 0 6.72 5.66Zm10.56 12.68L5.66 6.72a8.25 8.25 0 0 0 11.62 11.62ZM5.105 5.106c3.807-3.808 9.98-3.808 13.788 0 3.808 3.807 3.808 9.98 0 13.788-3.807 3.808-9.98 3.808-13.788 0-3.808-3.807-3.808-9.98 0-13.788Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Nonaktifkan
                                </x-item-dropdown>
                                <x-item-dropdown href="#" danger>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="size-6">
                                        <path fill-rule="evenodd"
                                            d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Hapus
                                </x-item-dropdown>
                            </x-button-dropdown>
                        </x-table.data>
                    </x-table.row>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
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
@endsection
