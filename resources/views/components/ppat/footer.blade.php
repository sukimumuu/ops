@props([
    'office' => 'Kantor PPAT Andini Prameswari, S.H., M.Kn. - Tangerang Selatan',
    'timestamp' => 'Data operasional internal • 16 Oktober 2026, 08.30 WIB',
])

<div class="mt-8 pt-6 border-t border-slate-200/80 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
    <div>{{ $office }}</div>
    <div>{{ $timestamp }}</div>
</div>
