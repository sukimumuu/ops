<aside x-cloak
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-50 w-64 flex flex-col
        transform bg-white text-black transition-transform duration-300 ease-in-out
        lg:translate-x-0 lg:hidden lg:bg-white lg:text-slate-700">

    <!-- Brand -->
    <div class="flex items-center justify-between h-16 px-6 border-b border-white/10 lg:border-slate-200">
        <a href="" class="flex items-center gap-2.5">
                <!-- Replace with your logo -->
            <img id="logo-black" src="{{ asset('assets/png/gry trnsprn.png') }}" alt="Mihom" class="h-8">
            <span class="font-heading text-xl font-bold text-white tracking-tight lg:text-black">
                Mihom
            </span>
        </a>

        <!-- Close button (mobile only) -->
        <button @click="sidebarOpen = false" class="lg:hidden text-white/70 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-1">

        <p class="px-3 pb-2 text-[11px] font-bold uppercase trackeeeing-widest text-black/60 lg:text-black/40">
            Main Menu
        </p>

        <!-- Active link: bg-primary/10 text-primary -->
        @role('Superadmin')
        <a href=""
           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold
                  {{ request()->routeIs('dashboard')
                     ? 'bg-primary text-white shadow-lg shadow-primary/25'
                     : 'text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 12l9-8 9 8M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10" />
            </svg>
            Dashboard Antrian
        </a>

        <a href="#" class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Manajemen Pengguna
        </a>

        <a href="#" class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            Master Data Properti
        </a>

        <a href="#" class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 3v18h18M18 17V9M13 17V5M8 17v-3" />
            </svg>
            Transaksi & Escrow
        </a>

        <a href="#" class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Sistem & Konfigurasi
        </a>
        <a href="#" class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Audit Log
        </a>
        @endrole

        {{-- PPAT Menu --}}
        @role('PPAT')
        <a href=""
           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold
                  {{ request()->routeIs('dashboard')
                     ? 'bg-primary text-white shadow-lg shadow-primary/25'
                     : 'text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 12l9-8 9 8M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10" />
            </svg>
            Dashboard Antrian
        </a>

        <a href="#" class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Pengecekan BPN
        </a>

        <a href="#" class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            Validasi Pajak
        </a>

        <a href="#" class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 3v18h18M18 17V9M13 17V5M8 17v-3" />
            </svg>
            Jadwal Penandatanganan AJB
        </a>

        <a href="#" class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Manajemen Akta
        </a>
        <a href="#" class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Resi Balik Nama
        </a>
        @endrole

        {{-- Buyer Menu --}}
        @role('Buyer')
        <a href=""
           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold
                  {{ request()->routeIs('dashboard')
                     ? 'bg-primary text-white shadow-lg shadow-primary/25'
                     : 'text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 12l9-8 9 8M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10" />
            </svg>
            Dashboard
        </a>

        <a href="#" class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Properti Tersimpan
        </a>

        <a href="#" class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            Jadwal Survei Saya
        </a>

        <a href="#" class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 3v18h18M18 17V9M13 17V5M8 17v-3" />
            </svg>
            Transaksi & Escrow
        </a>

        <a href="#" class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Profil & Verifikasi (KYC)
        </a>
        @endrole

        {{-- Seller Menu --}}
        @role('Seller')
        <a href=""
           class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold
                  {{ request()->routeIs('dashboard')
                     ? 'bg-primary text-white shadow-lg shadow-primary/25'
                     : 'text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 12l9-8 9 8M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10" />
            </svg>
            Dashboard
        </a>

        <a href="#" class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Properti Saya
        </a>

        <a href="#" class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            Permintaan Survei Masuk
        </a>

        <a href="#" class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 3v18h18M18 17V9M13 17V5M8 17v-3" />
            </svg>
            Transaksi Penjualan
        </a>

        <a href="#" class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Rekening Pencairan
        </a>
        <a href="#" class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-black/80 hover:text-black hover:bg-black/10 lg:text-black/70 lg:hover:text-black lg:hover:bg-black/5">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Profil & Verifikasi (KYC)
        </a>
        @endrole
    </nav>
</aside>