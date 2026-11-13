# Mihom

Mihom adalah platform properti yang menghubungkan pencari dan pemilik properti. Pengguna dapat mencari properti untuk dibeli atau disewa, melihat detail dan profil pemilik, menyimpan listing, serta mengajukan jadwal survei. Aplikasi juga menyediakan alur listing untuk penjual dan dashboard untuk pengelolaan listing, survei, dokumen, pengguna, serta pemantauan transaksi dan escrow.

## Fitur utama

- Pencarian, filter, dan halaman detail properti.
- Registrasi dan login untuk pengguna serta login khusus panel.
- Listing properti dengan wizard, unggah foto/dokumen, dan bantuan AI untuk ekstraksi data serta penulisan deskripsi.
- Asisten chatbot untuk mencari properti dan membantu pengisian formulir listing.
- Properti tersimpan, jadwal survei, notifikasi, dan dukungan notifikasi browser.
- Dashboard berbasis peran untuk penjual, pembeli, PPAT, admin, dan superadmin.
- Kalkulator pajak properti.

## Tech stack

- **Backend:** PHP 8.3+, Laravel 13, Eloquent ORM.
- **Database:** SQLite sebagai konfigurasi bawaan; konfigurasi Laravel juga dapat disesuaikan untuk database lain.
- **Frontend:** Blade, Tailwind CSS 4, Vite 8, dan JavaScript. Komponen interaktif tertentu menggunakan Alpine.js.
- **Akses dan peran:** Spatie Laravel Permission.
- **AI:** Groq API untuk chatbot dan fitur berbantuan AI.
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

3. (Opsional) Ubah nilai `APP_NAME` di `.env` menjadi `Mihom`, lalu atur integrasi yang diperlukan seperti dijelaskan di bagian [Konfigurasi integrasi](#konfigurasi-integrasi).

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

- **Chatbot dan fitur AI:** isi `GROQ_API_KEY` di `.env`. Model dapat diubah melalui `GROQ_CHAT_MODEL`; nilai default-nya `qwen/qwen3.8-27b`.
- **Notifikasi browser:** buat pasangan kunci dengan `php artisan webpush:generate-vapid-keys`, lalu isi `WEBPUSH_VAPID_PUBLIC_KEY`, `WEBPUSH_VAPID_PRIVATE_KEY`, dan `WEBPUSH_VAPID_SUBJECT` di `.env`.

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

Spesifikasi OpenAPI untuk endpoint chatbot tersedia di [`docs/chatbot-api.openapi.yaml`](docs/chatbot-api.openapi.yaml). Endpoint aplikasi adalah `POST /api/chat`; endpoint ini menggunakan session Laravel dan token CSRF, bukan bearer token.

## Struktur direktori

- `app/` — controller, model, policy, service, dan komponen aplikasi.
- `routes/` — definisi route web dan console.
- `resources/` — tampilan Blade, CSS, dan JavaScript.
- `database/` — migrasi, factory, dan seeder.
- `public/` — aset publik.
- `tests/` — pengujian fitur dan unit.
- `docs/` — spesifikasi API.
