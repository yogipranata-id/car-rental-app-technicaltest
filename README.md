# Car Rental Web App

Aplikasi persewaan mobil berbasis web, dibangun sebagai _technical test_ untuk posisi Web Programmer.

## Tech Stack

- **Backend:** PHP 8.5, Laravel 13
- **Database:** SQL Server 2022 Express (T-SQL kompatibel dengan SQL Server 2008+)
- **REST API:** Endpoint JSON internal untuk melayani _Client-Side Rendering_
- **Frontend:** Blade + Bootstrap 5 + AngularJS 1.8 (Hybrid Architecture)
- **UI/UX Design:** Custom "Premium Taste" CSS (menimpa default Bootstrap)
- **Database Objects:** Stored Procedure, Function, View, Trigger (dijalankan murni via Laravel Migration)

## Fitur Utama

1. **Registrasi & Login/Logout** — Menggunakan sistem _Custom Auth_ (tanpa Jetstream/Breeze) untuk fleksibilitas validasi data spesifik (Alamat, No Telepon, No SIM).
2. **Manajemen Mobil (CRUD)** — Fungsi standar _tambah, edit, hapus, dan daftar mobil_.
3. **Pencarian Mobil (Seamless AngularJS)** — Filter merek, model, dan ketersediaan dilakukan tanpa _reload_ halaman. Fitur ini diinjeksi dengan AngularJS ke dalam tampilan Blade, mengonsumsi REST API secara dinamis.
4. **Peminjaman Mobil (Anti-Race Condition)** — Semua proses cek bentrok tanggal dan transaksi insert didelegasikan ke Stored Procedure `sp_CreateRental`. Jika tanggal bentrok, SQL Server mengembalikan `RAISERROR`.
5. **My Rentals** — Menampilkan daftar sewa aktif menggunakan SQL View `vw_active_rentals`.
6. **Pengembalian Mobil** — Proses kalkulasi total biaya akhir dilakukan via Stored Procedure `sp_ReturnRental` dengan verifikasi kepemilikan.
7. **REST API** — Tersedia endpoint `GET /api/cars` yang merespons dengan JSON standar untuk melayani aplikasi Frontend.

## Objek SQL Server yang Diimplementasikan

| Jenis Objek          | Nama                       | Fungsi Utama                                                           |
| -------------------- | -------------------------- | ---------------------------------------------------------------------- |
| **View**             | `vw_active_rentals`        | Menyajikan data gabungan (JOIN) khusus penyewaan yang berstatus aktif. |
| **Function**         | `fn_CalculateRentalCost`   | Mengkalkulasi biaya harian (_daily rate_ x _durasi hari_).             |
| **Stored Procedure** | `sp_CreateRental`          | Menangani transaksi rental dan perlindungan dari _overlapping dates_.  |
| **Stored Procedure** | `sp_ReturnRental`          | Menangani pengembalian mobil dan kalkulasi biaya final.                |
| **Trigger**          | `trg_rentals_SetUpdatedAt` | Otomatis memperbarui kolom `updated_at` di tabel `rentals`.            |

## Instalasi

### Prasyarat

- PHP 8.x dengan ekstensi `pdo_sqlsrv` dan `sqlsrv` aktif
- Composer
- SQL Server (2008+ kompatibel, direkomendasikan 2022 Express)
- ODBC Driver 17+ for SQL Server

### Langkah-langkah

```bash
# 1. Clone repo
git clone https://github.com/yogipranata-id/car-rental-app-technicaltest.git
cd car-rental-app-technicaltest

# 2. Install dependencies
composer install

# 3. Salin .env dan konfigurasi key
cp .env.example .env
php artisan key:generate

# 4. Edit file .env — sesuaikan koneksi database SQL Server Anda:
# DB_CONNECTION=sqlsrv
# DB_HOST=localhost\SQLEXPRESS
# DB_PORT=1433
# DB_DATABASE=car_rental
# DB_USERNAME=<user>
# DB_PASSWORD=<password>
# DB_TRUST_SERVER_CERTIFICATE=true

# 5. Buat database kosong di SQL Server (via SSMS / Azure Data Studio):
# CREATE DATABASE car_rental;

# 6. Jalankan migration (termasuk objek SQL) dan seeder
php artisan migrate:fresh --seed

# 7. Jalankan aplikasi
php artisan serve
```

Akses aplikasi di `http://localhost:8000`.

## Menjalankan Test

```bash
php artisan test --filter=RentalConflictTest
```

> **Catatan:** Test berjalan **langsung ke SQL Server (Live DB)** (bukan SQLite di memori), karena fokus pengujiannya adalah memastikan Stored Procedure (`sp_CreateRental`) menolak irisan tanggal dengan benar. Pastikan koneksi database di `.env` sudah benar.

## Akun Demo (dari Seeder)

| Email            | Password |
| ---------------- | -------- |
| test@example.com | password |

_(Anda juga bisa membuat akun baru lewat halaman Register)._

## Struktur Folder Penting

```
app/
├── Http/Controllers/       # Controller (Auth, Car, Rental, Return, API)
├── Models/                 # Eloquent models
└── Services/               # RentalService (Menangkap PDOException dari SP)
database/
├── migrations/             # Struktur Tabel + Objek SQL Server Murni
└── seeders/                # User & Car Data Seeder
resources/views/
├── layouts/app.blade.php   # Master layout (Injeksi CSS & Font)
├── cars/                   # Integrasi AngularJS & Blade Index
└── rentals/, returns/      # Form booking, history, dan return receipt
public/css/
└── style.css               # Custom Premium CSS
```
