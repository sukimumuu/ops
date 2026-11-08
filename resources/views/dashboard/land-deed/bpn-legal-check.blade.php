@extends('layouts.app')
@section('page-title', 'Pengecekan Legalitas BPN & Validasi Pajak')

@section('content')
    @php
        $state = $transaction?->state;
        $stateLabel = match ($state) {
            'draft' => 'Draft',
            'held_in_escrow' => 'Dana escrow',
            'dp_paid' => 'DP dibayar',
            'bpn_checking' => 'Pengecekan BPN',
            'bpn_cleared' => 'BPN selesai',
            'ajb_scheduled' => 'AJB terjadwal',
            'completed' => 'Selesai',
            'refunded' => 'Dikembalikan',
            default => 'Belum ada transaksi',
        };
        $stateVariant = match ($state) {
            'bpn_cleared', 'ajb_scheduled', 'completed' => 'emerald',
            'bpn_checking' => 'blue',
            'dp_paid', 'held_in_escrow' => 'amber',
            'refunded' => 'rose',
            default => 'slate',
        };
        $isBpnStarted = in_array($state, ['bpn_checking', 'bpn_cleared', 'ajb_scheduled', 'completed'], true);
        $isBpnCleared = in_array($state, ['bpn_cleared', 'ajb_scheduled', 'completed'], true);
        $progressSteps = [
            ['label' => 'DP dibayar', 'done' => in_array($state, ['dp_paid', 'bpn_checking', 'bpn_cleared', 'ajb_scheduled', 'completed'], true)],
            ['label' => 'Pengecekan BPN', 'done' => $isBpnStarted],
            ['label' => 'Siap lanjut AJB', 'done' => $isBpnCleared],
        ];
    @endphp

    <div class="space-y-6">
        <x-ppat.header
            :breadcrumb="'Properti Klien / '.$property->title.' / Pengecekan Legalitas BPN'"
            :date="now()->locale('id')->translatedFormat('l, d F Y')"
            title="Pengecekan Legalitas BPN & Validasi Pajak"
            subtitle="Tinjau dokumen legal yang diunggah penjual, setujui atau tolak setiap dokumen, lalu lanjutkan transaksi ke tahap berikutnya."
            :actionLabel="null"
            exportLabel="Ekspor"
        />

        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('land-deed.client-properties.show', ['property' => $property->uuid]) }}"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7" />
                </svg>
                Kembali ke detail properti
            </a>

            @if ($transaction)
                <span @class([
                    'inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-bold',
                    'border-emerald-200 bg-emerald-50 text-emerald-700' => $stateVariant === 'emerald',
                    'border-blue-200 bg-blue-50 text-blue-700' => $stateVariant === 'blue',
                    'border-amber-200 bg-amber-50 text-amber-800' => $stateVariant === 'amber',
                    'border-rose-200 bg-rose-50 text-rose-700' => $stateVariant === 'rose',
                    'border-slate-200 bg-slate-50 text-slate-600' => $stateVariant === 'slate',
                ])>
                    Status transaksi: {{ $stateLabel }}
                </span>
            @endif
        </div>

        @if (session('status'))
            <div role="status"
                class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <p class="font-bold">Validasi dokumen gagal diproses.</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section aria-label="Ringkasan dokumen" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-ppat.stat-card
                :title="$metrics['total']['title']"
                :count="$metrics['total']['count']"
                :unit="$metrics['total']['unit']"
                :description="$metrics['total']['description']"
                :descriptionClass="$metrics['total']['descriptionClass']"
            >
                <x-slot:icon>
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </x-slot:icon>
            </x-ppat.stat-card>

            <x-ppat.stat-card
                :title="$metrics['pending']['title']"
                :count="$metrics['pending']['count']"
                :unit="$metrics['pending']['unit']"
                :description="$metrics['pending']['description']"
                :descriptionClass="$metrics['pending']['descriptionClass']"
            >
                <x-slot:icon>
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </x-slot:icon>
            </x-ppat.stat-card>

            <x-ppat.stat-card
                :title="$metrics['approved']['title']"
                :count="$metrics['approved']['count']"
                :unit="$metrics['approved']['unit']"
                :description="$metrics['approved']['description']"
                :descriptionClass="$metrics['approved']['descriptionClass']"
            >
                <x-slot:icon>
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </x-slot:icon>
            </x-ppat.stat-card>

            <x-ppat.stat-card
                :title="$metrics['rejected']['title']"
                :count="$metrics['rejected']['count']"
                :unit="$metrics['rejected']['unit']"
                :description="$metrics['rejected']['description']"
                :descriptionClass="$metrics['rejected']['descriptionClass']"
            >
                <x-slot:icon>
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </x-slot:icon>
            </x-ppat.stat-card>
        </section>

        @if ($metrics['rejected']['count'] > 0)
            <x-ppat.alert-banner type="warning">
                <span class="font-medium">{{ $metrics['rejected']['count'] }} dokumen ditolak.</span>
                Minta penjual mengunggah perbaikan sebelum pengecekan BPN dapat diselesaikan.
            </x-ppat.alert-banner>
        @elseif ($metrics['total']['count'] > 0 && $metrics['approved']['count'] === $metrics['total']['count'])
            <x-ppat.alert-banner type="info">
                <span class="font-medium">Seluruh dokumen telah disetujui.</span>
                Transaksi siap dilanjutkan ke tahap pelunasan dan penandatanganan AJB.
            </x-ppat.alert-banner>
        @else
            <x-ppat.alert-banner type="info">
                <span class="font-medium">{{ $metrics['pending']['count'] }} dokumen menunggu validasi.</span>
                Periksa keabsahan sertifikat dan kelengkapan dokumen pendukung sebelum menyetujui.
            </x-ppat.alert-banner>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)]">
            <div class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-xs">
                <div class="border-b border-slate-200/80 p-5">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-base font-bold text-secondary">Dokumen properti</h2>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">
                            {{ $documents->count() }} dokumen
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ $property->title }} &middot; {{ $property->certificate_type }} &middot;
                        {{ collect([$property->district, $property->city])->filter()->implode(', ') }}
                    </p>
                </div>

                @if ($documents->isEmpty())
                    <div class="px-6 py-16 text-center">
                        <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-slate-100 text-slate-400"
                            aria-hidden="true">
                            <svg class="size-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <h3 class="mt-5 font-heading text-lg font-bold text-secondary">Belum ada dokumen legal</h3>
                        <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                            Penjual belum mengunggah sertifikat, IMB/PBG, atau dokumen pajak untuk properti ini.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="border-b border-slate-200 bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-600">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-5 pr-3">Dokumen</th>
                                    <th scope="col" class="px-3 py-3.5">Status</th>
                                    <th scope="col" class="px-3 py-3.5">Catatan Validasi</th>
                                    <th scope="col" class="px-3 py-3.5">Divalidasi</th>
                                    <th scope="col" class="py-3.5 pl-3 pr-5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                                @foreach ($documents as $document)
                                    <tr class="align-top transition-colors hover:bg-slate-50/60">
                                        <td class="py-4 pl-5 pr-3">
                                            <div class="font-semibold text-secondary">{{ $document->typeLabel() }}</div>
                                            <div class="mt-0.5 text-[11px] text-slate-400">
                                                {{ basename($document->file_path) }}
                                            </div>
                                            @can('viewSensitiveDocument', $property)
                                                <a href="{{ route('property-review.file', $document) }}" target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="mt-2 inline-flex items-center gap-1 text-[11px] font-semibold text-primary hover:underline">
                                                    Lihat dokumen
                                                    <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2"
                                                        viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M14 5h5v5m0-5-9 9M19 14v5H5V5h5" />
                                                    </svg>
                                                </a>
                                            @endcan
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap">
                                            @if ($document->validation_status === \App\Models\PropertyPhoto::VALIDATION_APPROVED)
                                                <span class="inline-flex items-center rounded-md border border-emerald-200/70 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                                    {{ $document->validationStatusLabel() }}
                                                </span>
                                            @elseif ($document->validation_status === \App\Models\PropertyPhoto::VALIDATION_REJECTED)
                                                <span class="inline-flex items-center rounded-md border border-rose-200/70 bg-rose-50 px-2.5 py-1 text-xs font-medium text-rose-700">
                                                    {{ $document->validationStatusLabel() }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-md border border-amber-200/70 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-800">
                                                    {{ $document->validationStatusLabel() }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="max-w-xs px-3 py-4">
                                            @if (filled($document->validation_notes))
                                                <p class="text-xs leading-5 text-slate-600">{{ $document->validation_notes }}</p>
                                            @else
                                                <p class="text-[11px] text-slate-400">Belum ada catatan.</p>
                                            @endif
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap">
                                            @if ($document->validated_at)
                                                <div class="font-medium text-secondary">
                                                    {{ $document->validated_at->locale('id')->translatedFormat('d M Y') }}
                                                </div>
                                                <div class="mt-0.5 text-[11px] text-slate-400">
                                                    {{ $document->validated_at->format('H:i') }} WIB
                                                    @if ($document->validatedBy)
                                                        &middot; {{ $document->validatedBy->name }}
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-[11px] text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="py-4 pl-3 pr-5">
                                            @if ($document->validation_status === \App\Models\PropertyPhoto::VALIDATION_APPROVED)
                                                <span class="ml-auto inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                                                    <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                                                        viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                                                    </svg>
                                                    Disetujui
                                                </span>
                                            @elseif ($canReview)
                                                <form method="POST"
                                                    action="{{ route('land-deed.documents.review', ['property' => $property->uuid, 'document' => $document->id]) }}"
                                                    class="ml-auto flex w-56 flex-col gap-2">
                                                    @csrf
                                                    <textarea name="validation_notes" rows="2" placeholder="Catatan validasi (wajib jika ditolak)"
                                                        class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 placeholder:text-slate-400 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">{{ old('validation_notes') }}</textarea>
                                                    <div class="flex items-center justify-end gap-2">
                                                        <button type="submit" name="validation_status" value="rejected"
                                                            class="inline-flex items-center rounded-lg border border-rose-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-700 shadow-xs transition hover:bg-rose-50">
                                                            Tolak
                                                        </button>
                                                        <button type="submit" name="validation_status" value="approved"
                                                            class="inline-flex items-center rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white shadow-xs transition hover:bg-primary-hover">
                                                            Setujui
                                                        </button>
                                                    </div>
                                                </form>
                                            @else
                                                <span class="text-[11px] text-slate-400">Tidak ada akses validasi.</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <aside class="space-y-6">
                <x-card-dashboard title="Transaksi & progres" subtitle="Status pengecekan BPN pada transaksi properti">
                    @if ($transaction)
                        <div class="space-y-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Status saat ini</p>
                                    <p class="mt-1 font-heading text-lg font-bold text-secondary">{{ $stateLabel }}</p>
                                </div>
                                <span @class([
                                    'inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold',
                                    'border-emerald-200 bg-emerald-50 text-emerald-700' => $stateVariant === 'emerald',
                                    'border-blue-200 bg-blue-50 text-blue-700' => $stateVariant === 'blue',
                                    'border-amber-200 bg-amber-50 text-amber-800' => $stateVariant === 'amber',
                                    'border-rose-200 bg-rose-50 text-rose-700' => $stateVariant === 'rose',
                                    'border-slate-200 bg-slate-50 text-slate-600' => $stateVariant === 'slate',
                                ])>
                                    {{ $stateLabel }}
                                </span>
                            </div>

                            <ol class="space-y-3 border-t border-slate-100 pt-4">
                                @foreach ($progressSteps as $index => $step)
                                    <li class="flex items-start gap-3">
                                        <span @class([
                                            'flex size-7 shrink-0 items-center justify-center rounded-full border text-[11px] font-bold',
                                            'border-emerald-200 bg-emerald-50 text-emerald-600' => $step['done'],
                                            'border-slate-200 bg-white text-slate-400' => ! $step['done'],
                                        ])>
                                            @if ($step['done'])
                                                <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                                                    viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                                                </svg>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </span>
                                        <div class="pt-0.5">
                                            <p @class([
                                                'text-sm font-semibold',
                                                'text-secondary' => $step['done'],
                                                'text-slate-400' => ! $step['done'],
                                            ])>{{ $step['label'] }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>

                            <div class="space-y-2 border-t border-slate-100 pt-4 text-xs text-slate-500">
                                <p>
                                    <span class="font-semibold text-slate-600">Pembeli:</span>
                                    {{ $transaction->buyer?->name ?? '-' }}
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-600">Penjual:</span>
                                    {{ $property->seller?->name ?? '-' }}
                                </p>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-slate-500">
                            Transaksi belum dibuat. Pengecekan BPN akan terhubung setelah pembeli memulai transaksi.
                        </p>
                    @endif
                </x-card-dashboard>

                <x-card-dashboard title="Informasi properti">
                    <dl class="space-y-3 text-sm">
                        <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-3">
                            <dt class="text-xs font-semibold text-slate-400">Sertifikat</dt>
                            <dd class="text-right font-semibold text-secondary">{{ $property->certificate_type }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-3">
                            <dt class="text-xs font-semibold text-slate-400">Lokasi</dt>
                            <dd class="text-right font-semibold text-secondary">
                                {{ collect([$property->district, $property->city])->filter()->implode(', ') }}
                            </dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-xs font-semibold text-slate-400">Alamat</dt>
                            <dd class="text-right font-semibold text-secondary">{{ $property->address }}</dd>
                        </div>
                    </dl>
                </x-card-dashboard>
            </aside>
        </div>

        <x-ppat.footer />
    </div>
@endsection
