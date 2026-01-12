# SISTEM PEMESANAN PAKET TOUR BERBASIS WEB

Aplikasi web untuk katalog dan pemesanan paket tour, lengkap dengan manajemen admin dan owner, pembayaran Midtrans, serta ulasan pelanggan.

## Fitur Utama
- Katalog paket tour dan halaman detail.
- Pemesanan paket, pengisian data peserta, dan status pesanan.
- Pembayaran via Midtrans (Snap dan Virtual Account).
- Rating dan ulasan setelah pesanan selesai.
- Admin: CRUD paket, tampilkan atau sembunyikan paket, verifikasi atau tolak pesanan.
- Owner: rekapitulasi pesanan selesai dan rata rata rating.
- Autentikasi login dan Google OAuth.
- Role based access: customer, admin, owner.

## Peran dan Alur Singkat
- Customer: pilih paket -> pesan -> isi data peserta -> menunggu verifikasi -> bayar -> selesai -> beri rating.
- Admin: kelola paket dan verifikasi atau tolak pesanan.
- Owner: melihat rekapitulasi pendapatan dan rating.

## Status Pesanan
- `menunggu_verifikasi` - data peserta masuk, menunggu validasi admin.
- `menunggu_pembayaran` - pesanan sudah diverifikasi, menunggu pembayaran.
- `pembayaran_selesai` - pembayaran berhasil.
- `pesanan_selesai` - perjalanan selesai dan dapat diberi rating.
- `pesanan_ditolak` - pesanan ditolak (kuota penuh atau alasan admin).

## Teknologi
- Backend: Laravel 12, PHP 8.2, Laravel Socialite, Midtrans.
- Frontend: React 18, Vite, Tailwind CSS.
- Database: SQLite (default) atau MySQL.
- Auth: Laravel session dan CSRF Sanctum.

## Persyaratan
- PHP 8.2, Composer
- Node.js 18+ dan npm
- Database MySQL

## Instalasi Lokal
1. Salin env dan set konfigurasi.
   - PowerShell: `Copy-Item .env.example .env`
2. Generate key:
   - `php artisan key:generate`
3. Siapkan database.
   - MySQL: isi `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
4. Install dependencies:
   - `composer install`
   - `npm install`
5. Migrasi dan seed:
   - `php artisan migrate --seed`
6. Buat symlink storage:
   - `php artisan storage:link`

## Menjalankan Aplikasi
- Semua sekaligus:
  - `composer run dev`
- Atau terpisah:
  - `php artisan serve`
  - `npm run dev`

Akses aplikasi di `APP_URL` (default `http://localhost`).

## Akun Default (Seeder)
- Admin: `admin@tour.com` / `admin123`
- Owner: `owner@tour.com` / `owner123`
- Customer: login via Google atau buat user baru dengan role `customer`.

## Konfigurasi Penting (.env)
- `APP_URL`
- Database: `DB_CONNECTION` dan kredensial terkait
- Midtrans:
  - `MIDTRANS_SERVER_KEY`
  - `MIDTRANS_CLIENT_KEY`
  - `MIDTRANS_IS_PRODUCTION` (true atau false)
- Google OAuth:
  - `GOOGLE_CLIENT_ID`
  - `GOOGLE_CLIENT_SECRET`
  - `GOOGLE_REDIRECT_URI` (default `${APP_URL}/auth/google/callback`)

Jika key Midtrans belum diset, endpoint pembayaran akan mengembalikan pesan "Konfigurasi Midtrans belum disetel".

## Rute Ringkas
Public:
- GET `/`, `/paket`, `/paket/{id}`
- GET `/api/paket`, `/api/paket/{id}`, `/api/ratings`

Auth:
- GET `/login` (UI)
- POST `/login`, POST `/logout`
- GET `/auth/google/redirect`, GET `/auth/google/callback`
- GET `/api/me`

Customer (role: customer):
- GET `/pesanan-saya`
- POST `/pesan/{paketTour}`
- POST `/pesanan/{pesanan}/peserta`
- POST `/pesanan/{pesanan}/selesai`
- POST `/pesanan/{pesanan}/rating`
- POST `/payments/{pesanan}/snap-token`
- POST `/payments/{pesanan}/confirm`
- GET `/payments/{pesanan}/status`

Admin (role: admin):
- GET, POST, PUT, DELETE `/admin/paket`
- PUT `/admin/paket/{paketTour}/hide`, PUT `/admin/paket/{paketTour}/show`
- GET `/admin/pesanan`, `/admin/pesanan/{pesanan}`, `/admin/pesanan/{pesanan}/peserta`
- PUT `/admin/pesanan/{pesanan}/verify`, PUT `/admin/pesanan/{pesanan}/reject`

Owner (role: owner):
- GET `/owner/rekapitulasi?bulan=MM&tahun=YYYY`

## Struktur Direktori Utama
- `app/Http/Controllers` - logika backend.
- `app/Models` - model dan relasi.
- `routes/web.php` - rute web dan api.
- `resources/js` - SPA React.
- `resources/views/app.blade.php` - entry point SPA.
- `database/migrations` - skema database.

## Testing
- `php artisan test` atau `./vendor/bin/pest`

## Docker (Opsional)
Build dan jalankan:
- `docker build -t tour-web .`
- `docker run --rm -p 8080:8080 --env-file .env tour-web`

Aplikasi berjalan di `http://localhost:8080`.
