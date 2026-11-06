# InvoiceKu — Modul D-2

InvoiceKu adalah aplikasi web untuk mendukung proses administrasi kontrak komersial, termin pembayaran, dan penerbitan invoice pada ERP PT Anaheim Nimbus Universal.

Modul ini menghubungkan data kontrak dan termin dengan informasi proyek dari Modul D-1. Nilai pajak dihitung otomatis berdasarkan parameter pajak yang berlaku pada tanggal invoice.

## Fitur

- **Overview**: ringkasan proyek, kontrak aktif, invoice tertunda, pendapatan, dan aktivitas terbaru.
- **Proyek**: akses menu proyek dalam alur kerja Modul D-2.
- **Kontrak**: pencatatan kontrak komersial yang terhubung dengan proyek.
- **Termin**: menampilkan termin yang siap ditagih, nilai termin, tanggal jatuh tempo, serta status PO dari Modul D-1.
- **Invoice**: membuat invoice dari termin yang memenuhi syarat dan melihat detail invoice beserta riwayatnya.
- **Perhitungan pajak otomatis**: menghitung PPN, PPh, dan total tagihan berdasarkan parameter pajak aktif.
- **Validasi penagihan**: penerbitan invoice diblokir apabila PO belum tersedia, proyek tidak ditemukan, atau klien masih berstatus sementara.

## Teknologi

- PHP 8.3+
- Laravel 13
- SQLite
- Laravel Blade
- Tailwind CSS 4
- Vite

## Persyaratan

Pastikan perangkat telah memiliki:

- PHP 8.3 atau lebih baru
- Composer
- Node.js dan npm
- Ekstensi PHP SQLite

## Instalasi

1. Clone repository dan masuk ke direktori proyek.

   ```bash
   git clone https://github.com/eldyfg/PPL-D-2.git
   cd PPL-D-2
   ```

2. Pasang dependensi PHP dan JavaScript.

   ```bash
   composer install
   npm install
   ```

3. Siapkan file konfigurasi aplikasi.

   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

   Pada macOS atau Linux, gunakan `cp .env.example .env`.

4. Pastikan konfigurasi database pada `.env` menggunakan SQLite. Database bawaan tersedia di `database/database.sqlite`.

5. Jalankan migrasi dan data demo.

   ```bash
   php artisan migrate:fresh --seed
   ```

## Menjalankan aplikasi

Jalankan server Laravel:

```bash
php artisan serve
```

Jalankan Vite pada terminal lain agar aset frontend diperbarui saat pengembangan:

```bash
npm run dev
```

Buka aplikasi melalui [http://127.0.0.1:8000](http://127.0.0.1:8000).

Untuk membuat aset produksi:

```bash
npm run build
```

## Halaman web

| Halaman | URL | Keterangan |
| --- | --- | --- |
| Overview | `/` atau `/dashboard` | Ringkasan aktivitas dan metrik aplikasi |
| Proyek | `/projects` | Menu proyek |
| Kontrak | `/contracts` | Menu kontrak komersial |
| Termin | `/termins` | Daftar termin yang siap ditagih |
| Buat invoice | `/invoices` | Form penerbitan invoice dari termin |
| Detail invoice | `/invoices/{invoiceId}` | Detail invoice dan riwayat status |

## API

API menggunakan prefix `/api/v1` dan format respons JSON.

| Metode | Endpoint | Akses | Keterangan |
| --- | --- | --- | --- |
| `GET` | `/api/v1/termins` | Petugas Keuangan | Daftar termin siap ditagih dan status PO |
| `POST` | `/api/v1/invoices` | Petugas Keuangan | Menerbitkan invoice dari termin |
| `GET` | `/api/v1/invoices/{invoiceId}` | Petugas Keuangan | Mengambil detail invoice dan riwayat audit |
| `GET` | `/api/v1/tax-parameters/applicable` | Petugas Keuangan | Mengambil parameter pajak yang berlaku |
| `POST` | `/api/v1/contracts` | Admin, PM, AM/PM, atau Petugas Keuangan | Mencatat kontrak komersial |

Endpoint API membutuhkan header autentikasi berikut:

```http
Accept: application/json
Authorization: Bearer emp-demo-012
```

Pada konfigurasi demo, token `******` digunakan untuk autentikasi petugas keuangan. Identitas petugas yang tercatat pada invoice demo adalah `emp-demo-012`. Role dapat disimulasikan melalui header `X-User-Role` saat menguji endpoint yang membutuhkan peran tertentu.

### Contoh request membuat invoice

```http
POST /api/v1/invoices
Content-Type: application/json
Authorization: Bearer emp-demo-012
```

```json
{
  "terminId": "trm-demo-001",
  "invoiceDate": "2026-10-28",
  "dppAmount": 100000000
}
```

## Data demo

Seeder menyediakan data untuk mencoba alur utama aplikasi, antara lain:

- kontrak `KTR/2026/001`, `KTR/2026/004`, dan `KTR/2026/005`;
- termin dengan status `SIAP_DITAGIH`;
- parameter pajak PPN 11% dan PPh 2%;
- invoice contoh `INV-2026-SAMPLE`;
- skenario proyek dengan PO tersedia dan PO yang belum tersedia.

Jalankan ulang seeder apabila ingin mengembalikan database ke kondisi demo:

```bash
php artisan migrate:fresh --seed
```

## Contoh respons dan spesifikasi OpenAPI

Dokumentasi API tersedia dalam:

- [openapi.yaml](openapi.yaml)
- [openapi.json](openapi.json)

Contoh payload dan respons tersedia di folder [`examples/`](examples/), termasuk skenario invoice berhasil, validasi gagal, akses ditolak, dan penagihan yang diblokir karena PO belum tersedia.

## Pengujian

Jalankan seluruh pengujian Laravel dengan:

```bash
php artisan test
```

Pengujian fitur API berada di [`tests/Feature/`](tests/Feature/), sedangkan pengujian unit berada di [`tests/Unit/`](tests/Unit/).

## Struktur direktori utama

```text
app/
├── Http/             Controller, middleware, dan request validation
├── Models/           Model kontrak, termin, invoice, dan pajak
└── Services/         Integrasi Modul D-1 dan kalkulasi pajak
database/
├── migrations/       Struktur tabel aplikasi
└── seeders/          Data demo Modul D-2
resources/
├── js/               Interaksi halaman dan API client
├── css/              Styling aplikasi
└── views/            Template Blade InvoiceKu
routes/
├── web.php           Route halaman web
└── api.php           Route API Modul D-2
```

## Catatan pengembangan

Perubahan pada file frontend selama pengembangan akan diproses oleh Vite. Sebelum melakukan pengujian dari awal, pastikan migrasi dan seeder telah dijalankan agar data kontrak, termin, pajak, dan invoice tersedia.
