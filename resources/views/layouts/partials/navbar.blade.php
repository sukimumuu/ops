{{-- Navbar Top --}}
<header
    class="sticky top-0 z-30 flex items-center gap-4 h-16 sm:px-6
                    bg-white border-b border-slate-200 shadow-sm">
    <nav class="w-full lg:w-4/5 lg:mx-auto top-0 z-30 flex items-center gap-4 h-16 px-4 sm:px-6">
        <!-- Mobile menu button -->
        <button @click="sidebarOpen = true"
            class="lg:hidden p-2 rounded-lg text-slate-500 hover:text-secondary hover:bg-slate-100">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Page title (optional yield) -->
        <a href="{{ route('home') }}">
            <img id="logo-black" src="{{ asset('assets/png/gry trnsprn.png') }}" alt="Mihom"
                class="hidden lg:block h-8">
        </a>


        <div class="flex-1"></div>
        <nav class="hidden lg:flex items-center text-black/80 gap-4 h-16">
            @role('Superadmin')
                <li
                    class="flex items-center list-none text-sm font-semibold h-16 {{ request()->routeIs('dashboard')
                        ? 'border-b-6 border-b-primary text-primary'
                        : 'hover:border-b-2 hover:border-b-primary hover:text-primary/80' }}">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                </li>
                <li
                    class="flex items-center list-none text-sm font-semibold h-16 {{ request()->routeIs('superadmin.user-management')
                        ? 'border-b-6 border-b-primary text-primary'
                        : 'hover:border-b-2 hover:border-b-primary hover:text-primary/80' }}">
                    <a href="{{ route('superadmin.user-management') }}">Manajemen Pengguna</a>
                </li>
                <li
                    class="flex items-center list-none text-sm font-semibold h-16 {{ request()->routeIs('superadmin.master-data-property')
                        ? 'border-b-6 border-b-primary text-primary'
                        : 'hover:border-b-2 hover:border-b-primary hover:text-primary/80' }}">
                    <a href="{{ route('superadmin.master-data-property') }}">Master Data Properti</a>
                </li>
                <li
                    class="flex items-center list-none text-sm font-semibold h-16 {{ request()->routeIs('superadmin.transaction-and-escrow')
                        ? 'border-b-6 border-b-primary text-primary'
                        : 'hover:border-b-2 hover:border-b-primary hover:text-primary/80' }}">
                    <a href="{{ route('superadmin.transaction-and-escrow') }}">Transaksi & Escrow</a>
                </li>
                <li
                    class="flex items-center list-none text-sm font-semibold h-16 {{ request()->routeIs('superadmin.configuration-system')
                        ? 'border-b-6 border-b-primary text-primary'
                        : 'hover:border-b-2 hover:border-b-primary hover:text-primary/80' }}">
                    <a href="{{ route('superadmin.configuration-system') }}">Sistem & Konfigurasi</a>
                </li>
                <li
                    class="flex items-center list-none text-sm font-semibold h-16 {{ request()->routeIs('superadmin.audit-log')
                        ? 'border-b-6 border-b-primary text-primary'
                        : 'hover:border-b-2 hover:border-b-primary hover:text-primary/80' }}">
                    <a href="{{ route('superadmin.audit-log') }}">Audit Log</a>
                </li>
            @endrole
            @role('PPAT')
                <li
                    class="flex items-center list-none text-sm font-semibold h-16 {{ request()->routeIs('dashboard')
                        ? 'border-b-6 border-b-primary text-primary'
                        : 'hover:border-b-2 hover:border-b-primary hover:text-primary/80' }}">
                    <a href="">Dashboard Antrian</a>
                </li>
                <li class="flex items-center list-none text-sm font-semibold h-16">
                    <a href="">Pengecekan BPN</a>
                </li>
                <li class="flex items-center list-none text-sm font-semibold h-16">
                    <a href="">Validasi Pajak</a>
                </li>
                <li class="flex items-center list-none text-sm font-semibold createh-16">
                    <a href="">Jadwal Penandatanganan AJB</a>
                </li>
                <li class="flex items-center list-none text-sm font-semibold h-16">
                    <a href="">Manajemen Akta</a>
                </li>
                <li class="flex items-center list-none text-sm font-semibold h-16">
                    <a href="">Resi Balik Nama</a>
                </li>
            @endrole
            @role('Buyer')
                <li
                    class="flex items-center list-none text-sm font-semibold h-16 {{ request()->routeIs('dashboard')
                        ? 'border-b-6 border-b-primary text-primary'
                        : 'hover:border-b-2 hover:border-b-primary hover:text-primary/60' }}">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                </li>
                <li
                    class="flex items-center list-none text-sm font-semibold h-16 {{ request()->routeIs('buyer.saved-properties')
                        ? 'border-b-6 border-b-primary text-primary'
                        : 'hover:border-b-2 hover:border-b-primary hover:text-primary/60' }}">
                    <a href="{{ route('buyer.saved-properties') }}">Properti Tersimpan</a>
                </li>
                <li
                    class="flex items-center list-none text-sm font-semibold h-16 {{ request()->routeIs('buyer.survey-schedule')
                        ? 'border-b-6 border-b-primary text-primary'
                        : 'hover:border-b-2 hover:border-b-primary hover:text-primary/60' }}">
                    <a href="{{ route('buyer.survey-schedule') }}">Jadwal Survei Saya</a>
                </li>
                <li
                    class="flex items-center list-none text-sm font-semibold h-16 {{ request()->routeIs('buyer.transaction-escrow')
                        ? 'border-b-6 border-b-primary text-primary'
                        : 'hover:border-b-2 hover:border-b-primary hover:text-primary/60' }}">
                    <a href="{{ route('buyer.transaction-escrow') }}">Transaksi & Escrow</a>
                </li>
            @endrole
            @role('Seller')
                <li
                    class="flex items-center list-none text-sm font-semibold h-16 {{ request()->routeIs('dashboard')
                        ? 'border-b-6 border-b-primary text-primary'
                        : 'hover:border-b-2 hover:border-b-primary hover:text-primary/80' }}">
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                </li>
                <li
                    class="flex items-center list-none text-sm font-semibold h-16 {{ request()->routeIs('seller.my-properties') || request()->routeIs('listing.create')
                        ? 'border-b-6 border-b-primary text-primary'
                        : 'hover:border-b-2 hover:border-b-primary hover:text-primary/80' }}">
                    <a href="{{ route('seller.my-properties') }}">Properti Saya</a>
                </li>
                <li
                    class="flex items-center list-none text-sm font-semibold h-16 {{ request()->routeIs('seller.request-survey')
                        ? 'border-b-6 border-b-primary text-primary'
                        : 'hover:border-b-2 hover:border-b-primary hover:text-primary/80' }}">
                    <a href="{{ route('seller.request-survey') }}">Permintaan Survei Masuk</a>
                </li>
                <li
                    class="flex items-center list-none text-sm font-semibold h-16 {{ request()->routeIs('seller.selling-transaction')
                        ? 'border-b-6 border-b-primary text-primary'
                        : 'hover:border-b-2 hover:border-b-primary hover:text-primary/80' }}">
                    <a href="{{ route('seller.selling-transaction') }}">Transaksi Penjualan</a>
                </li>
                <li
                    class="flex items-center list-none text-sm font-semibold h-16 {{ request()->routeIs('seller.disbursement-account')
                        ? 'border-b-6 border-b-primary text-primary'
                        : 'hover:border-b-2 hover:border-b-primary hover:text-primary/80' }}">
                    <a href="{{ route('seller.disbursement-account') }}">Rekening Pencairan</a>
                </li>
            @endrole
        </nav>
        <div class="flex-1"></div>
        @role('Buyer')
            <x-button-dashboard type="button" variant="outline" class="hover:bg-primary hover:text-white">
                <a href="">
                    Pasang Iklan Properti
                </a>
                </x-button>
            @endrole
            <!-- Notifications Dropdown (Modifikasi) -->
            <div x-data="{ notifOpen: false }" class="relative hidden lg:block">
                <button @click="notifOpen = !notifOpen"
                    class="relative p-2 rounded-lg text-slate-500 hover:text-secondary hover:bg-slate-100 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-primary"></span>
                </button>

                <!-- Pop-up Notifikasi -->
                <div x-show="notifOpen" x-cloak @click.outside="notifOpen = false"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-3 w-[380px] sm:w-[420px] bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-50">

                    <!-- Header Pop-up -->
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <h3 class="font-bold text-slate-800 text-lg">Notifikasi</h3>
                            <span class="px-2.5 py-0.5 text-xs font-semibold text-primary bg-orange-50 rounded-full">3
                                baru</span>
                        </div>
                        <button class="text-sm font-semibold text-primary hover:underline">
                            Tandai dibaca
                        </button>
                    </div>

                    <!-- List Notifikasi -->
                    <div class="max-h-[420px] overflow-y-auto divide-y divide-slate-50">
                        <!-- Item 1 (Belum Dibaca) -->
                        <a href="#"
                            class="flex items-start gap-3.5 p-4 bg-orange-50/20 hover:bg-slate-50 transition relative">
                            <div class="p-2.5 bg-emerald-100/60 text-emerald-600 rounded-xl shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V8a2 2 0 00-2-2H6a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0 pr-2">
                                <p class="text-sm font-bold text-slate-800 truncate">Dana escrow telah diterima</p>
                                <p class="text-xs text-slate-400 mt-0.5 line-clamp-2">Pembayaran Rp 6,2 M untuk TX-2081
                                    berhasil masuk.</p>
                                <span class="text-xs font-semibold text-primary mt-1.5 inline-block">2 menit
                                    lalu</span>
                            </div>
                            <span class="w-2.5 h-2.5 bg-primary rounded-full shrink-0 mt-1.5"></span>
                        </a>

                        <!-- Item 2 (Belum Dibaca) -->
                        <a href="#"
                            class="flex items-start gap-3.5 p-4 bg-orange-50/20 hover:bg-slate-50 transition relative">
                            <div class="p-2.5 bg-sky-100/60 text-sky-500 rounded-xl shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0 pr-2">
                                <p class="text-sm font-bold text-slate-800 truncate">Verifikasi KYC menunggu tinjauan
                                </p>
                                <p class="text-xs text-slate-400 mt-0.5 line-clamp-2">Rian Hidayat mengunggah dokumen
                                    identitas baru.</p>
                                <span class="text-xs font-semibold text-primary mt-1.5 inline-block">18 menit
                                    lalu</span>
                            </div>
                            <span class="w-2.5 h-2.5 bg-primary rounded-full shrink-0 mt-1.5"></span>
                        </a>

                        <!-- Item 3 (Belum Dibaca) -->
                        <a href="#"
                            class="flex items-start gap-3.5 p-4 bg-orange-50/20 hover:bg-slate-50 transition relative">
                            <div class="p-2.5 bg-orange-100/60 text-primary rounded-xl shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0 pr-2">
                                <p class="text-sm font-bold text-slate-800 truncate">Listing baru perlu dimoderasi</p>
                                <p class="text-xs text-slate-400 mt-0.5 line-clamp-2">PRP-219 • Villa Canggu Berawa
                                    telah diajukan.</p>
                                <span class="text-xs font-semibold text-primary mt-1.5 inline-block">42 menit
                                    lalu</span>
                            </div>
                            <span class="w-2.5 h-2.5 bg-primary rounded-full shrink-0 mt-1.5"></span>
                        </a>

                        <!-- Item 4 (Sudah Dibaca) -->
                        <a href="#" class="flex items-start gap-3.5 p-4 hover:bg-slate-50 transition">
                            <div class="p-2.5 bg-emerald-100/60 text-emerald-600 rounded-xl shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-800 truncate">Dana escrow berhasil dirilis</p>
                                <p class="text-xs text-slate-400 mt-0.5 line-clamp-2">Dana transaksi TX-2080 diteruskan
                                    ke penjual.</p>
                                <span class="text-xs text-slate-400 mt-1.5 inline-block">2 jam lalu</span>
                            </div>
                        </a>

                        <!-- Item 5 (Sudah Dibaca) -->
                        <a href="#" class="flex items-start gap-3.5 p-4 hover:bg-slate-50 transition">
                            <div class="p-2.5 bg-purple-100/60 text-purple-500 rounded-xl shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-800 truncate">Pemeliharaan sistem selesai</p>
                                <p class="text-xs text-slate-400 mt-0.5 line-clamp-2">Pembaruan gateway pembayaran
                                    berjalan normal.</p>
                                <span class="text-xs text-slate-400 mt-1.5 inline-block">5 jam lalu</span>
                            </div>
                        </a>
                    </div>

                    <!-- Footer Pop-up -->
                    <a href="{{ route('detail-notif') }}"
                        class="flex items-center justify-center gap-2 py-3.5 bg-white text-sm font-bold text-primary hover:bg-slate-50 border-t border-slate-100 transition">
                        Lihat selengkapnya
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- User dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-100 transition">
                    <span
                        class="flex items-center justify-center w-9 h-9 rounded-full bg-secondary text-white font-heading font-bold text-sm">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </span>
                    <span class="text-[14px]">{{ Auth::user()->name ?? 'User' }}</span>
                    <svg class="hidden sm:block w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-cloak @click.outside="open = false" x-transition.origin.top.right
                    class="absolute right-0 mt-2 w-48 rounded-xl bg-white border border-slate-200 shadow-lg py-1">
                    <a href="{{ route('profile') }}"
                        class="block px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-primary-soft hover:text-primary">
                        Profile
                    </a>
                    <a href="#"
                        class="block lg:hidden relative px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-primary-soft hover:text-primary">
                        Notifikasi
                    </a>
                    <a href="#"
                        class="block px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-primary-soft hover:text-primary">
                        Settings
                    </a>
                    <hr class="my-1 border-slate-100">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
    </nav>
</header>
