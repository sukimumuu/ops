<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Kalkulator Pajak Properti - Hitung estimasi PPh Final Penjual dan BPHTB Pembeli secara transparan dan akurat sesuai ketentuan perpajakan properti Indonesia.">
    <link rel="icon" href="{{ asset('assets/png/faviconblack.ico') }}" media="(prefers-color-scheme: light)">
    <link rel="icon" href="{{ asset('assets/png/faviconwhite.ico') }}" media="(prefers-color-scheme: dark)">
    <title>Kalkulator Pajak Properti - Mihom</title>
    @vite(['resources/css/app.css', 'resources/css/welcome.css', 'resources/js/app.js'])
</head>
<body class="m-0 bg-white font-sans text-secondary antialiased">
    <x-navbar />

    {{-- Mobile Nav --}}
    <div class="mobile-nav fixed inset-x-0 top-16 z-40 hidden flex-col gap-1 border-b border-gray-100 bg-white/95 p-4 shadow-xl backdrop-blur-md md:hidden" id="mobileNav">
        <a href="{{ route('home') }}" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Beranda</a>
        <a href="{{ route('properti') }}" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Properti</a>
        <a href="{{ route('kalkulator-pajak') }}" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Kalkulator Pajak</a>
        <a href="{{ route('home') }}#tentang" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Tentang Kami</a>
        <a href="#kontak" class="rounded-lg px-4 py-3 text-sm font-semibold text-secondary hover:bg-[#fff0eb] hover:text-primary" onclick="closeMobileNav()">Kontak</a>
        <a href="{{ route('login') }}" class="mt-2 rounded-lg bg-primary px-5 py-3 text-center text-sm font-bold text-white">Daftar / Masuk</a>
    </div>

    <main>
        <x-tax-calculator />
    </main>

    <x-footer />

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
    </script>
</body>
</html>
