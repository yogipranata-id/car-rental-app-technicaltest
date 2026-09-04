# AGENT_BRIEF.md — Car Rental Web App (Web Programmer Technical Test)

Ini file konteks untuk AI coding agent (Claude Code, atau sejenisnya). Kalau tool-mu mengharapkan nama file tertentu (`CLAUDE.md`, `AGENTS.md`), tinggal copy/rename file ini — isinya tetap sama.

## Konteks
Aplikasi persewaan mobil untuk technical test posisi Web Programmer RSUD Indrasari Rengat, PT Jasamedika Saranatama (via Glints). Target: aplikasi yang benar-benar jalan dan bisa didemokan — bukan dokumen rencana.

**Deadline: Sabtu, 5 September 2026, pukul 23:00 WIB.**

## Tech stack (wajib — mengikuti deskripsi lowongan, bukan pilihan bebas)
- PHP + Laravel (backend, MVC)
- SQL Server, T-SQL **kompatibel SQL Server 2008** — WAJIB memakai Stored Procedure, Function, View, dan Trigger, bukan cuma tabel biasa
- AngularJS — WAJIB dipakai nyata di minimal satu fitur (bukan cuma disebut di README)
- Blade/Bootstrap untuk sisa UI yang tidak pakai Angular
- REST API, Git/GitHub
- **Catatan:** draft rencana sebelumnya memperlakukan AngularJS dan Stored Procedure/Function/View/Trigger sebagai opsional ("nice to have"). Di versi ini keduanya WAJIB, karena eksplisit diminta di deskripsi lowongan.

## Fitur wajib
1. **Registrasi** — nama, alamat, no. telepon, no. SIM, email, password (di-hash, jangan pernah plain text).
2. **Login/logout** — logout lalu login lagi pakai kredensial yang sama.
3. **Manajemen mobil (CRUD)** — merek, model, no. plat (unik), tarif sewa/hari (≥ 0).
4. **Pencarian mobil** — filter merek, model, ketersediaan.
5. **Peminjaman** — pilih mobil + tanggal mulai/selesai. WAJIB ditolak kalau bentrok dengan sewa aktif lain untuk mobil yang sama.
6. **My Rentals** — user lihat daftar sewa miliknya sendiri.
7. **Pengembalian** — input no. plat, verifikasi mobil itu disewa oleh user yang login, hitung durasi & biaya, simpan data pengembalian, ubah status sewa.
8. **Error handling** — pesan jelas untuk: tanggal tidak valid, mobil tidak tersedia, user tidak dikenal, plat/email duplikat, return oleh bukan pemilik sewa.

## Business rules
- Email unik, no. plat unik. `end_date >= start_date`.
- Aturan bentrok: mobil TIDAK tersedia kalau ada sewa `ACTIVE` dengan `existing_start <= requested_end AND existing_end >= requested_start`.
- `daily_rate` disalin ke baris `rentals` saat sewa dibuat (snapshot harga), supaya histori tidak berubah kalau tarif mobil di-update belakangan.
- Total biaya = jumlah hari × tarif harian. Konvensi hari: `DATEDIFF(day, start_date, end_date) + 1` (hari mulai & selesai dihitung inklusif) — pakai ini konsisten di semua tempat.
- Asumsi (boleh disesuaikan kalau evaluator minta lain): biaya final saat pengembalian = biaya estimasi saat pemesanan. Tidak ada penyesuaian untuk pengembalian lebih cepat/telat karena soal tidak menspesifikasikan itu.
- Hanya pemilik sewa yang boleh mengembalikan sewanya sendiri.
- Status: `ACTIVE` → `RETURNED`. Tidak ada status lain untuk MVP ini.

## Skema database — siap pakai
```sql
CREATE TABLE users (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(100) NOT NULL,
    email NVARCHAR(150) NOT NULL UNIQUE,
    password NVARCHAR(255) NOT NULL,
    address NVARCHAR(255) NOT NULL,
    phone NVARCHAR(30) NOT NULL,
    license_number NVARCHAR(50) NOT NULL,
    created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
    updated_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME()
);

CREATE TABLE cars (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    brand NVARCHAR(100) NOT NULL,
    model NVARCHAR(100) NOT NULL,
    license_plate NVARCHAR(30) NOT NULL UNIQUE,
    daily_rate DECIMAL(18,2) NOT NULL CHECK (daily_rate >= 0),
    created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
    updated_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME()
);

CREATE TABLE rentals (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    user_id BIGINT NOT NULL,
    car_id BIGINT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    daily_rate DECIMAL(18,2) NOT NULL CHECK (daily_rate >= 0),
    total_days INT NOT NULL CHECK (total_days > 0),
    estimated_cost DECIMAL(18,2) NOT NULL CHECK (estimated_cost >= 0),
    status NVARCHAR(20) NOT NULL DEFAULT 'ACTIVE' CHECK (status IN ('ACTIVE','RETURNED')),
    created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
    updated_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
    CONSTRAINT FK_rentals_users FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT FK_rentals_cars FOREIGN KEY (car_id) REFERENCES cars(id),
    CONSTRAINT CK_rentals_dates CHECK (end_date >= start_date)
);

CREATE TABLE returns (
    id BIGINT IDENTITY(1,1) PRIMARY KEY,
    rental_id BIGINT NOT NULL UNIQUE,
    returned_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
    total_days INT NOT NULL CHECK (total_days > 0),
    total_cost DECIMAL(18,2) NOT NULL CHECK (total_cost >= 0),
    created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
    updated_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
    CONSTRAINT FK_returns_rentals FOREIGN KEY (rental_id) REFERENCES rentals(id)
);

CREATE INDEX IX_rentals_car_status_dates ON rentals(car_id, status, start_date, end_date);
CREATE INDEX IX_rentals_user_status ON rentals(user_id, status);
CREATE INDEX IX_cars_brand_model ON cars(brand, model);
```

## Objek SQL Server tambahan — siap pakai (WAJIB ada, jangan dilewatkan)
Ditulis dan dicek manual untuk kompatibilitas SQL Server 2008 (pakai `RAISERROR`, bukan `THROW` yang baru ada di 2012+). Belum pernah dijalankan langsung ke instance SQL Server sungguhan — tetap tes saat migration jalan.

```sql
-- VIEW: gabungan sewa aktif + user + mobil, untuk "My Rentals" & dashboard
CREATE VIEW vw_active_rentals AS
SELECT
    r.id AS rental_id,
    u.id AS user_id,
    u.name AS renter_name,
    c.id AS car_id,
    c.brand,
    c.model,
    c.license_plate,
    r.start_date,
    r.end_date,
    r.daily_rate,
    r.total_days,
    r.estimated_cost,
    r.status,
    r.created_at
FROM rentals r
INNER JOIN users u ON u.id = r.user_id
INNER JOIN cars c ON c.id = r.car_id
WHERE r.status = 'ACTIVE';
GO

-- FUNCTION: hitung total biaya dari tanggal + tarif harian
CREATE FUNCTION fn_CalculateRentalCost
(
    @start_date DATE,
    @end_date DATE,
    @daily_rate DECIMAL(18,2)
)
RETURNS DECIMAL(18,2)
AS
BEGIN
    RETURN (DATEDIFF(DAY, @start_date, @end_date) + 1) * @daily_rate;
END;
GO

-- STORED PROCEDURE: cek ketersediaan + buat sewa dalam satu transaction
CREATE PROCEDURE sp_CreateRental
    @user_id BIGINT,
    @car_id BIGINT,
    @start_date DATE,
    @end_date DATE
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    IF @end_date < @start_date
    BEGIN
        RAISERROR('End date must be on or after start date.', 16, 1);
        RETURN;
    END

    BEGIN TRANSACTION;

    IF EXISTS (
        SELECT 1 FROM rentals WITH (UPDLOCK, HOLDLOCK)
        WHERE car_id = @car_id
          AND status = 'ACTIVE'
          AND start_date <= @end_date
          AND end_date >= @start_date
    )
    BEGIN
        ROLLBACK TRANSACTION;
        RAISERROR('Car is not available for the selected dates.', 16, 1);
        RETURN;
    END

    DECLARE @rate DECIMAL(18,2);
    SELECT @rate = daily_rate FROM cars WHERE id = @car_id;

    IF @rate IS NULL
    BEGIN
        ROLLBACK TRANSACTION;
        RAISERROR('Car not found.', 16, 1);
        RETURN;
    END

    DECLARE @days INT = DATEDIFF(DAY, @start_date, @end_date) + 1;
    DECLARE @cost DECIMAL(18,2) = dbo.fn_CalculateRentalCost(@start_date, @end_date, @rate);

    INSERT INTO rentals (user_id, car_id, start_date, end_date, daily_rate, total_days, estimated_cost, status)
    VALUES (@user_id, @car_id, @start_date, @end_date, @rate, @days, @cost, 'ACTIVE');

    DECLARE @new_id BIGINT = SCOPE_IDENTITY();
    COMMIT TRANSACTION;

    SELECT * FROM rentals WHERE id = @new_id;
END;
GO

-- STORED PROCEDURE: verifikasi kepemilikan + proses pengembalian
CREATE PROCEDURE sp_ReturnRental
    @license_plate NVARCHAR(30),
    @user_id BIGINT
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    BEGIN TRANSACTION;

    DECLARE @rental_id BIGINT, @days INT, @cost DECIMAL(18,2);

    SELECT
        @rental_id = r.id,
        @days = r.total_days,
        @cost = r.estimated_cost
    FROM rentals r WITH (UPDLOCK, HOLDLOCK)
    INNER JOIN cars c ON c.id = r.car_id
    WHERE c.license_plate = @license_plate
      AND r.status = 'ACTIVE'
      AND r.user_id = @user_id;

    IF @rental_id IS NULL
    BEGIN
        ROLLBACK TRANSACTION;
        RAISERROR('No active rental found for this plate under this user.', 16, 1);
        RETURN;
    END

    INSERT INTO returns (rental_id, total_days, total_cost)
    VALUES (@rental_id, @days, @cost);

    UPDATE rentals SET status = 'RETURNED' WHERE id = @rental_id;

    COMMIT TRANSACTION;

    SELECT * FROM returns WHERE rental_id = @rental_id;
END;
GO

-- TRIGGER: auto-update kolom updated_at tiap kali baris rentals di-UPDATE
CREATE TRIGGER trg_rentals_SetUpdatedAt
ON rentals
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE r
    SET updated_at = SYSUTCDATETIME()
    FROM rentals r
    INNER JOIN inserted i ON i.id = r.id;
END;
GO
```

`sp_CreateRental` dan `sp_ReturnRental` dipanggil dari Laravel lewat `DB::select()` (keduanya SELECT hasil di baris terakhir). CRUD mobil & auth tetap boleh pakai Eloquent biasa — tidak semua harus lewat stored procedure, cukup dua transaksi inti ini yang menunjukkan skill yang diminta.

## Arsitektur & konvensi kode
- Controller tipis — validasi lewat Form Request, delegasikan logic ke Service class (`RentalService` manggil kedua stored procedure di atas; `CarService` untuk CRUD & search biasa lewat Eloquent).
- Migration adalah source of truth, termasuk migration terpisah untuk 5 objek SQL Server di atas. **Penting:** `GO` di atas cuma pemisah batch untuk SSMS/sqlcmd, bukan T-SQL asli — driver PHP (`sqlsrv`/`pdo_sqlsrv`) akan error kalau menerima literal `GO`. Jalankan tiap `CREATE VIEW/FUNCTION/PROCEDURE/TRIGGER` sebagai `DB::statement()` terpisah, tanpa kata `GO`.
- `RAISERROR` di atas akan muncul di sisi PHP sebagai exception (PDOException/QueryException) karena severity-nya 16. Bungkus pemanggilan `DB::select()` ke kedua stored procedure dengan try/catch di `RentalService`, lalu terjemahkan jadi pesan error yang ramah untuk user.
- Password di-hash pakai `Hash::make()`. Jangan commit `.env`.
- Setiap input dari client divalidasi di Form Request, bukan cuma di JS.

## Koneksi SQL Server dari Laravel (.env) — catatan dari environment lokal
Environment: Laragon, PHP 8.5.10, Laravel 13, SQL Server 2022 Express + SSMS 22, driver PDO_SQLSRV (`php_pdo_sqlsrv_85_ts_x64.dll` + `php_sqlsrv_85_ts_x64.dll`).

- SQL Server 2022 di lokal tidak masalah walau lowongan minta 2008 — yang penting T-SQL yang dijalankan tetap kompatibel sintaks 2008 (sudah dipenuhi di bagian atas file ini, pakai `RAISERROR` bukan `THROW`).
- Cek driver benar-benar termuat sebelum lanjut: `php -m` harus menampilkan `pdo_sqlsrv` dan `sqlsrv`. Kalau tidak muncul, cek apakah build PHP di Laragon itu Thread-Safe (TS) atau Non-Thread-Safe (NTS) lewat `php -v` — DLL yang sudah diunduh (`_ts_` di nama file) cuma cocok untuk PHP build TS. Kalau PHP Laragon-nya NTS, unduh ulang varian `_nts_`.
- SSMS terhubung pakai Windows Authentication — untuk koneksi dari Laravel, disarankan pakai SQL Server Authentication (login terpisah) daripada Windows Authentication, supaya tidak tergantung identitas proses web server:
  1. SSMS → klik kanan nama server → Properties → Security → pastikan mode "SQL Server and Windows Authentication Mode" aktif (kalau masih "Windows Authentication Mode" saja, ganti lalu restart service SQL Server lewat SQL Server Configuration Manager).
  2. Security → Logins → New Login → buat username/password baru → beri akses ke database project ini (`db_owner` cukup untuk dev lokal).
  3. `.env`: `DB_CONNECTION=sqlsrv`, `DB_HOST=localhost\SQLEXPRESS`, `DB_PORT=1433`, `DB_DATABASE=<nama_db>`, `DB_USERNAME=<login_baru>`, `DB_PASSWORD=<password>`.
- SQL Server 2022 default mewajibkan koneksi terenkripsi. Kalau di SSMS sudah centang "Trust Server Certificate", opsi yang sama perlu diset juga di config `sqlsrv` Laravel (`config/database.php`), bukan cuma di SSMS — kalau tidak, kemungkinan muncul error sertifikat meskipun SSMS bisa konek normal.
- Test paling awal: `php artisan migrate:fresh` di project kosong (belum ada migration apapun). Kalau itu jalan tanpa error, koneksi sudah beres, baru lanjut isi migration tabel & 5 objek SQL Server di atas.
- Jangan beri nama Eloquent Model `Return` — itu reserved word di PHP juga. Pakai nama seperti `CarReturn` atau `RentalReturn`, dengan `protected $table = 'returns';`.

## Koneksi SQL Server dari Laravel (.env) — catatan dari environment lokal
Environment: Laragon, PHP 8.5.10, Laravel 13, SQL Server 2022 Express + SSMS 22, driver PDO_SQLSRV (`php_pdo_sqlsrv_85_ts_x64.dll` + `php_sqlsrv_85_ts_x64.dll`).

- SQL Server 2022 di lokal tidak masalah — yang penting T-SQL yang dijalankan tetap kompatibel sintaks 2008 (sudah dipenuhi di bagian atas file ini, pakai `RAISERROR` bukan `THROW`).
- Cek driver benar-benar termuat sebelum lanjut: `php -m` harus menampilkan `pdo_sqlsrv` dan `sqlsrv`. Kalau tidak muncul, cek lewat `php -v` apakah build PHP Laragon-nya Thread-Safe (TS) atau Non-Thread-Safe (NTS) — DLL yang sudah diunduh (`_ts_` di nama file) cuma cocok untuk PHP build TS. Kalau NTS, unduh ulang varian `_nts_`.
- SSMS terhubung pakai Windows Authentication — untuk koneksi dari Laravel, disarankan pakai SQL Server Authentication (login terpisah) daripada Windows Authentication, supaya tidak tergantung identitas proses web server:
  1. SSMS → klik kanan nama server → Properties → Security → pastikan mode "SQL Server and Windows Authentication Mode" aktif (kalau masih "Windows Authentication Mode" saja, ganti lalu restart service SQL Server lewat SQL Server Configuration Manager).
  2. Security → Logins → New Login → buat username/password baru → beri akses ke database project ini (`db_owner` cukup untuk dev lokal).
  3. `.env`: `DB_CONNECTION=sqlsrv`, `DB_HOST=localhost\SQLEXPRESS`, `DB_PORT=1433`, `DB_DATABASE=<nama_db>`, `DB_USERNAME=<login_baru>`, `DB_PASSWORD=<password>`.
- SQL Server 2022 default mewajibkan koneksi terenkripsi. Kalau di SSMS sudah dicentang "Trust Server Certificate", opsi yang sama perlu diset juga di config `sqlsrv` Laravel (`config/database.php`), bukan cuma di SSMS — kalau tidak, kemungkinan muncul error sertifikat meskipun SSMS bisa konek normal.
- Test paling awal: `php artisan migrate:fresh` di project kosong (belum ada migration apapun). Kalau itu jalan tanpa error, koneksi sudah beres, baru lanjut isi migration tabel & 5 objek SQL Server di atas.
- Jangan beri nama Eloquent Model `Return` — itu reserved word juga di PHP. Pakai nama seperti `CarReturn` atau `RentalReturn`, dengan `protected $table = 'returns';`.

## Urutan pengerjaan
1. **Fase 1 (hari ini, 3 Sept):** setup project Laravel + koneksi SQL Server → migration 4 tabel inti → migration 5 objek SQL Server di atas → model & relationship → seeder.
2. **Fase 2 (4 Sept):** auth → car CRUD & search → rental (lewat `sp_CreateRental`) → My Rentals (lewat `vw_active_rentals`) → return (lewat `sp_ReturnRental`) → REST API → komponen AngularJS di halaman cari/daftar mobil (konsumsi `GET /api/cars`).
3. **Fase 3 (5 Sept, sampai 23:00 WIB):** automated test (fokus ke aturan bentrok tanggal), manual QA, README + TOOLS.txt, bersihkan Git history, push, submit.

## Definition of done
- `php artisan migrate:fresh --seed` jalan tanpa error dari kondisi kosong.
- Rental yang bentrok tanggal ditolak; yang tidak bentrok diterima.
- Return hanya bisa oleh pemilik sewa, untuk sewa yang masih `ACTIVE`.
- `vw_active_rentals`, `fn_CalculateRentalCost`, `sp_CreateRental`, `sp_ReturnRental`, `trg_rentals_SetUpdatedAt` semuanya benar-benar dipanggil aplikasi — bukan cuma ada di database tanpa dipakai.
- Minimal satu halaman benar-benar pakai AngularJS untuk konsumsi REST API, bukan cuma Blade.
- README menjelaskan cara install, migrate, jalankan test, dan stack yang benar-benar dipakai.
