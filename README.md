# Mihom

Mihom adalah platform properti yang menghubungkan pencari dan pemilik properti. Pengguna dapat mencari listing jual atau sewa, melihat detail dan profil pemilik, menyimpan properti, serta mengajukan jadwal survei. Setelah pembeli menyatakan minat, transaksi dilanjutkan lewat temu PPAT, uang muka, pengecekan legalitas, pelunasan escrow, penandatanganan AJB, pencairan dana, dan balik nama sertifikat.

## Fitur utama

### Publik

- Beranda, daftar properti, detail properti, profil pemilik, halaman tentang, dan kontak.
- Pencarian listing yang sudah tayang, dengan filter jenis properti, kategori jual atau sewa, rentang harga, serta lokasi.
- Kalkulator pajak properti untuk estimasi PPh Final penjual (2,5%) dan BPHTB pembeli (5%).
- Registrasi, login pengguna, dan login terpisah untuk panel.
- Asisten chatbot untuk mencari properti dan membantu pengisian formulir listing. Percakapan tamu terikat pada session; percakapan pengguna yang sudah login terikat pada akun.

### Akun dan listing

- Profil dengan penggantian kata sandi dan avatar.
- Verifikasi email memakai kode OTP. Fitur penjual hanya terbuka setelah email terverifikasi.
- Wizard listing dengan draf, data wilayah (provinsi, kabupaten/kota, kecamatan), unggah foto dan dokumen, serta watermark pada foto.
- Bantuan AI untuk ekstraksi data listing dan penulisan deskripsi.
- Pengelolaan properti sendiri, permintaan survei, pencatatan hasil survei, serta rekening tujuan pencairan.
- Peninjauan listing oleh admin: setujui atau tolak, lalu tugaskan PPAT.

### Transaksi

Setelah survei selesai dan pembeli memilih untuk melanjutkan, alur pembelian terdiri dari tujuh tahap:

1. Jadwal temu PPAT.
2. Pembayaran uang muka (DP) ke virtual account.
3. Pengecekan legalitas BPN dan validasi pajak oleh PPAT.
4. Pelunasan properti ke escrow.
5. Penjadwalan dan penandatanganan Akta Jual Beli (AJB), termasuk unggah bukti.
6. Pencairan escrow oleh admin: catat transfer, lalu konfirmasi.
7. Balik nama sertifikat, dengan unggah tanda terima BPN oleh PPAT.

Pembeli dapat mengikuti properti tersimpan, jadwal survei, transaksi escrow, dan properti yang dibeli. Penjual mengikuti transaksi penjualan. Notifikasi dalam aplikasi didukung notifikasi browser (Web Push).

### Panel operasional

- **User** dapat bertindak sebagai pembeli dan penjual, sesuai izin yang dimiliki.
- **PPAT** mengelola antrian klien, validasi dokumen, jadwal temu dan AJB, serta tanda terima BPN.
- **Admin** mengelola pengguna, meninjau listing, memantau transaksi dan escrow, serta melihat audit log.
- **Superadmin** memiliki seluruh izin, termasuk manajemen pengguna, konfigurasi peran dan permission, master data properti, dan audit log.

Akun yang dinonaktifkan tidak dapat melanjutkan sesi. Peran lama `Buyer` dan `Seller` digabung ke peran `User` saat seeder dijalankan.

## Tech stack

- **Backend:** PHP 8.3+, Laravel 13, Eloquent ORM.
- **Database:** SQLite sebagai konfigurasi bawaan. Koneksi lain dapat diatur lewat konfigurasi Laravel.
- **Frontend:** Blade, Tailwind CSS 4, Vite 8, JavaScript, dan Alpine.js.
- **Akses dan peran:** Spatie Laravel Permission.
- **AI:** Groq API untuk chatbot dan fitur berbantuan AI.
- **Pembayaran:** Xendit (virtual account) untuk DP dan pelunasan escrow. Persentase DP bawaan adalah 10%.
- **Email:** SMTP untuk pengiriman kode verifikasi email.
- **Notifikasi browser:** Web Push dengan kunci VAPID.
- **Pemrosesan gambar:** Intervention Image.
- **Pengujian:** Pest 5 dan Pest Laravel.

## Persyaratan

- PHP **8.3 atau lebih baru** dengan ekstensi PHP yang dibutuhkan Laravel.
- Composer.
- Node.js **20.19+ atau 22.12+** dan npm.
- Git.

## Instalasi lokal

1. Clone repository lalu masuk ke folder project:

   ```bash
   git clone <URL_REPOSITORY>
   cd ops
   ```

2. Jalankan skrip setup yang tersedia di Composer:

   ```bash
   composer run setup
   ```

   Skrip ini memasang dependensi PHP, membuat `.env` dari `.env.example` jika belum ada, membuat application key, menjalankan migrasi database, memasang dependensi frontend, dan membangun aset. Konfigurasi bawaan menggunakan SQLite di `database/database.sqlite`.

3. Ubah `APP_NAME` di `.env` menjadi `Mihom` jika perlu, lalu atur integrasi di bagian [Konfigurasi integrasi](#konfigurasi-integrasi).

4. Jalankan aplikasi dan proses development:

   ```bash
   composer run dev
   ```

   Perintah ini menjalankan server Laravel, queue listener, dan Vite. Buka alamat lokal yang ditampilkan oleh server (biasanya `http://localhost:8000`).

### Data demo

Untuk mengisi peran, permission, dan akun demo di database lokal:

```bash
php artisan db:seed
```

Seeder membuat akun demo dengan kredensial bawaan yang ditentukan di `database/seeders/DatabaseSeeder.php`. Gunakan hanya untuk development lokal; jangan gunakan atau jalankan data kredensial demo di production.

## Konfigurasi integrasi

Fitur inti dapat dijalankan dengan konfigurasi lokal bawaan. Integrasi berikut memerlukan konfigurasi tambahan:

- **Chatbot dan fitur AI:** isi `GROQ_API_KEY`. Model dapat diubah melalui `GROQ_CHAT_MODEL`; nilai default-nya `qwen/qwen3.8-27b`.
- **Verifikasi email:** isi `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, dan `MAIL_FROM_NAME`. Tanpa SMTP yang valid, kode OTP tidak terkirim dan fitur penjual tetap terkunci.
- **Notifikasi browser:** buat pasangan kunci dengan `php artisan webpush:generate-vapid-keys`, lalu isi `WEBPUSH_VAPID_PUBLIC_KEY`, `WEBPUSH_VAPID_PRIVATE_KEY`, dan `WEBPUSH_VAPID_SUBJECT`.
- **Pembayaran Xendit:** atur `XENDIT_ENVIRONMENT` ke `sandbox` atau `production`, lalu isi secret key dan webhook token yang sesuai (`XENDIT_SANDBOX_*` atau `XENDIT_PRODUCTION_*`). Kanal virtual account bawaan adalah `BNI` (`XENDIT_DP_VA_CHANNEL_CODE`) dengan masa berlaku 24 jam (`XENDIT_DP_VA_EXPIRY_HOURS`). Persentase DP diatur lewat `MIHOM_DP_PERCENTAGE`. Webhook pembayaran diterima di `POST /api/v2/webhooks/xendit`.

Setelah mengubah konfigurasi environment, bersihkan cache konfigurasi jika sebelumnya pernah di-cache:

```bash
php artisan config:clear
```

## Pengujian dan build

Jalankan pengujian:

```bash
php artisan test
```

Bangun aset frontend untuk deployment:

```bash
npm run build
```

## Dokumentasi API chatbot

Spesifikasi OpenAPI untuk endpoint chatbot tersedia di [`docs/chatbot-api.openapi.yaml`](docs/chatbot-api.openapi.yaml). Endpoint aplikasi adalah `POST /api/chat`. Endpoint ini memakai session Laravel dan token CSRF, bukan bearer token, dan dibatasi 10 permintaan per menit per alamat IP.

## Struktur direktori

- `app/` — controller, model, policy, service, dan komponen aplikasi.
- `routes/` — definisi route web, API, dan console.
- `resources/` — tampilan Blade, CSS, dan JavaScript.
- `database/` — migrasi, factory, dan seeder.
- `public/` — aset publik.
- `tests/` — pengujian fitur dan unit.
- `docs/` — spesifikasi API.
