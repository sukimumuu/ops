@props(['defaultPrice' => 2000000000, 'defaultNpoptkp' => 80000000])

{{-- ======================= HERO SECTION ======================= --}}
<section class="relative flex min-h-[420px] items-center justify-center overflow-hidden bg-secondary text-center" id="pajak">
    {{-- Background pattern overlay --}}
    <div class="absolute inset-0 opacity-[0.04]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;1&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

    <div class="relative z-10 mx-auto max-w-3xl px-4 py-28 sm:px-6">
        {{-- Badge --}}
        <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-primary/30 bg-primary/10 px-5 py-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-primary" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 2a2 2 0 00-2 2v14l3.5-2 3.5 2 3.5-2 3.5 2V4a2 2 0 00-2-2H5zm2.5 3a1.5 1.5 0 100 3 1.5 1.5 0 000-3zm6.207.293a1 1 0 00-1.414 0l-6 6a1 1 0 101.414 1.414l6-6a1 1 0 000-1.414zM12.5 10a1.5 1.5 0 100 3 1.5 1.5 0 000-3z" clip-rule="evenodd" />
            </svg>
            <span class="text-xs font-bold uppercase tracking-wider text-primary">Edisi Regulasi Terbaru (UU HKPD)</span>
        </div>

        <h1 class="font-display text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-5xl lg:text-[56px]">
            Kalkulator Pajak Properti
        </h1>
        <p class="mx-auto mt-5 max-w-2xl text-base leading-7 text-white/65 sm:text-lg">
            Hitung estimasi PPh Final Penjual dan BPHTB Pembeli secara transparan, akurat, dan sesuai dengan ketentuan perpajakan properti di Indonesia.
        </p>
    </div>

    {{-- Bottom curve --}}
    <div class="absolute -bottom-1 left-0 right-0">
        <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
            <path d="M0 60V30C240 0 480 0 720 30C960 60 1200 60 1440 30V60H0Z" fill="#f1f5f9"/>
        </svg>
    </div>
</section>

{{-- ======================= CALCULATOR SECTION ======================= --}}
<section class="bg-slate-100 px-4 pb-20 pt-10 sm:px-6">
    <div class="mx-auto max-w-6xl">
        <div class="grid gap-8 lg:grid-cols-2">

            {{-- ========== LEFT: Input Form ========== --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-7 shadow-sm sm:p-9">
                <h2 class="font-display text-xl font-extrabold text-secondary sm:text-2xl">Hitung Pajak Properti Anda</h2>
                <p class="mt-2 text-sm leading-6 text-gray-400">Masukkan nilai transaksi untuk memulai simulasi perhitungan.</p>

                <div class="mt-8 space-y-6">
                    {{-- Harga Properti --}}
                    <div>
                        <label for="calc_property_price" class="mb-2 block text-sm font-bold text-secondary">
                            Harga Properti / Nilai Transaksi (Rp)
                        </label>
                        <div class="relative">
                            <span class="absolute left-0 top-0 flex h-full items-center pl-4 text-sm font-bold text-gray-400">Rp</span>
                            <input
                                type="text"
                                id="calc_property_price"
                                class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 py-3.5 pl-12 pr-4 text-sm font-semibold text-secondary outline-none transition-all focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10"
                                placeholder="Masukkan harga properti"
                                value="{{ number_format($defaultPrice, 0, ',', '.') }}"
                            >
                        </div>
                        <p class="mt-2 text-xs leading-5 text-gray-400">
                            Gunakan harga riil kesepakatan atau NJOP (ambil mana yang lebih tinggi).
                        </p>
                    </div>

                    {{-- NPOPTKP --}}
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label for="calc_npoptkp" class="text-sm font-bold text-secondary">
                                NPOPTKP (Rp)
                            </label>
                            <button type="button" id="btn-lihat-standar" class="text-xs font-bold text-primary transition hover:underline">
                                Lihat Standar Daerah
                            </button>
                        </div>
                        <div class="relative">
                            <span class="absolute left-0 top-0 flex h-full items-center pl-4 text-sm font-bold text-gray-400">Rp</span>
                            <input
                                type="text"
                                id="calc_npoptkp"
                                class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 py-3.5 pl-12 pr-4 text-sm font-semibold text-secondary outline-none transition-all focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10"
                                placeholder="Masukkan NPOPTKP"
                                value="{{ number_format($defaultNpoptkp, 0, ',', '.') }}"
                            >
                        </div>
                        <p class="mt-2 text-xs leading-5 text-gray-400">
                            Nilai Perolehan Objek Pajak Tidak Kena Pajak. Besarnya bisa berbeda tiap daerah (umumnya Rp 60jt - Rp 80jt).
                        </p>
                    </div>

                    {{-- Kategori Wajib Pajak --}}
                    <div>
                        <label class="mb-3 block text-sm font-bold text-secondary">Kategori Wajib Pajak</label>
                        <div class="flex flex-wrap gap-3">
                            <label class="tax-radio-label group cursor-pointer" id="radio-keduanya">
                                <input type="radio" name="calc_tax_category" value="keduanya" class="peer sr-only" checked>
                                <span class="inline-flex items-center gap-2 rounded-full border-2 border-gray-200 bg-gray-50 px-5 py-2.5 text-sm font-semibold text-gray-500 transition-all peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Keduanya
                                </span>
                            </label>
                            <label class="tax-radio-label group cursor-pointer" id="radio-penjual">
                                <input type="radio" name="calc_tax_category" value="penjual" class="peer sr-only">
                                <span class="inline-flex items-center gap-2 rounded-full border-2 border-gray-200 bg-gray-50 px-5 py-2.5 text-sm font-semibold text-gray-500 transition-all peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white">
                                    Penjual Saja
                                </span>
                            </label>
                            <label class="tax-radio-label group cursor-pointer" id="radio-pembeli">
                                <input type="radio" name="calc_tax_category" value="pembeli" class="peer sr-only">
                                <span class="inline-flex items-center gap-2 rounded-full border-2 border-gray-200 bg-gray-50 px-5 py-2.5 text-sm font-semibold text-gray-500 transition-all peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white">
                                    Pembeli Saja
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- CTA Button --}}
                    <button
                        type="button"
                        id="btn-hitung-pajak"
                        class="group flex w-full items-center justify-center gap-3 rounded-xl bg-primary px-6 py-4 text-base font-extrabold text-white shadow-lg shadow-primary/25 transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#df3f05] hover:shadow-xl hover:shadow-primary/30 active:translate-y-0 active:scale-[0.98]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:scale-110" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V8z" clip-rule="evenodd" />
                        </svg>
                        Hitung Pajak Sekarang
                    </button>
                </div>
            </div>

            {{-- ========== RIGHT: Results ========== --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-7 shadow-sm sm:p-9">
                <div class="mb-6 flex items-start justify-between">
                    <div>
                        <h2 class="font-display text-xl font-extrabold text-secondary sm:text-2xl">Hasil Estimasi Pajak</h2>
                        <p class="mt-2 text-sm leading-6 text-gray-400">Berikut rincian beban pajak yang harus dibayarkan masing-masing pihak.</p>
                    </div>
                    <span id="badge-selesai" class="hidden items-center gap-1.5 rounded-full bg-green-50 px-3 py-1.5 text-xs font-bold text-green-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Selesai Dihitung
                    </span>
                </div>

                {{-- PPh & BPHTB Cards --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    {{-- PPh Card --}}
                    <div id="card-pph" class="rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/50 p-5 transition-all">
                        <div class="mb-1 flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">PPh Final Penjual (2,5%)</span>
                        </div>
                        <p id="result_pph" class="mt-2 font-display text-2xl font-extrabold text-secondary sm:text-[26px]">Rp 0</p>
                        <p class="mt-2 text-xs leading-5 text-gray-400">Ditanggung sepenuhnya oleh Penjual sebelum AJB.</p>
                    </div>

                    {{-- BPHTB Card --}}
                    <div id="card-bphtb" class="rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/50 p-5 transition-all">
                        <div class="mb-1 flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">BPHTB Pembeli (5%)</span>
                        </div>
                        <p id="result_bphtb" class="mt-2 font-display text-2xl font-extrabold text-primary sm:text-[26px]">Rp 0</p>
                        <p class="mt-2 text-xs leading-5 text-gray-400">Ditanggung Pembeli (Sudah dikurangi NPOPTKP).</p>
                    </div>
                </div>

                {{-- Rincian Perhitungan --}}
                <div class="mt-6 rounded-xl border border-gray-100 bg-gray-50/50">
                    <div class="border-b border-gray-100 px-5 py-3.5">
                        <h3 class="text-sm font-extrabold text-secondary">Rincian Perhitungan</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        {{-- PPh Detail --}}
                        <div id="detail-pph" class="flex items-center justify-between px-5 py-4">
                            <div>
                                <p class="text-sm font-bold text-secondary">PPh Final Penjual</p>
                                <p id="detail_pph_formula" class="mt-0.5 text-xs text-gray-400">Tarif 2,5% dari DPP Rp 0</p>
                            </div>
                            <p id="detail_pph_value" class="text-sm font-extrabold text-secondary">Rp 0</p>
                        </div>

                        {{-- BPHTB Detail --}}
                        <div id="detail-bphtb" class="flex items-center justify-between px-5 py-4">
                            <div>
                                <p class="text-sm font-bold text-secondary">BPHTB Pembeli</p>
                                <p id="detail_bphtb_formula" class="mt-0.5 text-xs text-gray-400">Tarif 5% dari DPP Rp 0</p>
                            </div>
                            <p id="detail_bphtb_value" class="text-sm font-extrabold text-primary">Rp 0</p>
                        </div>

                        {{-- Total --}}
                        <div class="flex items-center justify-between bg-gray-50 px-5 py-4">
                            <p class="text-sm font-extrabold text-secondary">Total Estimasi Pajak Transaksi</p>
                            <p id="detail_total_value" class="text-base font-extrabold text-primary sm:text-lg">Rp 0</p>
                        </div>
                    </div>
                </div>

                {{-- BPHTB Note --}}
                <div class="mt-5 flex gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-400 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <p class="text-xs leading-5 text-amber-800">
                        Berdasarkan UU HKPD terbaru, tarif BPHTB maksimal ditetapkan sebesar 5%. Konsultasikan ke Notaris/PPAT setempat untuk validasi lebih lanjut.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ======================= EDUCATIONAL SECTION ======================= --}}
<section class="bg-white px-4 py-20 sm:px-6">
    <div class="mx-auto max-w-6xl">
        <div class="mb-10 text-center">
            <h2 class="font-display text-2xl font-extrabold text-secondary sm:text-3xl">Pahami Pajak Transaksi Properti</h2>
            <p class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-gray-400">
                Setiap transaksi jual beli properti di Indonesia melibatkan kewajiban perpajakan yang sah dari sisi penjual maupun pembeli.
            </p>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            {{-- PPh Explanation Card --}}
            <div class="group rounded-2xl border-2 border-gray-100 bg-white p-7 transition-all hover:border-primary/20 hover:shadow-lg hover:shadow-primary/5 sm:p-8">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-secondary/5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-secondary" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h3 class="text-base font-extrabold text-secondary sm:text-lg">Apa itu PPh Final Penjual?</h3>
                </div>
                <p class="text-sm leading-7 text-gray-500">
                    Pajak Penghasilan (PPh) Final sebesar 2,5% dikenakan kepada penjual atas penghasilan yang diterima dari pengalihan hak atas tanah dan/atau bangunan. Dasar pengenaan pajaknya adalah jumlah bruto nilai pengalihan (harga transaksi yang disepakati atau NJOP, mana yang lebih tinggi).
                </p>
                <div class="mt-5 flex items-center gap-2 text-xs font-bold text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                    </svg>
                    Dasar Hukum: PP No. 34 Tahun 2016
                </div>
            </div>

            {{-- BPHTB Explanation Card --}}
            <div class="group rounded-2xl border-2 border-gray-100 bg-white p-7 transition-all hover:border-primary/20 hover:shadow-lg hover:shadow-primary/5 sm:p-8">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-secondary/5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-secondary" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h3 class="text-base font-extrabold text-secondary sm:text-lg">Apa itu BPHTB Pembeli?</h3>
                </div>
                <p class="text-sm leading-7 text-gray-500">
                    Bea Perolehan Hak atas Tanah dan Bangunan (BPHTB) adalah pungutan atas perolehan hak atas tanah dan/atau bangunan yang ditanggung oleh pembeli sebesar 5%. Nilai pajak dihitung dari nilai transaksi dikurangi NPOPTKP daerah setempat, kemudian dikalikan tarif 5%.
                </p>
                <div class="mt-5 flex items-center gap-2 text-xs font-bold text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                    </svg>
                    Dasar Hukum: UU HKPD No. 1 Tahun 2022
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ======================= DISCLAIMER SECTION ======================= --}}
<section class="bg-slate-100 px-4 py-12 sm:px-6">
    <div class="mx-auto max-w-6xl">
        <div class="rounded-2xl border border-gray-200 bg-white p-7 sm:p-8">
            <h3 class="mb-3 text-sm font-extrabold text-secondary">
                <span class="mr-2 inline-flex h-5 w-5 items-center justify-center rounded bg-secondary text-[10px] text-white">!</span>
                Disclaimer / Catatan Penting:
            </h3>
            <p class="text-xs leading-6 text-gray-500">
                Hasil perhitungan kalkulator ini bersifat estimasi dan simulasi untuk keperluan perencanaan keuangan Anda. Nilai pajak riil yang sah secara hukum dapat berbeda tergantung pada validitas Nilai Jual Objek Pajak (NJOP) tahun berjalan oleh Kantor Pertanahan (BPN) dan Kantor Pelayanan Pajak (KPP) setempat, serta kebijakan besaran NPOPTKP spesifik di wilayah administratif kabupaten/kota properti tersebut berada.
            </p>
        </div>
    </div>
</section>

{{-- ======================= NPOPTKP MODAL ======================= --}}
<div id="modal-npoptkp" class="fixed inset-0 z-[100] flex hidden items-center justify-center bg-secondary/60 backdrop-blur-sm" style="display:none;">
    <div class="mx-4 w-full max-w-lg animate-[taxModalIn_0.3s_ease] rounded-2xl bg-white p-7 shadow-2xl sm:p-8">
        <div class="mb-5 flex items-center justify-between">
            <h3 class="font-display text-lg font-extrabold text-secondary">Standar NPOPTKP Daerah</h3>
            <button type="button" id="btn-close-modal" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        <div class="overflow-hidden rounded-xl border border-gray-200">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-gray-500">Daerah</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-gray-500">NPOPTKP</th>
                        <th class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-gray-500"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ([
                        ['DKI Jakarta', 80000000],
                        ['Kota Bandung', 75000000],
                        ['Kota Surabaya', 75000000],
                        ['Kota Semarang', 60000000],
                        ['Kota Yogyakarta', 60000000],
                        ['Kota Medan', 60000000],
                        ['Kota Makassar', 60000000],
                        ['Kabupaten Bogor', 60000000],
                        ['Kota Denpasar', 80000000],
                    ] as [$daerah, $nilai])
                        <tr class="npoptkp-row cursor-pointer transition hover:bg-primary/5" data-npoptkp="{{ $nilai }}">
                            <td class="px-4 py-3 font-semibold text-secondary">{{ $daerah }}</td>
                            <td class="px-4 py-3 text-gray-500">Rp {{ number_format($nilai, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-xs font-bold text-primary opacity-0 transition group-hover:opacity-100">Gunakan →</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="mt-4 text-xs text-gray-400">Klik baris untuk menggunakan nilai NPOPTKP tersebut.</p>
    </div>
</div>

{{-- ======================= JAVASCRIPT ======================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ---- DOM Elements ----
    const priceInput = document.getElementById('calc_property_price');
    const npoptkpInput = document.getElementById('calc_npoptkp');
    const btnHitung = document.getElementById('btn-hitung-pajak');
    const badgeSelesai = document.getElementById('badge-selesai');

    // Result elements
    const resultPph = document.getElementById('result_pph');
    const resultBphtb = document.getElementById('result_bphtb');
    const detailPphFormula = document.getElementById('detail_pph_formula');
    const detailPphValue = document.getElementById('detail_pph_value');
    const detailBphtbFormula = document.getElementById('detail_bphtb_formula');
    const detailBphtbValue = document.getElementById('detail_bphtb_value');
    const detailTotalValue = document.getElementById('detail_total_value');

    // Cards
    const cardPph = document.getElementById('card-pph');
    const cardBphtb = document.getElementById('card-bphtb');
    const detailPph = document.getElementById('detail-pph');
    const detailBphtb = document.getElementById('detail-bphtb');

    // Modal
    const modal = document.getElementById('modal-npoptkp');
    const btnLihatStandar = document.getElementById('btn-lihat-standar');
    const btnCloseModal = document.getElementById('btn-close-modal');
    const npoptkpRows = document.querySelectorAll('.npoptkp-row');

    // ---- Constants (mirroring TaxCalculatorService) ----
    const PPH_RATE = {{ App\Services\TaxCalculatorService::PPH_RATE }};
    const BPHTB_RATE = {{ App\Services\TaxCalculatorService::BPHTB_RATE }};

    // ---- Utility: Format Rupiah ----
    const formatRupiah = (number) => {
        return 'Rp ' + new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(number);
    };

    // ---- Utility: Parse number from formatted string ----
    const parseNumber = (str) => parseInt(str.replace(/\D/g, ''), 10) || 0;

    // ---- Format input with thousand separator ----
    const formatInput = (input) => {
        let value = input.value.replace(/\D/g, '');
        if (value !== '') {
            input.value = parseInt(value, 10).toLocaleString('id-ID');
        } else {
            input.value = '';
        }
    };

    // ---- Get selected category ----
    const getCategory = () => {
        const checked = document.querySelector('input[name="calc_tax_category"]:checked');
        return checked ? checked.value : 'keduanya';
    };

    // ---- Main calculation ----
    const calculateTaxes = () => {
        const price = parseNumber(priceInput.value);
        const npoptkp = parseNumber(npoptkpInput.value);
        const category = getCategory();

        // PPh = price * 2.5%
        const pph = price * PPH_RATE;

        // BPHTB = max(0, price - npoptkp) * 5%
        const dppBphtb = Math.max(0, price - npoptkp);
        const bphtb = dppBphtb * BPHTB_RATE;

        // Show/hide based on category
        const showPph = category === 'keduanya' || category === 'penjual';
        const showBphtb = category === 'keduanya' || category === 'pembeli';

        // Update visibility
        cardPph.style.display = showPph ? '' : 'none';
        cardBphtb.style.display = showBphtb ? '' : 'none';
        detailPph.style.display = showPph ? '' : 'none';
        detailBphtb.style.display = showBphtb ? '' : 'none';

        // Animate cards
        if (showPph) {
            cardPph.classList.remove('border-dashed', 'border-gray-200', 'bg-gray-50/50');
            cardPph.classList.add('border-solid', 'border-gray-200', 'bg-white', 'shadow-sm');
        }
        if (showBphtb) {
            cardBphtb.classList.remove('border-dashed', 'border-gray-200', 'bg-gray-50/50');
            cardBphtb.classList.add('border-solid', 'border-gray-200', 'bg-white', 'shadow-sm');
        }

        // Update result values
        resultPph.textContent = formatRupiah(pph);
        resultBphtb.textContent = formatRupiah(bphtb);

        // Update detail formulas
        detailPphFormula.textContent = `Tarif 2,5% dari DPP ${formatRupiah(price)}`;
        detailBphtbFormula.textContent = `Tarif 5% dari DPP ${formatRupiah(dppBphtb)}`;

        // Update detail values
        detailPphValue.textContent = formatRupiah(pph);
        detailBphtbValue.textContent = formatRupiah(bphtb);

        // Total
        let total = 0;
        if (showPph) total += pph;
        if (showBphtb) total += bphtb;
        detailTotalValue.textContent = formatRupiah(total);

        // Show badge
        badgeSelesai.style.display = 'inline-flex';
        badgeSelesai.classList.remove('hidden');
    };

    // ---- Input formatting event listeners ----
    priceInput.addEventListener('input', function () { formatInput(this); });
    npoptkpInput.addEventListener('input', function () { formatInput(this); });

    // ---- Calculate on button click ----
    btnHitung.addEventListener('click', function () {
        // Add a pulse animation to the button
        this.style.transform = 'scale(0.96)';
        setTimeout(() => { this.style.transform = ''; }, 150);
        calculateTaxes();
    });

    // ---- Category radio change ----
    document.querySelectorAll('input[name="calc_tax_category"]').forEach(radio => {
        radio.addEventListener('change', () => {
            if (badgeSelesai.style.display !== 'none') {
                calculateTaxes();
            }
        });
    });

    // ---- NPOPTKP Modal ----
    btnLihatStandar.addEventListener('click', () => {
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    });

    btnCloseModal.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    npoptkpRows.forEach(row => {
        row.addEventListener('click', () => {
            const val = parseInt(row.dataset.npoptkp, 10);
            npoptkpInput.value = val.toLocaleString('id-ID');
            modal.style.display = 'none';
            if (badgeSelesai.style.display !== 'none') {
                calculateTaxes();
            }
        });
    });

    // ---- Initialize on page load ----
    if (priceInput.value) {
        calculateTaxes();
    }
});
</script>

<style>
    @keyframes taxModalIn {
        from { opacity: 0; transform: scale(0.95) translateY(10px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
</style>