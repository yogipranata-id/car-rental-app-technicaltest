# Car Rental Web App

Aplikasi persewaan mobil berbasis web, dibangun sebagai technical test posisi Web Programmer.

## Tech Stack

- **Backend:** PHP 8.5, Laravel 13
- **Database:** SQL Server 2022 Express (T-SQL kompatibel SQL Server 2008)
- **Frontend:** Blade + Bootstrap 5 (UI utama), AngularJS 1.8 (halaman pencarian mobil)
- **Database Objects:** Stored Procedure, Function, View, Trigger (dijalankan via migration)

## Fitur

1. **Registrasi & Login/Logout** — dengan validasi server-side dan password hashing
2. **Manajemen Mobil (CRUD)** — tambah, edit, hapus, daftar mobil
3. **Pencarian Mobil** — filter merek, model, ketersediaan (Blade & AngularJS)
4. **Peminjaman Mobil** — lewat stored procedure `sp_CreateRental` dengan cek bentrok tanggal
5. **My Rentals** — lewat SQL View `vw_active_rentals` + Eloquent
6. **Pengembalian Mobil** — lewat stored procedure `sp_ReturnRental` dengan verifikasi kepemilikan
7. **REST API** — `GET /api/cars` dikonsumsi oleh komponen AngularJS

## Objek SQL Server yang Dipakai

| Objek | Nama | Dipakai Di |
|---|---|---|
| View | `vw_active_rentals` | Halaman My Rentals (sewa aktif) |
| Function | `fn_CalculateRentalCost` | Dipanggil oleh `sp_CreateRental` |
| Stored Procedure | `sp_CreateRental` | `RentalService::createRental()` |
| Stored Procedure | `sp_ReturnRental` | `RentalService::returnRental()` |
| Trigger | `trg_rentals_SetUpdatedAt` | Auto-update `updated_at` saat status rental berubah |

## Instalasi

### Prasyarat
- PHP 8.x dengan extension `pdo_sqlsrv` dan `sqlsrv`
- Composer
- SQL Server (2008+ kompatibel, tested di 2022 Express)
- ODBC Driver 17+ for SQL Server

### Langkah

```bash
# 1. Clone repo
git clone https://github.com/yogipranata-id/car-rental-app-technicaltest.git
cd car-rental-app-technicaltest

# 2. Install dependencies
composer install

# 3. Salin .env dan konfigurasi
cp .env.example .env
php artisan key:generate

# 4. Edit .env — sesuaikan koneksi database:
# DB_CONNECTION=sqlsrv
# DB_HOST=localhost\SQLEXPRESS
# DB_PORT=1433
# DB_DATABASE=car_rental
# DB_USERNAME=<user>
# DB_PASSWORD=<password>
# DB_TRUST_SERVER_CERTIFICATE=true

# 5. Buat database di SQL Server (via SSMS atau sqlcmd):
# CREATE DATABASE car_rental;

# 6. Jalankan migration dan seeder
php artisan migrate:fresh --seed

# 7. Jalankan aplikasi
php artisan serve
```

Akses di `http://localhost:8000`.

## Menjalankan Test

```bash
php artisan test --filter=RentalConflictTest
```

> **Catatan:** Test berjalan langsung ke SQL Server (bukan SQLite), karena menguji stored procedure. Pastikan koneksi database `.env` sudah benar.

## Akun Demo (dari seeder)

| Email | Password |
|---|---|
| test@example.com | password |

## Struktur Folder Penting

```
app/
├── Http/Controllers/       # Controller (Auth, Car, Rental, Return, API)
├── Http/Requests/           # Form Request validation
├── Models/                  # Eloquent models
└── Services/                # RentalService (panggil SP)
database/
├── migrations/              # Tabel + objek SQL Server
├── seeders/                 # User & Car seeder
└── factories/               # UserFactory
resources/views/
├── layouts/app.blade.php    # Master layout Bootstrap 5
├── auth/                    # Login & Register
├── cars/                    # CRUD + AngularJS search
├── rentals/                 # Booking & My Rentals
└── returns/                 # Return car + receipt
tests/Feature/
└── RentalConflictTest.php   # Test aturan bentrok tanggal
```
