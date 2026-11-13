@props(['logs', 'showContext' => false])

<x-table>
    <x-slot name="head">
        <x-table.cell>Waktu</x-table.cell>
        <x-table.cell>Aktor</x-table.cell>
        <x-table.cell>Kategori</x-table.cell>
        <x-table.cell>Aktivitas</x-table.cell>
        <x-table.cell>Subjek</x-table.cell>
        <x-table.cell>Detail</x-table.cell>
    </x-slot>

    @forelse ($logs as $log)
        <x-table.row striped>
            <td class="px-6 py-4 align-top">
                <p class="text-sm font-semibold text-secondary">{{ $log->created_at?->translatedFormat('d M Y') }}</p>
                <p class="text-xs text-slate-500">{{ $log->created_at?->format('H:i') }} WIB</p>
            </td>
            <td class="px-6 py-4 align-top">
                <p class="text-sm font-bold text-secondary">{{ $log->actor_name ?? 'Sistem' }}</p>
                @if (filled($log->actor_role))
                    <span
                        class="mt-1 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-600">
                        {{ $log->actor_role }}
                    </span>
                @endif
                @if (filled($log->actor_email))
                    <p class="mt-1 text-xs text-slate-400">{{ $log->actor_email }}</p>
                @endif
            </td>
            <td class="px-6 py-4 align-top">
                <x-audit-category-badge :category="$log->category" />
            </td>
            <td class="whitespace-normal px-6 py-4 align-top">
                <p class="text-sm font-semibold text-secondary">{{ $log->eventLabel() }}</p>
                <p class="mt-1 max-w-md text-sm text-slate-500">{{ $log->description }}</p>
            </td>
            <td class="px-6 py-4 align-top">
                @if (filled($log->subject_label))
                    <p class="max-w-56 truncate text-sm font-semibold text-secondary" title="{{ $log->subject_label }}">
                        {{ $log->subject_label }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $log->subjectTypeLabel() }}</p>
                @else
                    <span class="text-sm text-slate-400">&mdash;</span>
                @endif
            </td>
            <td class="whitespace-normal px-6 py-4 align-top">
                @if (filled($log->properties))
                    <details>
                        <summary class="cursor-pointer text-xs font-semibold text-primary hover:text-secondary">Lihat
                            detail
                        </summary>
                        <pre class="mt-2 max-w-xs overflow-x-auto rounded-lg bg-slate-50 p-3 text-[11px] leading-relaxed text-slate-600">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    </details>
                @else
                    <span class="text-sm text-slate-400">&mdash;</span>
                @endif

                @if ($showContext)
                    <div class="mt-3 space-y-1 text-[11px] text-slate-400">
                        <p>IP: {{ $log->ip_address ?? 'Tidak tercatat' }}</p>
                        <p class="max-w-xs break-words">{{ $log->user_agent ?? 'User agent tidak tercatat' }}</p>
                    </div>
                @endif
            </td>
        </x-table.row>
    @empty
        <tr>
            <td colspan="6" class="px-6 py-12 text-center">
                <p class="font-heading font-bold text-slate-400">Belum ada aktivitas</p>
                <p class="mt-1 text-sm text-slate-400">Audit log akan terisi saat pengguna melakukan aksi di platform.
                </p>
            </td>
        </tr>
    @endforelse
</x-table>
