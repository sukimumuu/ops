{{-- Navbar Top --}}
<header class="sticky top-0 z-30 flex items-center gap-4 h-16 sm:px-6
                    bg-white border-b border-slate-200 shadow-sm">
    <nav class="w-full lg:w-4/5 lg:mx-auto top-0 z-30 flex items-center gap-4 h-16 px-4 sm:px-6">
        <!-- Mobile menu button -->
        <button @click="sidebarOpen = true"
                class="lg:hidden p-2 rounded-lg text-slate-500 hover:text-secondary hover:bg-slate-100">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    
        <!-- Page title (optional yield) -->
        <img id="logo-black" src="{{ asset('assets/png/gry trnsprn.png') }}" alt="Mihom" class="hidden lg:block h-8">
        
    
        <div class="flex-1"></div>
        <nav class="hidden lg:flex items-center text-black/80 gap-4 h-16">
            @role('Superadmin')
            <li class="flex items-center list-none text-sm font-semibold h-16 {{ request()->routeIs('dashboard')
                         ? 'border-b-6 border-b-primary text-primary'
                         : 'hover:border-b-2 hover:border-b-primary hover:text-primary/80' }}">
                <a href="">Dashboard</a>
            </li>
            <li class="flex items-center list-none text-sm font-semibold h-16">
                <a href="">Manajemen Pengguna</a>
            </li>
            <li class="flex items-center list-none text-sm font-semibold h-16">
                <a href="">Master Data Properti</a>
            </li>
            <li class="flex items-center list-none text-sm font-semibold h-16">
                <a href="">Transaksi & Escrow</a>
            </li>
            <li class="flex items-center list-none text-sm font-semibold h-16">
                <a href="">Sistem & Konfigurasi</a>
            </li>
            <li class="flex items-center list-none text-sm font-semibold h-16">
                <a href="">Audit Log</a>
            </li>
            @endrole
            @role('PPAT')
            <li class="flex items-center list-none text-sm font-semibold h-16 {{ request()->routeIs('dashboard')
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
            <li class="flex items-center list-none text-sm font-semibold h-16 {{ request()->routeIs('dashboard')
                         ? 'border-b-6 border-b-primary text-primary'
                         : 'hover:border-b-2 hover:border-b-primary hover:text-primary/80' }}">
                <a href="">Dashboard</a>
            </li>
            <li class="flex items-center list-none text-sm font-semibold h-16">
                <a href="">Properti Tersimpan</a>
            </li>
            <li class="flex items-center list-none text-sm font-semibold h-16">
                <a href="">Jadwal Survei Saya</a>
            </li>
            <li class="flex items-center list-none text-sm font-semibold h-16">
                <a href="">Transaksi & Escrow</a>
            </li>
            <li class="flex items-center list-none text-sm font-semibold h-16">
                <a href="">Profil & Verifikasi (KYC)</a>
            </li>
            @endrole
            @role('Seller')
            <li class="flex items-center list-none text-sm font-semibold h-16 {{ request()->routeIs('dashboard')
                         ? 'border-b-6 border-b-primary text-primary'
                         : 'hover:border-b-2 hover:border-b-primary hover:text-primary/80' }}">
                <a href="{{ route('dashboard') }}">Dashboard</a>
            </li>
            <li class="flex items-center list-none text-sm font-semibold h-16 {{ request()->routeIs('my-properties') || request()->routeIs('listing.create') 
                         ? 'border-b-6 border-b-primary text-primary'
                         : 'hover:border-b-2 hover:border-b-primary hover:text-primary/80' }}">
                <a href="{{ route('my-properties') }}">Properti Saya</a>
            </li>
            <li class="flex items-center list-none text-sm font-semibold h-16">
                <a href="">Permintaan Survei Masuk</a>
            </li>
            <li class="flex items-center list-none text-sm font-semibold h-16">
                <a href="">Transaksi Penjualan</a>
            </li>
            <li class="flex items-center list-none text-sm font-semibold h-16">
                <a href="">Rekening Pencairan</a>
            </li>
            <li class="flex items-center list-none text-sm font-semibold h-16">
                <a href="">Profil & Verifikasi (KYC)</a>
            </li>
            @endrole
        </nav>
        <div class="flex-1"></div>
    
        <!-- Notifications -->
        <button class="hidden lg:block relative p-2 rounded-lg text-slate-500 hover:text-secondary hover:bg-slate-100">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-primary"></span>
        </button>
    
        <!-- User dropdown -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                    class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-100 transition">
                <span class="flex items-center justify-center w-9 h-9 rounded-full bg-secondary text-white font-heading font-bold text-sm">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                </span>
                <span class="text-[14px]">{{ Auth::user()->name ?? 'User' }}</span>
                <svg class="hidden sm:block w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
    
            <div x-show="open"
                    x-cloak
                    @click.outside="open = false"
                    x-transition.origin.top.right
                    class="absolute right-0 mt-2 w-48 rounded-xl bg-white border border-slate-200 shadow-lg py-1">
                <a href="#" class="block px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-primary-soft hover:text-primary">
                    Profile
                </a>
                <a href="#" class="block lg:hidden relative px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-primary-soft hover:text-primary">
                    Notifikasi
                </a>
                <a href="#" class="block px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-primary-soft hover:text-primary">
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

