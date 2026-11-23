@extends('layouts.app')
@section('page-title', 'Rekening Pencairan')
@section('content')
    <div class="space-y-6">
        @if (session('status'))
            <div role="status"
                class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div role="alert"
                class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Dashboard seller</p>
                <h1 class="mt-1 font-heading text-2xl font-bold tracking-tight text-secondary">Rekening pencairan</h1>
                <p class="mt-1 max-w-2xl text-sm text-slate-500">
                    Rekening ini digunakan tim Mihom untuk mentransfer dana escrow hasil penjualan properti Anda.
                    Pencairan tidak dapat diproses sebelum rekening dilengkapi.
                </p>
            </div>

            @if ($hasDisbursementAccount)
                <span
                    class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                    <span class="size-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                    Rekening aktif
                </span>
            @else
                <span
                    class="inline-flex w-fit items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-800">
                    <span class="size-1.5 rounded-full bg-amber-500" aria-hidden="true"></span>
                    Rekening belum lengkap
                </span>
            @endif
        </header>

        @unless ($hasDisbursementAccount)
            <div role="alert" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                <p class="text-sm font-bold text-amber-900">Lengkapi rekening untuk membuka pencairan dana escrow</p>
                <p class="mt-1 text-sm text-amber-800">
                    Admin dan Superadmin tidak dapat memulai pencairan selama nama bank, nomor rekening, dan nama
                    pemilik rekening belum tersimpan.
                </p>
            </div>
        @endunless

        @if ($pendingDisbursementCount > 0)
            <div role="status"
                class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-medium text-sky-800">
                {{ $pendingDisbursementCount }} pencairan sedang menunggu konfirmasi transfer bank oleh admin.
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
            <x-card-dashboard title="Data rekening penampung"
                subtitle="Pastikan nama pemilik rekening sama dengan nama pada rekening bank agar transfer tidak gagal.">
                <form method="POST" action="{{ route('seller.disbursement-account.update') }}" class="space-y-5"
                    autocomplete="off">
                    @csrf
                    @method('PUT')

                    @php
                        $selectedBank = old('bank_name', $user->bank_name);
                        $bankNameError = $errors->first('bank_name');
                        $legacyBankName =
                            filled($user->bank_name) && !array_key_exists((string) $user->bank_name, $bankOptions)
                                ? $user->bank_name
                                : null;
                    @endphp

                    <div>
                        <span class="block text-sm font-bold text-secondary">
                            Nama bank <span class="text-primary">*</span>
                        </span>
                        <p class="mt-1 text-xs text-slate-500">Pilih bank penampung dana pencairan dari daftar berikut.</p>

                        <div class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-3" role="radiogroup" aria-label="Nama bank">
                            @foreach ($bankOptions as $bankValue => $bankOption)
                                <label class="cursor-pointer">
                                    <input type="radio" name="bank_name" value="{{ $bankValue }}"
                                        @checked($selectedBank === $bankValue) class="peer sr-only">
                                    <span @class([
                                        'flex h-full flex-col items-center justify-center gap-2 rounded-xl border bg-white px-3 py-4 text-center text-xs font-bold text-secondary transition hover:border-primary/50 peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary peer-checked:ring-2 peer-checked:ring-primary/20 peer-focus-visible:ring-2 peer-focus-visible:ring-primary/30',
                                        'border-red-400' => $bankNameError,
                                        'border-slate-200' => !$bankNameError,
                                    ])>
                                        <img src="{{ asset($bankOption['logo']) }}" alt="Logo {{ $bankOption['label'] }}"
                                            class="h-9 w-auto" loading="lazy">
                                        {{ $bankOption['label'] }}
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        @if ($legacyBankName)
                            <p class="mt-2 text-xs font-semibold text-amber-700">
                                Bank tersimpan saat ini ({{ $legacyBankName }}) belum tersedia pada daftar. Pilih salah
                                satu bank di atas untuk memperbarui rekening.
                            </p>
                        @endif

                        @if ($bankNameError)
                            <p class="mt-1.5 text-xs font-semibold text-red-600">{{ $bankNameError }}</p>
                        @endif
                    </div>

                    <x-form-text-input-dashboard id="bank_account_number" name="bank_account_number" label="Nomor rekening"
                        required inputmode="numeric" placeholder="Contoh: 1370012345678"
                        hint="Hanya angka, tanpa spasi atau tanda baca." :value="old('bank_account_number', $user->bank_account_number)" :error="$errors->first('bank_account_number')"
                        maxlength="34" />

                    <x-form-text-input-dashboard id="bank_account_holder_name" name="bank_account_holder_name"
                        label="Nama pemilik rekening" required placeholder="Nama sesuai buku tabungan" :value="old('bank_account_holder_name', $user->bank_account_holder_name)"
                        :error="$errors->first('bank_account_holder_name')" maxlength="150" />

                    <div
                        class="flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-slate-500">
                            Perubahan rekening hanya berlaku untuk pencairan berikutnya.
                        </p>
                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-white transition hover:bg-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30">
                            Simpan rekening
                        </button>
                    </div>
                </form>
            </x-card-dashboard>

            <x-card-dashboard title="Rekening tersimpan">
                @if ($hasDisbursementAccount)
                    @php
                        $savedBankOption = $bankOptions[$user->bank_name] ?? null;
                    @endphp
                    <dl class="space-y-4 text-sm">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bank</dt>
                            <dd class="mt-1 flex items-center gap-2 font-bold text-secondary">
                                @if ($savedBankOption)
                                    <img src="{{ asset($savedBankOption['logo']) }}" alt="" class="h-6 w-auto"
                                        loading="lazy">
                                @endif
                                {{ $user->bank_name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nomor rekening</dt>
                            <dd class="mt-1 font-heading text-lg font-bold tracking-wide text-secondary">
                                {{ $user->bank_account_number }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Atas nama</dt>
                            <dd class="mt-1 font-semibold text-secondary">{{ $user->bank_account_holder_name }}</dd>
                        </div>
                        @if ($user->bank_account_updated_at)
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Terakhir diperbarui
                                </dt>
                                <dd class="mt-1 text-slate-600">
                                    {{ $user->bank_account_updated_at->locale('id')->translatedFormat('d F Y, H:i') }} WIB
                                </dd>
                            </div>
                        @endif
                    </dl>
                @else
                    <div class="flex flex-col items-center py-6 text-center">
                        <span class="flex size-14 items-center justify-center rounded-full bg-amber-50 text-amber-600"
                            aria-hidden="true">
                            <svg class="size-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 7h18v12H3zM7 11h.01M17 15h.01M12 11a2 2 0 1 0 0 4 2 2 0 0 0 0-4ZM6 4h12" />
                            </svg>
                        </span>
                        <p class="mt-4 font-heading text-base font-bold text-secondary">Belum ada rekening tersimpan</p>
                        <p class="mt-1 max-w-xs text-sm leading-6 text-slate-500">
                            Isi formulir di samping untuk mengaktifkan pencairan dana escrow ke rekening Anda.
                        </p>
                    </div>
                @endif

                <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Catatan</p>
                    <p class="mt-1 text-xs leading-5 text-slate-600">
                        Transfer dana dilakukan manual oleh admin Mihom di luar aplikasi, lalu dikonfirmasi pada
                        dashboard transaksi &amp; escrow. Nominal pencairan dan status transfer dapat dipantau pada
                        tabel riwayat di bawah.
                    </p>
                </div>
            </x-card-dashboard>
        </div>

        <x-card-dashboard title="Riwayat pembayaran & pencairan"
            subtitle="Pantau status DP, pelunasan escrow, hingga dana masuk ke rekening Anda." :padding="false">
            @if ($transactions->isEmpty())
                <div class="px-6 py-14 text-center">
                    <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-primary/10 text-secondary"
                        aria-hidden="true">
                        <svg class="size-8" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 7h18v12H3zM7 11h.01M17 15h.01M12 11a2 2 0 1 0 0 4 2 2 0 0 0 0-4ZM6 4h12" />
                        </svg>
                    </div>
                    <h2 class="mt-5 font-heading text-lg font-bold text-secondary">Belum ada transaksi penjualan</h2>
                    <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500">
                        Riwayat pembayaran DP dan pelunasan escrow akan tampil di sini setelah calon pembeli
                        melanjutkan pembelian properti Anda.
                    </p>
                </div>
            @else
                @php
                    $stageToneClasses = [
                        'emerald' => 'border-emerald-200 bg-emerald-50',
                        'amber' => 'border-amber-200 bg-amber-50',
                        'rose' => 'border-rose-200 bg-rose-50',
                        'slate' => 'border-slate-200 bg-slate-50',
                    ];
                    $badgeToneClasses = [
                        'emerald' => 'border-emerald-200 bg-white text-emerald-700',
                        'amber' => 'border-amber-200 bg-white text-amber-800',
                        'rose' => 'border-rose-200 bg-white text-rose-700',
                        'slate' => 'border-slate-200 bg-white text-slate-600',
                    ];
                @endphp

                <ul class="divide-y divide-slate-100">
                    @foreach ($transactions as $transaction)
                        <li class="px-6 py-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="font-heading text-base font-bold text-secondary">
                                        {{ $transaction->property->title }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ collect([$transaction->property->city, $transaction->property->province])->filter()->implode(', ') }}
                                        · Pembeli {{ $transaction->buyer->name }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-center gap-3 sm:justify-end">
                                    <x-transaction-status-badge :status="$transaction->state" />
                                    <div class="text-right">
                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                            Nilai transaksi</p>
                                        <p class="font-heading text-base font-bold text-secondary">
                                            Rp
                                            {{ number_format((float) $transaction->total_property_amount, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                @foreach ($transaction->payment_stages as $stage)
                                    <div @class([
                                        'rounded-xl border px-4 py-3',
                                        $stageToneClasses[$stage['tone']] ?? $stageToneClasses['slate'],
                                    ])>
                                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">
                                            {{ $stage['label'] }}
                                        </p>
                                        <p class="mt-1 font-heading text-base font-bold text-secondary">
                                            Rp {{ number_format((float) $stage['amount'], 0, ',', '.') }}
                                        </p>
                                        <span @class([
                                            'mt-1.5 inline-flex rounded-full border px-2 py-0.5 text-[11px] font-bold',
                                            $badgeToneClasses[$stage['tone']] ?? $badgeToneClasses['slate'],
                                        ])>
                                            {{ $stage['status'] }}
                                        </span>
                                        <p class="mt-2 text-[11px] leading-4 text-slate-600">
                                            {{ $stage['description'] }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-slate-500">
                                <span>Referensi transaksi: {{ $transaction->uuid }}</span>
                                @if ($transaction->property->uuid)
                                    <a href="{{ route('seller.my-properties.show', ['property' => $transaction->property->uuid]) }}"
                                        class="font-semibold text-primary transition hover:text-primary/80">
                                        Lihat detail properti
                                    </a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>

                <div class="border-t border-slate-100 px-6 py-4">
                    <x-table.pagination :paginator="$transactions" />
                </div>
            @endif
        </x-card-dashboard>
    </div>
@endsection
