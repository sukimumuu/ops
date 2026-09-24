<footer class="bg-[#1f2d3a] px-4 py-14 text-white sm:px-6" id="kontak">
    <div class="mx-auto grid max-w-7xl gap-10 sm:grid-cols-2 lg:grid-cols-5">
        
        <!-- Brand & Description -->
        <div class="lg:col-span-2">
            <div class="flex items-center gap-2">
                <img src="{{ asset('assets/png/wht trnsprn.png') }}" alt="Mihom" class="h-8">
                <span class="font-display text-[22px] font-extrabold">mihom</span>
            </div>
            <p class="mt-4 max-w-xs text-sm leading-6 text-white/55">
                Platform properti terpercaya untuk menemukan rumah impian Anda di seluruh Indonesia.
            </p>
        </div>

        <!-- Dynamic Navigation Links -->
        @foreach ([
            ['Layanan Kami', ['Beli Properti', 'Sewa Properti', 'Jual Properti', 'KPR Rumah']],
            ['Tipe Properti', ['Rumah', 'Apartemen', 'Villa', 'Ruko']],
            ['Informasi', ['Tentang Mihom', 'Blog Properti', 'Karir', 'Kebijakan Privasi']]
        ] as [$heading, $links])
            <div>
                <h3 class="mb-4 text-xs font-extrabold uppercase tracking-wider">{{ $heading }}</h3>
                <ul class="space-y-3 text-sm text-white/55">
                    @foreach ($links as $link)
                        <li>
                            <a href="#" class="transition hover:text-primary">{{ $link }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach

    </div>

    <!-- Copyright -->
    <div class="mx-auto mt-12 max-w-7xl border-t border-white/10 pt-5 text-center text-xs text-white/40">
        © 2026 Mihom. Hak cipta dilindungi.
    </div>
</footer>