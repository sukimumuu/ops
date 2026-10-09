@extends('layouts.app')
@section('page-title', 'Manajemen User')
@section('content')
    <x-card-dashboard title="Daftar Pengguna / Akun" subtitle="Informasi tentang user yang terdaftar">
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
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
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
                                        <path fill-rule="evenodd"
                                            d="m6.72 5.66 11.62 11.62A8.25 8.25 0 0 0 6.72 5.66Zm10.56 12.68L5.66 6.72a8.25 8.25 0 0 0 11.62 11.62ZM5.105 5.106c3.807-3.808 9.98-3.808 13.788 0 3.808 3.807 3.808 9.98 0 13.788-3.807 3.808-9.98 3.808-13.788 0-3.808-3.807-3.808-9.98 0-13.788Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Nonaktifkan
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
