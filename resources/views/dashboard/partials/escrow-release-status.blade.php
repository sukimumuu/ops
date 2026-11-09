<div class="mt-3 rounded-lg border border-slate-200 bg-white p-3 text-left">
    @if ($escrowDisbursement?->status === \App\Models\EscrowDisbursement::STATUS_RELEASED)
        <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700">
            Dana telah dicairkan
        </span>
        <dl class="mt-3 grid gap-2 text-xs sm:grid-cols-2">
            <div>
                <dt class="text-slate-500">Jumlah</dt>
                <dd class="mt-0.5 font-bold text-secondary">Rp {{ number_format((float) $escrowDisbursement->amount, 0, ',', '.') }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Penerima</dt>
                <dd class="mt-0.5 font-semibold text-secondary">{{ $escrowDisbursement->recipient_name }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Dikonfirmasi</dt>
                <dd class="mt-0.5 font-medium text-secondary">{{ $escrowDisbursement->confirmed_at?->locale('id')->translatedFormat('d F Y, H:i') }} WIB</dd>
            </div>
            <div>
                <dt class="text-slate-500">Referensi transfer</dt>
                <dd class="mt-0.5 break-all font-medium text-secondary">{{ $escrowDisbursement->transfer_reference }}</dd>
            </div>
        </dl>
    @elseif ($escrowDisbursement?->status === \App\Models\EscrowDisbursement::STATUS_PENDING)
        <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-800">
            Menunggu konfirmasi transfer bank
        </span>
        <p class="mt-2 text-xs leading-5 text-slate-600">
            Rp {{ number_format((float) $escrowDisbursement->amount, 0, ',', '.') }} akan dicairkan kepada {{ $escrowDisbursement->recipient_name }}.
            Progres Stage 6 selesai setelah Admin atau Superadmin mengonfirmasi transfer.
        </p>
        <p class="mt-1 text-[11px] text-slate-500">Dimulai {{ $escrowDisbursement->created_at->locale('id')->translatedFormat('d F Y, H:i') }} WIB.</p>
    @elseif ($transaction?->state === 'ajb_scheduled' && filled($transaction?->ajb_document_path))
        <span class="inline-flex rounded-full border border-violet-200 bg-violet-50 px-2.5 py-1 text-[11px] font-bold text-violet-800">
            Menunggu pencairan Admin/Superadmin
        </span>
        <p class="mt-2 text-xs leading-5 text-slate-600">
            Dana yang dapat dicairkan setelah verifikasi seluruh pembayaran:
            <span class="font-bold text-secondary">Rp {{ number_format((float) $escrowReleaseAmount, 0, ',', '.') }}</span>.
        </p>
    @else
        <span class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600">
            Tahap 6 terkunci
        </span>
        <p class="mt-2 text-xs leading-5 text-slate-600">
            Dana hanya dapat dicairkan setelah DP dan pelunasan escrow terverifikasi serta bukti AJB ditandatangani dan diunggah PPAT.
        </p>
    @endif
</div>
