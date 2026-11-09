<div class="mt-3 space-y-3 rounded-lg border border-slate-200 bg-white p-3 text-left">
    @if (! ($isStageSevenUnlocked ?? false))
        <span class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600">
            Tahap 7 terkunci
        </span>
        <p class="text-xs leading-5 text-slate-600">
            PPAT dapat mengunggah Tanda Terima BPN setelah Admin mengonfirmasi pencairan dana escrow pada Tahap 6.
        </p>
        @if ($escrowDisbursement?->status === \App\Models\EscrowDisbursement::STATUS_PENDING)
            <p class="text-xs font-semibold text-amber-700">Pencairan dana masih menunggu konfirmasi Admin.</p>
        @endif
    @elseif ($transaction?->hasCompletedCertificateTransfer())
        <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700">
            Balik nama sertifikat selesai
        </span>
        <dl class="grid gap-2 text-xs sm:grid-cols-2">
            <div>
                <dt class="text-slate-500">Nomor tanda terima BPN</dt>
                <dd class="mt-0.5 break-all font-semibold text-secondary">{{ $transaction->certificate_transfer_reference }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Dikirim</dt>
                <dd class="mt-0.5 font-medium text-secondary">
                    {{ $transaction->certificate_transfer_submitted_at?->locale('id')->translatedFormat('d F Y, H:i') }} WIB
                </dd>
            </div>
            <div>
                <dt class="text-slate-500">PPAT</dt>
                <dd class="mt-0.5 font-medium text-secondary">{{ $transaction->certificateTransferSubmittedBy?->name ?? 'PPAT' }}</dd>
            </div>
            @if (filled($transaction->certificate_transfer_notes))
                <div class="sm:col-span-2">
                    <dt class="text-slate-500">Catatan</dt>
                    <dd class="mt-0.5 whitespace-pre-line text-secondary">{{ $transaction->certificate_transfer_notes }}</dd>
                </div>
            @endif
        </dl>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('certificate-transfer.receipt.view', ['property' => $property->uuid]) }}"
                target="_blank" rel="noopener"
                class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-800 transition hover:bg-emerald-100">
                Lihat Tanda Terima BPN
            </a>
            <a href="{{ route('certificate-transfer.receipt.download', ['property' => $property->uuid]) }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-800 transition hover:bg-emerald-100">
                Unduh Tanda Terima BPN
            </a>
        </div>
        @if (auth()->user()?->hasRole('PPAT') && $property->assigned_ppat_id === auth()->id())
            <p class="text-xs text-slate-500">Unggah dokumen pengganti hanya jika tanda terima perlu diperbarui.</p>
        @endif
    @else
        <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-800">
            Menunggu Tanda Terima BPN
        </span>
        <p class="text-xs leading-5 text-slate-600">
            Pencairan escrow telah dikonfirmasi. Tahap akhir pembelian menunggu bukti pengajuan balik nama dari PPAT.
        </p>
    @endif

    @if (($isStageSevenUnlocked ?? false)
        && auth()->user()?->hasRole('PPAT')
        && $property->assigned_ppat_id === auth()->id())
        <form method="POST" action="{{ route('land-deed.certificate-transfer.store', ['property' => $property->uuid]) }}"
            enctype="multipart/form-data" class="space-y-3 border-t border-slate-100 pt-3">
            @csrf
            <div>
                <label for="bpn-receipt" class="block text-xs font-semibold text-slate-700">
                    {{ $transaction?->hasCompletedCertificateTransfer() ? 'Ganti Tanda Terima BPN' : 'Unggah Tanda Terima BPN' }}
                </label>
                <input id="bpn-receipt" name="bpn_receipt" type="file" accept=".pdf,.jpg,.jpeg,.png" required
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-2.5 py-2 text-xs text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700">
                @error('bpn_receipt')
                    <p class="mt-1 text-xs text-rose-700">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="bpn-reference" class="block text-xs font-semibold text-slate-700">Nomor atau referensi tanda terima</label>
                <input id="bpn-reference" name="bpn_reference" type="text" maxlength="255" required
                    value="{{ old('bpn_reference', $transaction?->certificate_transfer_reference) }}"
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-700 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                @error('bpn_reference')
                    <p class="mt-1 text-xs text-rose-700">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="bpn-notes" class="block text-xs font-semibold text-slate-700">Catatan (opsional)</label>
                <textarea id="bpn-notes" name="bpn_notes" rows="2" maxlength="1000"
                    class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2 text-xs text-slate-700 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">{{ old('bpn_notes', $transaction?->certificate_transfer_notes) }}</textarea>
                @error('bpn_notes')
                    <p class="mt-1 text-xs text-rose-700">{{ $message }}</p>
                @enderror
            </div>
            @error('certificate_transfer')
                <p class="text-xs font-semibold text-rose-700">{{ $message }}</p>
            @enderror
            <button type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white transition hover:bg-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30">
                {{ $transaction?->hasCompletedCertificateTransfer() ? 'Simpan pembaruan' : 'Simpan Tanda Terima BPN' }}
            </button>
        </form>
    @endif
</div>
