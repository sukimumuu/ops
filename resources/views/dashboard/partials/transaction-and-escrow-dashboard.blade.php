<div class="space-y-6">
    @if (session('status'))
        <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('status') }}
        </div>
    @endif
    @if ($errors->any())
        <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="font-heading text-2xl font-bold text-secondary">Transaksi &amp; Manajemen Escrow</h1>
            <p class="mt-1 text-sm text-slate-500">Pantau pembayaran terverifikasi dan pencairan dana berdasarkan catatan transaksi.</p>
        </div>
        <p class="text-xs text-slate-500">Semua nilai dalam Rupiah (IDR)</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['label' => 'Dana tertahan', 'value' => $summary['held'], 'description' => 'Dana terverifikasi, di luar proses pencairan'],
            ['label' => 'Menunggu konfirmasi', 'value' => $summary['pending_release'], 'description' => 'Pencairan bank yang belum dikonfirmasi'],
            ['label' => 'Telah dicairkan', 'value' => $summary['released'], 'description' => 'Total dana keluar yang tercatat'],
            ['label' => 'Pembayaran menunggu', 'value' => $summary['pending_payments'], 'description' => 'Permintaan masuk, belum terverifikasi'],
            ['label' => 'Siap dicairkan', 'value' => $summary['available_to_release'], 'description' => 'Memenuhi syarat Stage 6'],
        ] as $metric)
            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold text-slate-500">{{ $metric['label'] }}</p>
                <p class="mt-2 font-heading text-lg font-bold text-secondary">Rp {{ number_format((float) $metric['value'], 0, ',', '.') }}</p>
                <p class="mt-1 text-[11px] leading-4 text-slate-500">{{ $metric['description'] }}</p>
            </article>
        @endforeach
    </div>

    <form method="GET" class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="xl:col-span-2">
            <label for="transaction-search" class="mb-1 block text-xs font-semibold text-slate-600">Cari transaksi</label>
            <input id="transaction-search" type="search" name="search" value="{{ $filters['search'] ?? '' }}"
                placeholder="Properti, pembeli, penjual, atau referensi"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-secondary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
        </div>
        <div>
            <label for="payment-type-filter" class="mb-1 block text-xs font-semibold text-slate-600">Jenis pembayaran</label>
            <select id="payment-type-filter" name="payment_type"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-secondary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                <option value="">Semua jenis</option>
                <option value="dp" @selected(($filters['payment_type'] ?? '') === 'dp')>Tahap 2 · DP</option>
                <option value="final" @selected(($filters['payment_type'] ?? '') === 'final')>Tahap 4 · Pelunasan escrow</option>
            </select>
        </div>
        <div>
            <label for="payment-status-filter" class="mb-1 block text-xs font-semibold text-slate-600">Status pembayaran</label>
            <select id="payment-status-filter" name="payment_status"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-secondary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                <option value="">Semua status</option>
                @foreach (['creating' => 'Disiapkan', 'pending' => 'Menunggu pembayaran', 'paid' => 'Terverifikasi', 'expired' => 'Kedaluwarsa', 'failed' => 'Gagal', 'canceled' => 'Dibatalkan'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['payment_status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="release-status-filter" class="mb-1 block text-xs font-semibold text-slate-600">Status pencairan</label>
            <select id="release-status-filter" name="release_status"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-secondary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                <option value="">Semua status</option>
                <option value="pending" @selected(($filters['release_status'] ?? '') === 'pending')>Menunggu konfirmasi</option>
                <option value="released" @selected(($filters['release_status'] ?? '') === 'released')>Dicairkan</option>
            </select>
        </div>
        <div class="flex items-end gap-2 sm:col-span-2 xl:col-span-5">
            <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white transition hover:bg-primary-hover">Terapkan filter</button>
            <a href="{{ request()->url() }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Reset</a>
        </div>
    </form>

    <section class="overflow-hidden rounded-xl border border-violet-200 bg-white shadow-sm">
        <div class="border-b border-violet-100 bg-violet-50/60 px-4 py-4 sm:px-5">
            <h2 class="font-heading text-base font-bold text-secondary">Transaksi memenuhi syarat pencairan</h2>
            <p class="mt-1 text-xs text-slate-600">Pencairan hanya tersedia setelah DP, pelunasan, dan bukti AJB yang disetujui PPAT terverifikasi.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[850px] text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Properti &amp; transaksi</th>
                        <th class="px-4 py-3">Pembeli</th>
                        <th class="px-4 py-3">Penerima</th>
                        <th class="px-4 py-3">Dana siap dilepas</th>
                        <th class="px-4 py-3">AJB ditandatangani</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($eligibleTransactions as $transaction)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-secondary">{{ $transaction->property->title }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $transaction->uuid }} · {{ collect([$transaction->property->city, $transaction->property->province])->filter()->implode(', ') }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $transaction->buyer->name }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $transaction->seller->name }}</td>
                            <td class="px-4 py-3 font-bold text-secondary">Rp {{ number_format((float) $transaction->total_property_amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->ajb_signed_at?->locale('id')->translatedFormat('d M Y, H:i') ?? '—' }} WIB</td>
                            <td class="px-4 py-3">
                                @can('transactions.trigger_escrow_release')
                                    <form method="POST" action="{{ route('escrow-disbursements.store', ['transaction' => $transaction->uuid]) }}"
                                        onsubmit="return confirm('Mulai pencairan Rp {{ number_format((float) $transaction->total_property_amount, 0, ',', '.') }} kepada {{ $transaction->seller->name }}? Transfer bank harus dilakukan di luar aplikasi lalu dikonfirmasi di sini.')">
                                        @csrf
                                        <button type="submit" class="whitespace-nowrap rounded-lg bg-primary px-3 py-2 text-xs font-bold text-white transition hover:bg-primary-hover">
                                            Mulai pencairan
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada transaksi yang memenuhi syarat pencairan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 sm:px-5">
            <x-table.pagination :paginator="$eligibleTransactions" />
        </div>
    </section>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-4 py-4 sm:px-5">
            <h2 class="font-heading text-base font-bold text-secondary">Riwayat pencairan dana</h2>
            <p class="mt-1 text-xs text-slate-500">Catatan transfer keluar, konfirmasi, dan pembatalan tersimpan untuk audit.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Properti &amp; transaksi</th>
                        <th class="px-4 py-3">Penerima</th>
                        <th class="px-4 py-3">Jumlah</th>
                        <th class="px-4 py-3">Dibuat / status</th>
                        <th class="px-4 py-3">Referensi transfer</th>
                        <th class="px-4 py-3">Audit &amp; aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($disbursements as $disbursement)
                        <tr class="align-top">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-secondary">{{ $disbursement->transaction->property->title }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $disbursement->transaction->uuid }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-700">{{ $disbursement->recipient_name }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">Penjual</p>
                            </td>
                            <td class="px-4 py-3 font-bold text-secondary">Rp {{ number_format((float) $disbursement->amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <p class="text-slate-700">{{ $disbursement->created_at->locale('id')->translatedFormat('d M Y, H:i') }} WIB</p>
                                <span @class([
                                    'mt-1 inline-flex rounded-full border px-2 py-0.5 text-[11px] font-bold',
                                    'border-amber-200 bg-amber-50 text-amber-800' => $disbursement->status === \App\Models\EscrowDisbursement::STATUS_PENDING,
                                    'border-emerald-200 bg-emerald-50 text-emerald-700' => $disbursement->status === \App\Models\EscrowDisbursement::STATUS_RELEASED,
                                ])>
                                    {{ ['pending' => 'Menunggu konfirmasi', 'released' => 'Dicairkan'][$disbursement->status] ?? $disbursement->status }}
                                </span>
                                @if ($disbursement->confirmed_at)
                                    <p class="mt-1 text-[11px] text-slate-500">Dikonfirmasi {{ $disbursement->confirmed_at->locale('id')->translatedFormat('d M Y, H:i') }} WIB</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $disbursement->transfer_reference ?: '—' }}</td>
                            <td class="px-4 py-3">
                                @if ($disbursement->status === \App\Models\EscrowDisbursement::STATUS_PENDING && auth()->user()->can('transactions.trigger_escrow_release'))
                                    <p class="mb-1 text-xs font-semibold text-slate-700">
                                        Konfirmasi transfer Rp {{ number_format((float) $disbursement->amount, 0, ',', '.') }}
                                        kepada {{ $disbursement->recipient_name }}.
                                    </p>
                                    <p class="mb-2 text-[11px] text-slate-500">Dimulai oleh {{ $disbursement->initiatedBy->name }}</p>
                                    <form method="POST" action="{{ route('escrow-disbursements.confirm', ['escrowDisbursement' => $disbursement->uuid]) }}" class="mb-3 space-y-2">
                                        @csrf
                                        <label for="transfer-reference-{{ $disbursement->id }}" class="block text-xs font-semibold text-slate-600">Referensi transfer bank</label>
                                        <input id="transfer-reference-{{ $disbursement->id }}" name="transfer_reference" required maxlength="255"
                                            class="w-full rounded-lg border border-slate-300 px-2.5 py-2 text-xs focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                                        <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-700"
                                            onclick="return confirm('Konfirmasi bahwa transfer bank telah berhasil diproses? Tahap 6 akan ditandai selesai.')">
                                            Konfirmasi transfer
                                        </button>
                                    </form>
                                @elseif ($disbursement->confirmedBy)
                                    <p class="text-[11px] text-slate-500">Dikonfirmasi oleh {{ $disbursement->confirmedBy->name }}</p>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada riwayat pencairan dana.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 sm:px-5">
            <x-table.pagination :paginator="$disbursements" />
        </div>
    </section>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-4 py-4 sm:px-5">
            <h2 class="font-heading text-base font-bold text-secondary">Riwayat pembayaran masuk</h2>
            <p class="mt-1 text-xs text-slate-500">DP Stage 2 dan pelunasan escrow Stage 4 dari permintaan pembayaran Xendit serta verifikasi webhook.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Properti &amp; transaksi</th>
                        <th class="px-4 py-3">Pembeli / penjual</th>
                        <th class="px-4 py-3">Jenis</th>
                        <th class="px-4 py-3">Jumlah / kanal</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Status / referensi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-secondary">{{ $payment->transaction->property->title }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $payment->transaction->uuid }} · {{ collect([$payment->transaction->property->city, $payment->transaction->property->province])->filter()->implode(', ') }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-slate-700">{{ $payment->transaction->buyer->name }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">Pembeli · {{ $payment->transaction->seller->name }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $payment->isFinalPayment() ? 'Tahap 4 · Pelunasan escrow' : 'Tahap 2 · DP' }}</td>
                            <td class="px-4 py-3">
                                <p class="font-bold text-secondary">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $payment->channel_code }} · {{ $payment->currency }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ ($payment->paid_at ?? $payment->created_at)->locale('id')->translatedFormat('d M Y, H:i') }} WIB</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'inline-flex rounded-full border px-2 py-0.5 text-[11px] font-bold',
                                    'border-emerald-200 bg-emerald-50 text-emerald-700' => $payment->status === \App\Models\DpPaymentAttempt::STATUS_PAID,
                                    'border-amber-200 bg-amber-50 text-amber-800' => in_array($payment->status, [\App\Models\DpPaymentAttempt::STATUS_CREATING, \App\Models\DpPaymentAttempt::STATUS_PENDING], true),
                                    'border-rose-200 bg-rose-50 text-rose-700' => in_array($payment->status, [\App\Models\DpPaymentAttempt::STATUS_EXPIRED, \App\Models\DpPaymentAttempt::STATUS_FAILED, \App\Models\DpPaymentAttempt::STATUS_CANCELED], true),
                                ])>
                                    {{ ['creating' => 'Disiapkan', 'pending' => 'Menunggu pembayaran', 'paid' => 'Terverifikasi', 'expired' => 'Kedaluwarsa', 'failed' => 'Gagal', 'canceled' => 'Dibatalkan'][$payment->status] ?? $payment->status }}
                                </span>
                                <p class="mt-1 max-w-xs truncate text-[11px] text-slate-500" title="{{ $payment->reference_id }}">{{ $payment->reference_id }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Tidak ada pembayaran yang cocok dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 sm:px-5">
            <x-table.pagination :paginator="$payments" />
        </div>
    </section>
</div>
