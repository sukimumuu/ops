@php
    $bpnProgress = $bpnProgress ?? $property->bpnVerificationProgress();

    $bpnTone = match ($bpnProgress['tone']) {
        'emerald' => [
            'bar' => 'bg-emerald-500',
            'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'icon' => 'text-emerald-600',
        ],
        'rose' => [
            'bar' => 'bg-rose-500',
            'badge' => 'border-rose-200 bg-rose-50 text-rose-700',
            'icon' => 'text-rose-600',
        ],
        'amber' => [
            'bar' => 'bg-amber-500',
            'badge' => 'border-amber-200 bg-amber-50 text-amber-800',
            'icon' => 'text-amber-600',
        ],
        default => [
            'bar' => 'bg-slate-400',
            'badge' => 'border-slate-200 bg-slate-50 text-slate-600',
            'icon' => 'text-slate-500',
        ],
    };
@endphp

<div class="mt-3 space-y-3 rounded-lg border border-slate-200 bg-white p-3 text-left">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                Progres pengecekan PPAT
            </p>
            <span class="mt-1 inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $bpnTone['badge'] }}">
                {{ $bpnProgress['label'] }}
            </span>
            <p class="mt-2 text-xs leading-5 text-slate-500">
                {{ $bpnProgress['description'] }}
            </p>
        </div>
        <span class="shrink-0 font-heading text-2xl font-bold text-secondary">
            {{ $bpnProgress['percentage'] }}%
        </span>
    </div>

    <div class="h-2 overflow-hidden rounded-full bg-slate-100" role="progressbar"
        aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $bpnProgress['percentage'] }}"
        aria-label="Progres verifikasi dokumen oleh PPAT">
        <div class="h-full rounded-full transition-all duration-500 {{ $bpnTone['bar'] }}"
            style="width: {{ $bpnProgress['percentage'] }}%"></div>
    </div>

    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] font-medium text-slate-500">
        <span>{{ $bpnProgress['approved'] }} dari {{ $bpnProgress['total'] }} dokumen disetujui</span>
        @if ($bpnProgress['pending'] > 0)
            <span>&middot; {{ $bpnProgress['pending'] }} menunggu</span>
        @endif
        @if ($bpnProgress['rejected'] > 0)
            <span class="text-rose-600">&middot; {{ $bpnProgress['rejected'] }} ditolak</span>
        @endif
    </div>

    <p class="text-[11px] text-slate-500">
        PPAT:
        <span class="font-semibold text-slate-700">{{ $property->assignedPpat?->name ?? 'Belum ditugaskan' }}</span>
    </p>

    @if ($bpnProgress['documents']->isEmpty())
        <div class="rounded-lg border border-dashed border-slate-200 bg-slate-50/70 px-3 py-4 text-center">
            <p class="text-xs font-semibold text-slate-600">Belum ada dokumen legal</p>
            <p class="mt-1 text-[11px] leading-5 text-slate-400">
                Progres akan muncul setelah penjual mengunggah sertifikat, IMB/PBG, atau dokumen pajak.
            </p>
        </div>
    @else
        <ul class="space-y-2">
            @foreach ($bpnProgress['documents'] as $document)
                @php
                    $documentTone = match ($document->validation_status) {
                        \App\Models\PropertyPhoto::VALIDATION_APPROVED => [
                            'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                            'dot' => 'bg-emerald-500',
                        ],
                        \App\Models\PropertyPhoto::VALIDATION_REJECTED => [
                            'badge' => 'border-rose-200 bg-rose-50 text-rose-700',
                            'dot' => 'bg-rose-500',
                        ],
                        default => [
                            'badge' => 'border-amber-200 bg-amber-50 text-amber-800',
                            'dot' => 'bg-amber-500',
                        ],
                    };
                @endphp

                <li class="rounded-lg border border-slate-100 bg-slate-50/60 px-3 py-2.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-secondary">{{ $document->typeLabel() }}</p>
                            @if ($document->validated_at)
                                <p class="mt-0.5 text-[11px] text-slate-400">
                                    Ditinjau oleh {{ $document->validatedBy?->name ?? 'PPAT' }}
                                    &middot;
                                    {{ $document->validated_at->locale('id')->translatedFormat('d M Y, H:i') }} WIB
                                </p>
                            @else
                                <p class="mt-0.5 text-[11px] text-slate-400">Belum ditinjau PPAT</p>
                            @endif
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $documentTone['badge'] }}">
                            <span class="size-1.5 rounded-full {{ $documentTone['dot'] }}"></span>
                            {{ $document->validationStatusLabel() }}
                        </span>
                    </div>

                    @if (filled($document->validation_notes))
                        <p class="mt-2 rounded-md border border-slate-200 bg-white px-2.5 py-2 text-[11px] leading-5 text-slate-600">
                            <span class="font-semibold text-slate-500">
                                {{ $document->validation_status === \App\Models\PropertyPhoto::VALIDATION_REJECTED ? 'Alasan penolakan:' : 'Catatan PPAT:' }}
                            </span>
                            {{ $document->validation_notes }}
                        </p>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    @if ($bpnProgress['latest_validated_at'])
        <p class="text-[11px] text-slate-400">
            Terakhir diperbarui
            {{ $bpnProgress['latest_validated_at']->locale('id')->translatedFormat('d F Y, H:i') }} WIB
        </p>
    @endif
</div>
