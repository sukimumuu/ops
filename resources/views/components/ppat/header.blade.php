@props([
    'breadcrumb' => 'Operasional kantor / Dashboard Antrian',
    'date' => 'Jumat, 16 Oktober 2026',
    'title',
    'subtitle',
    'actionLabel' => '+ Tambah Antrian',
    'exportLabel' => 'Ekspor',
])

<div class="space-y-3 pb-6 border-b border-slate-200/80 mb-6">
    <div class="flex items-center justify-between text-xs text-slate-500">
        <span>{{ $breadcrumb }}</span>
        <div class="flex items-center gap-1.5 font-medium text-slate-600">
            <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </svg>
            <span>{{ $date }}</span>
        </div>
    </div>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-secondary">{{ $title }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $subtitle }}</p>
        </div>
        <div class="flex items-center gap-3 self-start sm:self-auto shrink-0">
            <button type="button" class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-slate-900 transition-colors shadow-xs cursor-pointer">
                <svg class="size-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                </svg>
                <span>{{ $exportLabel }}</span>
            </button>
            @if($actionLabel)
                <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-primary/90 transition-colors shadow-xs cursor-pointer">
                    <span>{{ $actionLabel }}</span>
                </button>
            @endif
        </div>
    </div>
</div>
