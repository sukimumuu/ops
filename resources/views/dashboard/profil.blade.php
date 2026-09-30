<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Laba Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        navbg: '#1e293b',
                        primary: '#f97316', // Orange
                        pagebg: '#f8fafc',
                        bordercolor: '#e2e8f0',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        /* Custom scrollbar for a cleaner look */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Toggle Switch CSS */
        .toggle-checkbox:checked {
            right: 0;
            border-color: #f97316;
        }

        .toggle-checkbox:checked+.toggle-label {
            background-color: #f97316;
        }

        .toggle-checkbox {
            right: 0;
            z-index: 1;
            border-color: #e2e8f0;
            transition: all 0.3s;
        }

        .toggle-label {
            width: 44px;
            height: 24px;
            background-color: #e2e8f0;
            border-radius: 9999px;
            transition: all 0.3s;
        }

        .toggle-circle {
            width: 20px;
            height: 20px;
            background-color: white;
            border-radius: 50%;
            position: absolute;
            top: 2px;
            left: 2px;
            transition: all 0.3s;
        }

        .toggle-checkbox:checked~.toggle-circle {
            transform: translateX(20px);
        }
    </style>
</head>

<body class="text-gray-800 antialiased">

    <!-- Navbar -->
    <header class="bg-navbg text-white shadow-sm sticky top-0 z-50">
        <div class="px-6 py-3 flex items-center justify-between">
            <!-- Left: Logo & Brand -->
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-primary rounded flex items-center justify-center font-bold text-lg">
                    L
                </div>
                <span class="text-xl font-bold tracking-tight">Laba</span>
            </div>

            <!-- Middle: Navigation Links (Hidden on small screens) -->
            <nav class="hidden lg:flex items-center gap-6 text-sm font-medium">
                <a href="#" class="text-gray-300 hover:text-white transition">Dashboard</a>
                <a href="#" class="text-gray-300 hover:text-white transition">Manajemen Pengguna</a>
                <a href="#" class="text-gray-300 hover:text-white transition">Master Data Properti</a>
                <a href="#" class="text-gray-300 hover:text-white transition">Transaksi & Escrow</a>
                <a href="#" class="text-gray-300 hover:text-white transition">Sistem & Konfigurasi</a>
                <a href="#" class="text-gray-300 hover:text-white transition">Audit Log</a>
            </nav>

            <!-- Right: User Profile & Notifications -->
            <div class="flex items-center gap-5">
                <!-- Bell Icon -->
                <button class="text-gray-400 hover:text-white transition relative">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor"
                        viewBox="0 0 256 256">
                        <path
                            d="M214.92,198.81l-14.54-17.11A40,40,0,0,1,192,156V104a64,64,0,0,0-128,0v52a40,40,0,0,1-8.38,25.7L41.08,198.81A16,16,0,0,0,53.3,224H96a32,32,0,0,0,64,0h42.7a16,16,0,0,0,12.22-25.19ZM128,240a16,16,0,0,1-16-16h32A16,16,0,0,1,128,240Zm74.7,14c-1.39,1.64-3.52,2-5.4,2H58.7c-1.88,0-4-.36-5.4-2a6.3,6.3,0,0,1-1.38-5.69l14.54-17.12A24,24,0,0,0,80,156V104a48,48,0,0,1,96,0v52a24,24,0,0,0,5.05,15.19l14.54,17.12A6.3,6.3,0,0,1,202.7,254Z">
                        </path>
                    </svg>
                </button>

                <!-- User Pill -->
                <div class="flex items-center gap-2 cursor-pointer">
                    <div
                        class="w-8 h-8 rounded-full bg-[#fde68a] text-[#b45309] font-bold text-xs flex items-center justify-center">
                        SA
                    </div>
                    <span class="text-sm font-medium hidden sm:block text-gray-200">Super Admin</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Profil Saya</h1>
            <p class="text-gray-500 mt-1">Kelola informasi pribadi, keamanan akun, dan perangkat yang terhubung</p>
        </div>

        <!-- Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_2.5fr] gap-6 items-start">

            <!-- Left Column: Profile Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <!-- Avatar & Actions -->
                <div class="flex flex-col items-center text-center">
                    <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-4.0.3&auto=format&fit=crop&w=256&q=80"
                        alt="Profile Picture"
                        class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-sm mb-4">

                    <div class="flex items-center gap-3 mb-4">
                        <button
                            class="bg-orange-50 hover:bg-orange-100 text-primary font-medium text-sm px-4 py-2 rounded-lg transition border border-orange-100">
                            Ganti Foto
                        </button>
                        <button
                            class="bg-white hover:bg-gray-50 text-gray-600 font-medium text-sm px-4 py-2 rounded-lg transition border border-gray-200">
                            Hapus
                        </button>
                    </div>

                    <p class="text-xs text-gray-400 mb-6 px-4">
                        JPG atau PNG, maksimal 2 MB. Gunakan foto wajah yang jelas.
                    </p>
                </div>

                <hr class="border-gray-100 mb-6">

                <!-- User Info Summary -->
                <div class="text-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Surya Adinata</h2>
                    <p class="text-gray-500 text-sm mb-3">surya.adinata@laba.co.id</p>
                    <span
                        class="inline-block bg-orange-50 text-primary text-xs font-semibold px-3 py-1 rounded-full border border-orange-100">
                        Super Admin
                    </span>
                </div>

                <!-- Details List -->
                <div class="space-y-4 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">ID Admin</span>
                        <span class="font-semibold text-gray-800">ADM-001</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Bergabung</span>
                        <span class="font-medium text-gray-800">12 Agustus 2024</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Status akun</span>
                        <div class="flex items-center gap-1.5 font-medium text-green-600">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            Aktif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Main Content Cards -->
            <div class="space-y-6">

                <!-- Card 1: Informasi Pribadi -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8">
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-gray-800">Informasi Pribadi</h3>
                        <p class="text-sm text-gray-500 mt-1">Pastikan data ini selalu terbaru agar komunikasi
                            operasional berjalan lancar.</p>
                    </div>

                    <form class="space-y-6">
                        <!-- Grid for inputs -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nama Lengkap -->
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1.5">Nama Lengkap</label>
                                <input type="text" value="Surya Adinata"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            </div>

                            <!-- Peran Akun -->
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1.5">Peran Akun</label>
                                <input type="text" value="Super Admin" disabled
                                    class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-2.5 text-gray-500 cursor-not-allowed">
                                <p class="text-xs text-gray-400 mt-1.5">Peran hanya dapat diubah oleh pemilik
                                    organisasi.</p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1.5">Email</label>
                                <input type="email" value="surya.adinata@laba.co.id"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                <p class="text-xs text-gray-400 mt-1.5">Email telah terverifikasi</p>
                            </div>

                            <!-- Nomor Telepon -->
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1.5">Nomor Telepon</label>
                                <input type="tel" value="+62 812-3456-7890"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                <p class="text-xs text-gray-400 mt-1.5">Digunakan untuk OTP dan notifikasi keamanan</p>
                            </div>
                        </div>

                        <!-- Alamat -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1.5">Alamat</label>
                            <input type="text"
                                value="Jl. Jenderal Sudirman Kav. 52–53, Kebayoran Baru, Jakarta Selatan 12190"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        </div>

                        <!-- Footer Actions -->
                        <div
                            class="flex flex-col sm:flex-row justify-between items-center pt-4 border-t border-gray-100 gap-4 mt-8">
                            <span class="text-sm text-gray-400">Terakhir diperbarui 3 hari lalu</span>
                            <button type="button"
                                class="bg-primary hover:bg-orange-600 text-white font-medium rounded-lg px-6 py-2.5 transition w-full sm:w-auto shadow-sm">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Card 2: Keamanan Akun -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8">
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Keamanan Akun</h3>
                            <p class="text-sm text-gray-500 mt-1">Password, autentikasi dua langkah, dan sesi perangkat
                                aktif.</p>
                        </div>
                        <div
                            class="bg-green-50 text-green-600 border border-green-100 px-3 py-1.5 rounded-md flex items-center gap-1.5 text-sm font-semibold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 256 256"
                                fill="currentColor">
                                <path
                                    d="M224,64V128c0,42-22.1,80-60.6,104.6a32.3,32.3,0,0,1-34.8,0C90.1,208,68,170,68,128V64a16,16,0,0,1,16-16h88A16,16,0,0,1,224,64Zm-16,0H84v64c0,35.2,18.5,67,50.7,87.6a16.2,16.2,0,0,0,17.4,0C184.3,195,208,163.2,208,128ZM179.3,108.7a8,8,0,0,0-11.3-11.4l-40,40-20-20a8,8,0,0,0-11.3,11.4l25.6,25.6a8,8,0,0,0,11.3,0Z">
                                </path>
                            </svg>
                            Keamanan baik
                        </div>
                    </div>

                    <!-- Password Section -->
                    <div class="flex flex-col md:flex-row gap-4 items-end mb-8">
                        <div class="flex-1 w-full">
                            <label class="block text-sm font-medium text-gray-500 mb-1.5">Password</label>
                            <input type="password" value="••••••••••••" readonly
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-800 bg-gray-50 outline-none">
                        </div>
                        <div class="flex-1 w-full">
                            <label class="block text-sm font-medium text-gray-500 mb-1.5">Konfirmasi Password
                                Baru</label>
                            <input type="password" value="••••••••••••" readonly
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-gray-800 bg-gray-50 outline-none">
                        </div>
                        <div class="w-full md:w-auto mt-4 md:mt-0">
                            <button
                                class="w-full md:w-auto border border-primary text-primary hover:bg-orange-50 font-medium rounded-lg px-6 py-2.5 transition">
                                Perbarui Password
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mb-8 -mt-6">Terakhir diubah 45 hari lalu</p>

                    <!-- Two-Factor Authentication -->
                    <div class="flex justify-between items-center py-5 border-y border-gray-100 mb-4">
                        <div>
                            <h4 class="text-sm font-bold text-gray-800">Autentikasi Dua Langkah</h4>
                            <p class="text-sm text-gray-500 mt-0.5">Kode OTP dikirim ke +62 812-••••-7890</p>
                        </div>

                        <!-- Toggle Switch -->
                        <label class="flex items-center cursor-pointer relative">
                            <input type="checkbox" checked class="sr-only toggle-checkbox">
                            <div class="toggle-label block bg-gray-300 w-11 h-6 rounded-full"></div>
                            <div class="toggle-circle absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition">
                            </div>
                        </label>
                    </div>

                    <!-- Connected Devices -->
                    <div>
                        <h4 class="text-sm font-bold text-gray-800 mb-4">Perangkat Terhubung</h4>

                        <div class="space-y-4">
                            <!-- Device 1 -->
                            <div
                                class="flex items-center justify-between p-4 border border-gray-100 rounded-lg bg-gray-50/50 hover:bg-gray-50 transition">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-10 h-10 bg-white border border-gray-200 rounded-lg flex items-center justify-center text-gray-600 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 256 256"
                                            fill="currentColor">
                                            <path
                                                d="M224,64V160a16,16,0,0,1-16,16H48a16,16,0,0,1-16-16V64A16,16,0,0,1,48,48H208A16,16,0,0,1,224,64Zm-16,0H48V160H208ZM232,208v16H24v-16a16,16,0,0,1,16-16H216A16,16,0,0,1,232,208Z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-semibold text-gray-800">Chrome di MacBook Pro</p>
                                            <span
                                                class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded">Saat
                                                ini</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5">Jakarta, Indonesia • Aktif sekarang</p>
                                    </div>
                                </div>
                                <span class="text-sm font-medium text-gray-400">Aktif</span>
                            </div>

                            <!-- Device 2 -->
                            <div
                                class="flex items-center justify-between p-4 border border-gray-100 rounded-lg hover:bg-gray-50 transition">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-10 h-10 bg-white border border-gray-200 rounded-lg flex items-center justify-center text-gray-600 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 256 256"
                                            fill="currentColor">
                                            <path
                                                d="M176,16H80A24,24,0,0,0,56,40V216a24,24,0,0,0,24,24h96a24,24,0,0,0,24-24V40A24,24,0,0,0,176,16Zm8,200a8,8,0,0,1-8,8H80a8,8,0,0,1-8-8V40a8,8,0,0,1,8-8h96a8,8,0,0,1,8,8ZM128,192a12,12,0,1,0,12,12A12,12,0,0,0,128,192Z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">Safari di iPhone 15 Pro</p>
                                        <p class="text-xs text-gray-500 mt-0.5">Jakarta, Indonesia • 2 jam lalu</p>
                                    </div>
                                </div>
                                <button
                                    class="text-sm font-medium text-red-500 hover:text-red-700 transition">Keluar</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

</body>

</html>
