<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Mihom - Platform properti terpercaya untuk menemukan rumah impian Anda di Indonesia.">
    <link rel="icon" href="{{ asset('assets/png/faviconblack.ico') }}" media="(prefers-color-scheme: light)">
    <link rel="icon" href="{{ asset('assets/png/faviconwhite.ico') }}" media="(prefers-color-scheme: dark)">
    <title>Mihom - Temukan Properti Impian Anda</title>
    @vite(['resources/css/app.css', 'resources/css/welcome.css', 'resources/js/app.js'])
</head>
<body class="m-0 bg-white font-sans text-secondary antialiased">
    <x-navbar />

    <div class="mobile-nav fixed inset-x-0 top-16 z-40 hidden flex-col gap-1 border-b border-gray-100 bg-white/95 p-4 shadow-xl backdrop-blur-md md:hidden" id="mobileNav">
        <a href="#beranda" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Beranda</a>
        <a href="#properti" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Properti</a>
        <a href="#pajak" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Kalkulator Pajak</a>
        <a href="#tentang" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Tentang Kami</a>
        <a href="#kontak" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Kontak</a>
        <a href="{{ route('login') }}" class="mt-2 rounded-lg bg-primary px-5 py-3 text-center text-sm font-bold text-white">Daftar / Masuk</a>
    </div>

    <main>
        <section class="relative flex min-h-screen items-center overflow-hidden" id="beranda">
            <img src="https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=1600&q=80&auto=format&fit=crop" alt="Rumah modern" class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-[linear-gradient(135deg,rgb(44_62_80/85%),rgb(44_62_80/45%)_65%,rgb(44_62_80/25%))]"></div>
            <div class="relative z-10 mx-auto w-full max-w-7xl px-4 py-28 sm:px-6 lg:py-36">
                <h1 class="max-w-3xl font-display text-5xl font-extrabold leading-[1.08] tracking-tight text-white sm:text-6xl lg:text-7xl">Temukan Properti<br>Impian Anda</h1>
                <p class="mb-10 mt-5 max-w-xl text-base leading-7 text-white/80 sm:text-lg">Kami membantu Anda menemukan properti yang sempurna sesuai kebutuhan dan anggaran. Lebih dari 10.000 listing terpercaya tersedia.</p>
                <div class="max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                    <div class="flex border-b border-gray-100"><button class="border-b-2 border-primary px-7 py-3.5 text-sm font-bold text-primary" onclick="setTab(this)">Beli</button><button class="px-7 py-3.5 text-sm font-bold text-gray-400" onclick="setTab(this)">Sewa</button></div>
                    <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-[1.2fr_1fr_1fr_auto] lg:items-end">
                        <label class="block px-2"><span class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-gray-400">Lokasi</span><select class="w-full border-0 bg-transparent p-0 text-sm font-semibold text-secondary outline-none"><option>Jakarta Selatan, Indonesia</option><option>Bali</option><option>Surabaya</option><option>Bandung</option></select></label>
                        <label class="block border-gray-100 px-2 sm:border-l"><span class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-gray-400">Tipe Properti</span><select class="w-full border-0 bg-transparent p-0 text-sm font-semibold text-secondary outline-none"><option>Semua Tipe</option><option>Rumah</option><option>Apartemen</option><option>Villa</option><option>Ruko</option></select></label>
                        <label class="block border-gray-100 px-2 sm:border-l"><span class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-gray-400">Kisaran Harga</span><select class="w-full border-0 bg-transparent p-0 text-sm font-semibold text-secondary outline-none"><option>Semua Harga</option><option>Di bawah 500 Juta</option><option>500 Juta - 1 Miliar</option><option>1 - 3 Miliar</option></select></label>
                        <button class="group flex items-center justify-center gap-2 rounded-lg bg-primary px-6 py-3.5 text-sm font-bold text-white transition duration-200 hover:bg-[#df3f05] active:scale-95 focus:outline-none focus:ring-2 focus:ring-primary/50">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5 transition-transform group-hover:scale-110">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <span>Cari Properti</span>
                    </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="fade-in px-4 py-20 sm:px-6" id="properti"><div class="mx-auto max-w-7xl"><p class="mb-3 text-xs font-extrabold uppercase tracking-[0.2em] text-primary">Kategori</p><h2 class="font-display text-3xl font-extrabold text-secondary sm:text-4xl">Jelajahi Berdasarkan Tipe Properti</h2><div class="mt-9 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ([['rumah.svg','Rumah'],['apartemen.svg','Apartemen'],['tanah.svg','Tanah'],['ruko.svg','Ruko'],['villa.svg','Villa'],['kosan.svg','Kosan']] as [$icon, $name])
                <a href="#" class="group flex flex-col items-center gap-3 rounded-2xl border-2 border-gray-100 bg-gray-50 p-6 text-center transition hover:-translate-y-1 hover:border-primary hover:bg-[#fff5f1]"><span class="flex h-14 w-14 items-center justify-center rounded-xl bg-white shadow-sm"><img src="{{ asset('assets/png/' . $icon) }}" alt="{{ $name }}" class="h-8 w-8"></span><span class="text-sm font-bold text-secondary">{{ $name }}</span></a>
            @endforeach
        </div></div></section>

        <section class="fade-in bg-gray-50 px-4 py-20 sm:px-6"><div class="mx-auto max-w-7xl"><div class="mb-8 flex flex-wrap items-end justify-between gap-4"><div><p class="mb-3 text-xs font-extrabold uppercase tracking-[0.2em] text-primary">Rekomendasi</p><h2 class="font-display text-3xl font-extrabold text-secondary sm:text-4xl">Properti Terbaik Untuk Anda</h2></div><a href="{{ route('properti') }}" class="rounded-lg border-2 border-primary px-5 py-2.5 text-sm font-bold text-primary transition hover:bg-primary hover:text-white">Lihat Semua Properti</a></div><div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([['1568605114967-8130f3a36994','Rp 1,2 M','Rumah Modern 3 Kamar Tidur','Kemang, Jakarta Selatan','Dijual','3','120 m²'],['1600596542815-ffad4c1539a9','Rp 3,5 M','Villa Mewah dengan Kolam Renang','Ubud, Bali','Dijual','4','250 m²'],['1545324418-cc1a3fa10c00','Rp 8,5 Jt/bln','Apartemen Studio City View','Sudirman, Jakarta Pusat','Disewa','1','42 m²'],['1512917774080-9991f1c4c750','Rp 2,1 M','Rumah Cluster Premium Baru','BSD City, Tangerang','Dijual','4','180 m²']] as [$image, $price, $title, $location, $status, $beds, $size])
                <article class="overflow-hidden rounded-2xl bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl"><div class="relative"><img src="https://images.unsplash.com/photo-{{ $image }}?w=500&q=80&auto=format&fit=crop" alt="{{ $title }}" class="h-48 w-full object-cover"><span class="absolute left-3 top-3 rounded-full {{ $status === 'Disewa' ? 'bg-blue-500' : 'bg-primary' }} px-3 py-1 text-[11px] font-extrabold uppercase text-white">{{ $status }}</span></div><div class="p-4"><p class="mb-1 text-lg font-extrabold text-primary">{{ $price }}</p><h3 class="mb-2 text-sm font-bold leading-5 text-secondary">{{ $title }}</h3><p class="mb-3 text-xs text-gray-400">⌖ {{ $location }}</p><div class="flex gap-4 border-t border-gray-100 pt-3 text-xs font-semibold text-gray-500"><span>⌂ {{ $beds }}</span><span>▣ {{ $size }}</span></div></div></article>
            @endforeach
        </div></div></section>

        <section class="fade-in px-4 py-20 sm:px-6" id="tentang"><div class="mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-2"><div class="relative pb-6"><img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=700&q=80&auto=format&fit=crop" alt="Pasangan bahagia" class="h-72 w-full rounded-2xl object-cover shadow-xl sm:h-[460px]"><div class="absolute bottom-0 right-0 rounded-2xl bg-white px-5 py-3 shadow-xl"><p class="text-xl font-extrabold text-primary">10.000+</p><p class="text-xs font-semibold text-gray-400">Pelanggan Puas</p></div></div><div><p class="mb-3 text-xs font-extrabold uppercase tracking-[0.2em] text-primary">Kenapa Mihom?</p><h2 class="font-display text-3xl font-extrabold leading-tight text-secondary sm:text-4xl">Solusi Properti Terbaik<br>Tanpa Ribet</h2><p class="my-6 leading-7 text-gray-500">Mihom hadir untuk memudahkan Anda dalam mencari, membeli, atau menyewa properti impian. Dengan teknologi terkini dan agen berpengalaman, kami siap membantu setiap langkah.</p><div class="grid gap-5 sm:grid-cols-2">@foreach ([['Pencarian Mudah','Filter canggih untuk menemukan properti sesuai kebutuhan.'],['Harga Transparan','Tidak ada biaya tersembunyi.'],['Agen Terpercaya','Jaringan agen terverifikasi siap mendampingi Anda.'],['Proses Cepat','Proses efisien hingga serah terima kunci.']] as [$title, $description])<div class="flex gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#fff0eb] text-primary">✓</span><div><h3 class="text-sm font-extrabold text-secondary">{{ $title }}</h3><p class="mt-1 text-xs leading-5 text-gray-400">{{ $description }}</p></div></div>@endforeach</div></div></div></section>

        <section class="fade-in bg-secondary px-4 py-16 text-white sm:px-6"><div class="mx-auto grid max-w-7xl grid-cols-2 gap-8 text-center lg:grid-cols-4">@foreach ([['10,000+','Properti Terdaftar'],['5,000+','Transaksi Berhasil'],['500+','Agen Terpercaya'],['50+','Kota Terjangkau']] as [$number, $label])<div><p class="font-display text-4xl font-extrabold text-primary">{{ $number }}</p><p class="mt-2 text-sm text-white/60">{{ $label }}</p></div>@endforeach</div></section>

        <section class="fade-in bg-gray-50 px-4 py-20 text-center sm:px-6"><div class="mx-auto max-w-7xl"><p class="mb-3 text-xs font-extrabold uppercase tracking-[0.2em] text-primary">Cara Kerja</p><h2 class="font-display text-3xl font-extrabold text-secondary sm:text-4xl">Bagaimana Mihom Membantu Anda?</h2><p class="mx-auto mt-4 max-w-xl leading-7 text-gray-500">Tiga langkah mudah untuk menemukan properti impian Anda bersama kami.</p><div class="mt-12 grid gap-5 text-left md:grid-cols-3">@foreach ([['01','Cari Properti','Gunakan fitur pencarian untuk menemukan properti sesuai lokasi, tipe, dan anggaran.'],['02','Hubungi Agen','Terhubung dengan agen terverifikasi untuk mendapatkan informasi dan jadwalkan kunjungan.'],['03','Selesaikan Transaksi','Kami membantu proses administrasi hingga serah terima kunci properti.']] as [$step, $title, $description])<div class="rounded-2xl bg-white p-7 shadow-sm"><p class="font-display text-5xl font-extrabold text-orange-200">{{ $step }}</p><h3 class="mt-4 text-lg font-extrabold text-secondary">{{ $title }}</h3><p class="mt-2 text-sm leading-6 text-gray-500">{{ $description }}</p></div>@endforeach</div></div></section>

        <section class="fade-in relative overflow-hidden px-4 py-24 text-center sm:px-6"><img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1400&q=80&auto=format&fit=crop" alt="Rumah" class="absolute inset-0 h-full w-full object-cover"><div class="absolute inset-0 bg-secondary/85"></div><div class="relative mx-auto max-w-2xl"><h2 class="font-display text-3xl font-extrabold text-white sm:text-5xl">Siap Menemukan Rumah Impian Anda?</h2><p class="mx-auto mt-4 max-w-lg leading-7 text-white/70">Bergabunglah dengan lebih dari 50.000 pengguna yang telah menemukan properti impian mereka bersama Mihom.</p><a href="{{ route('login') }}" class="mt-8 inline-block rounded-lg bg-primary px-8 py-4 text-base font-extrabold text-white transition hover:bg-[#df3f05]">Mulai Sekarang</a></div></section>
    </main>

    <footer class="bg-[#1f2d3a] px-4 py-14 text-white sm:px-6" id="kontak"><div class="mx-auto grid max-w-7xl gap-10 sm:grid-cols-2 lg:grid-cols-5"><div class="lg:col-span-2"><div class="flex items-center gap-2"><img src="{{ asset('assets/png/wht trnsprn.png') }}" alt="Mihom" class="h-8"><span class="font-display text-[22px] font-extrabold">mihom</span></div><p class="mt-4 max-w-xs text-sm leading-6 text-white/55">Platform properti terpercaya untuk menemukan rumah impian Anda di seluruh Indonesia.</p></div>@foreach ([['Layanan Kami',['Beli Properti','Sewa Properti','Jual Properti','KPR Rumah']],['Tipe Properti',['Rumah','Apartemen','Villa','Ruko']],['Informasi',['Tentang Mihom','Blog Properti','Karir','Kebijakan Privasi']]] as [$heading, $links])<div><h3 class="mb-4 text-xs font-extrabold uppercase tracking-wider">{{ $heading }}</h3><ul class="space-y-3 text-sm text-white/55">@foreach ($links as $link)<li><a href="#" class="transition hover:text-primary">{{ $link }}</a></li>@endforeach</ul></div>@endforeach</div><div class="mx-auto mt-12 max-w-7xl border-t border-white/10 pt-5 text-center text-xs text-white/40">© 2026 Mihom. Hak cipta dilindungi.</div></footer>

    <script>
        const navbar = document.getElementById('navbar');
        const blackLogo = document.getElementById('logo-black');
        const whiteLogo = document.getElementById('logo-white');
        const hamburger = document.getElementById('hamburgerBtn');
        const mobileNav = document.getElementById('mobileNav');
        window.addEventListener('scroll', () => { const scrolled = window.scrollY > 60; navbar.classList.toggle('scrolled', scrolled); blackLogo.classList.toggle('hidden', !scrolled); blackLogo.classList.toggle('block', scrolled); whiteLogo.classList.toggle('hidden', scrolled); });
        function closeMobileNav() { mobileNav.classList.remove('open'); hamburger.classList.remove('open'); hamburger.setAttribute('aria-expanded', 'false'); }
        hamburger.addEventListener('click', () => { const open = mobileNav.classList.toggle('open'); hamburger.classList.toggle('open', open); hamburger.setAttribute('aria-expanded', String(open)); });
        document.addEventListener('click', (event) => { if (!navbar.contains(event.target) && !mobileNav.contains(event.target)) closeMobileNav(); });
        function setTab(button) { document.querySelectorAll('.search-tab').forEach((tab) => tab.classList.remove('active')); button.classList.add('active'); }
        const observer = new IntersectionObserver((entries) => entries.forEach((entry) => { if (entry.isIntersecting) entry.target.classList.add('visible'); }), { threshold: 0.12 });
        document.querySelectorAll('.fade-in').forEach((element) => observer.observe(element));
        document.querySelectorAll('a[href^="#"]').forEach((link) => link.addEventListener('click', (event) => { const target = document.querySelector(link.getAttribute('href')); if (target) { event.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); } }));
    </script>
</body>
</html>
