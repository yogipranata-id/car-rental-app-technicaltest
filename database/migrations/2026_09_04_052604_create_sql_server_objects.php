<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
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
            WHERE r.status = 'ACTIVE'
        ");

        DB::statement("
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
            END
        ");

        DB::statement("
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

                INSERT INTO rentals (user_id, car_id, start_date, end_date, daily_rate, total_days, estimated_cost, status, created_at, updated_at)
                VALUES (@user_id, @car_id, @start_date, @end_date, @rate, @days, @cost, 'ACTIVE', SYSUTCDATETIME(), SYSUTCDATETIME());

                DECLARE @new_id BIGINT = SCOPE_IDENTITY();
                COMMIT TRANSACTION;

                SELECT * FROM rentals WHERE id = @new_id;
            END
        ");

        DB::statement("
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

                INSERT INTO returns (rental_id, returned_at, total_days, total_cost, created_at, updated_at)
                VALUES (@rental_id, SYSUTCDATETIME(), @days, @cost, SYSUTCDATETIME(), SYSUTCDATETIME());

                UPDATE rentals SET status = 'RETURNED' WHERE id = @rental_id;

                COMMIT TRANSACTION;

                SELECT * FROM returns WHERE rental_id = @rental_id;
            END
        ");

        DB::statement("
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
            END
        ");
    }

    public function down(): void
    {
        DB::statement("IF OBJECT_ID('trg_rentals_SetUpdatedAt', 'TR') IS NOT NULL DROP TRIGGER trg_rentals_SetUpdatedAt");
        DB::statement("IF OBJECT_ID('sp_ReturnRental', 'P') IS NOT NULL DROP PROCEDURE sp_ReturnRental");
        DB::statement("IF OBJECT_ID('sp_CreateRental', 'P') IS NOT NULL DROP PROCEDURE sp_CreateRental");
        DB::statement("IF OBJECT_ID('fn_CalculateRentalCost', 'FN') IS NOT NULL DROP FUNCTION fn_CalculateRentalCost");
        DB::statement("IF OBJECT_ID('vw_active_rentals', 'V') IS NOT NULL DROP VIEW vw_active_rentals");
    }
};