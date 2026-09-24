<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Katalog Properti - Mihom</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (CDN for static preview) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FC4907',
                        secondary: '#2C3E50',
                        light: '#F8F9FA'
                    },
                    fontFamily: {
                        manrope: ['Manrope', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="font-manrope bg-light text-secondary antialiased">
    <!-- Navbar Component -->
    <x-navbar />
    
    <!-- Hero Section -->
    <header class="relative h-[600px] w-full bg-secondary overflow-hidden">
        <!-- Background Image -->
        <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1600&q=80&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover opacity-60" alt="Rumah Mewah">
        
        <!-- Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-secondary/80 to-transparent"></div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex flex-col justify-center">
            <span class="text-light font-bold text-sm tracking-wider uppercase mb-2">Katalog Properti Pilihan</span>
            <h1 class="font-outfit text-5xl md:text-6xl font-extrabold text-white leading-tight max-w-2xl mb-4">
                Temukan ruang untuk hidup lebih baik.
            </h1>
            <p class="text-white/80 text-lg max-w-xl mb-12">
                Jelajahi rumah, apartemen, dan vila terkurasi di kawasan terbaik Indonesia—transparan, terpercaya, dan mudah dibandingkan.
            </p>
            
            <!-- Search Bar -->
            <div class="bg-white rounded-xl p-3 flex flex-col md:flex-row gap-4 items-center w-full max-w-5xl shadow-xl">
                <!-- Lokasi -->
                <div class="flex-1 w-full px-4 border-b md:border-b-0 md:border-r border-gray-200 pb-3 md:pb-0">
                    <label class="text-[10px] text-gray-500 font-bold uppercase tracking-wide block mb-1">Lokasi / Kata Kunci</label>
                    <div class="flex items-center gap-2 text-secondary font-semibold text-sm">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input type="text" value="Jakarta Selatan, rumah dekat MRT..." class="w-full bg-transparent border-none outline-none text-secondary">
                    </div>
                </div>
                <!-- Tipe Properti -->
                <div class="flex-1 w-full px-4 border-b md:border-b-0 md:border-r border-gray-200 pb-3 md:pb-0">
                    <label class="text-[10px] text-gray-500 font-bold uppercase tracking-wide block mb-1">Tipe Properti</label>
                    <select class="w-full bg-transparent border-none outline-none text-secondary font-semibold text-sm appearance-none cursor-pointer">
                        <option>Semua tipe</option>
                    </select>
                </div>
                <!-- Kisaran Harga -->
                <div class="flex-1 w-full px-4 pb-3 md:pb-0">
                    <label class="text-[10px] text-gray-500 font-bold uppercase tracking-wide block mb-1">Kisaran Harga</label>
                    <select class="w-full bg-transparent border-none outline-none text-secondary font-semibold text-sm appearance-none cursor-pointer">
                        <option>Rp 1 M - Rp 8 M</option>
                    </select>
                </div>
                <!-- Button -->
                <div class="w-full md:w-auto px-2">
                    <button class="w-full md:w-auto bg-primary hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-lg transition text-sm whitespace-nowrap">
                        Cari properti
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <!-- Section Title -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
            <div>
                <h3 class="text-xs font-bold text-gray-400 tracking-wider uppercase mb-1">Koleksi Terkini</h3>
                <h2 class="font-outfit text-3xl font-extrabold text-secondary">Properti pilihan untuk Anda</h2>
                <p class="text-sm text-gray-500 mt-1">Menampilkan 128 properti terverifikasi</p>
            </div>
            <div class="flex gap-2">
                <button class="flex items-center gap-2 border border-gray-200 bg-white px-4 py-2 rounded-full text-sm font-semibold text-secondary hover:bg-gray-50">
                    Urutkan: Rekomendasi
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <button class="flex items-center gap-2 bg-secondary text-white px-5 py-2 rounded-full text-sm font-semibold hover:bg-secondary/90">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                    Grid
                </button>
                <button class="flex items-center gap-2 border border-gray-200 bg-white px-4 py-2 rounded-full text-sm font-semibold text-secondary hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-2 mb-10">
            <button class="bg-secondary text-white px-5 py-2 rounded-full text-sm font-semibold">Semua</button>
            <button class="bg-white border border-gray-200 text-secondary hover:border-primary hover:text-primary px-5 py-2 rounded-full text-sm font-semibold transition">Rumah</button>
            <button class="bg-white border border-gray-200 text-secondary hover:border-primary hover:text-primary px-5 py-2 rounded-full text-sm font-semibold transition">Apartemen</button>
            <button class="bg-white border border-gray-200 text-secondary hover:border-primary hover:text-primary px-5 py-2 rounded-full text-sm font-semibold transition">Vila</button>
            <button class="bg-white border border-gray-200 text-secondary hover:border-primary hover:text-primary px-5 py-2 rounded-full text-sm font-semibold transition">Tanah</button>
            <button class="bg-white border border-gray-200 text-secondary hover:border-primary hover:text-primary px-5 py-2 rounded-full text-sm font-semibold transition">3+ Kamar</button>
            <button class="bg-white border border-gray-200 text-secondary hover:border-primary hover:text-primary px-5 py-2 rounded-full text-sm font-semibold transition">Siap Huni</button>
            <button class="bg-white border border-gray-200 text-secondary hover:border-primary hover:text-primary px-5 py-2 rounded-full text-sm font-semibold transition">Filter lainnya +</button>
        </div>

        <!-- Property Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Property Card 1 -->
            <x-property-card 
                image="https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&q=80"
                tag="Pilihan editor"
                price="Rp 4,85 M"
                title="Aruna Residence"
                location="Cilandak, Jakarta Selatan"
                specs="4 KT &bull; 3 KM &bull; 210 m²"
            />
            
            <!-- Property Card 2 -->
            <x-property-card 
                image="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&q=80"
                tag="Dekat MRT"
                price="Rp 2,9 M"
                title="Verde Heights"
                location="Kuningan, Jakarta Selatan"
                specs="2 KT &bull; 2 KM &bull; 98 m²"
            />

            <!-- Property Card 3 -->
            <x-property-card 
                image="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800&q=80"
                tag="Baru"
                price="Rp 8,2 M"
                title="Uma Svara Villa"
                location="Ubud, Bali"
                specs="3 KT &bull; 3 KM &bull; 340 m²"
            />

            <!-- Property Card 4 -->
            <x-property-card 
                image="https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800&q=80"
                tag="Open house"
                price="Rp 5,6 M"
                title="Kemang Courtyard"
                location="Kemang, Jakarta Selatan"
                specs="4 KT &bull; 4 KM &bull; 260 m²"
            />

            <!-- Property Card 5 -->
            <x-property-card 
                image="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&q=80"
                tag="Harga terbaik"
                price="Rp 3,15 M"
                title="Nara Townhouse"
                location="Alam Sutera, Tangerang"
                specs="3 KT &bull; 3 KM &bull; 188 m²"
            />

            <!-- Property Card 6 -->
            <x-property-card 
                image="https://images.unsplash.com/photo-1600566752355-35792bedcfea?w=800&q=80"
                tag="Eksklusif"
                price="Rp 6,75 M"
                title="The Maris"
                location="Sanur, Bali"
                specs="3 KT &bull; 3 KM &bull; 280 m²"
            />
        </div>

        <!-- Pagination -->
        <div class="flex justify-center items-center gap-2 mt-12">
            <button class="w-10 h-10 flex justify-center items-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50">
                &larr;
            </button>
            <button class="w-10 h-10 flex justify-center items-center rounded-lg bg-primary text-white font-bold">1</button>
            <button class="w-10 h-10 flex justify-center items-center rounded-lg border border-gray-200 text-secondary font-bold hover:bg-gray-50">2</button>
            <button class="w-10 h-10 flex justify-center items-center rounded-lg border border-gray-200 text-secondary font-bold hover:bg-gray-50">3</button>
            <button class="w-10 h-10 flex justify-center items-center rounded-lg border border-gray-200 text-secondary font-bold hover:bg-gray-50">4</button>
            <span class="text-gray-400">...</span>
            <button class="w-10 h-10 flex justify-center items-center rounded-lg border border-gray-200 text-secondary font-bold hover:bg-gray-50">12</button>
            <button class="w-10 h-10 flex justify-center items-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50">
                &rarr;
            </button>
        </div>
    </main>

    <!-- Consultation Banner -->
    <section class="bg-[#F5EFE6] py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center gap-10">
                <div class="flex-1">
                    <p class="text-primary font-bold text-xs uppercase tracking-wider mb-2">Bingung Memilih?</p>
                    <h2 class="font-outfit text-4xl font-extrabold text-secondary mb-4 leading-tight">
                        Temukan properti yang benar-benar cocok.
                    </h2>
                    <p class="text-gray-600 mb-8 max-w-md">
                        Konsultan lokal kami siap membantu membandingkan kawasan, harga, dan potensi investasi tanpa biaya konsultasi.
                    </p>
                    <button class="bg-primary hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-lg transition text-sm">
                        Konsultasi gratis &rarr;
                    </button>
                </div>
                <div class="flex-1">
                    <img src="https://images.unsplash.com/photo-1560520653-9e0e4c89eb11?w=800&q=80" alt="Konsultasi Properti" class="rounded-2xl shadow-xl w-full object-cover h-[300px]">
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Component -->
    <x-footer />
</body>
</html>
