<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\User;
use App\Services\RentalService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Tests the date-conflict business rule enforced by sp_CreateRental.
 * Runs against the real SQL Server database (stored procedures don't work on SQLite).
 */
class RentalConflictTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Car $car;
    protected RentalService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->car = Car::create([
            'brand' => 'Test',
            'model' => 'Car',
            'license_plate' => 'TEST-' . uniqid(),
            'daily_rate' => 100000,
        ]);
        $this->service = app(RentalService::class);
    }

    public function test_rental_succeeds_when_no_conflict(): void
    {
        $result = $this->service->createRental(
            $this->user->id,
            $this->car->id,
            '2026-10-01',
            '2026-10-03'
        );

        $this->assertEquals($this->car->id, $result->car_id);
        $this->assertEquals('ACTIVE', $result->status);
    }

    public function test_rental_rejected_when_dates_overlap_exactly(): void
    {
        // First rental: Oct 1-5
        $this->service->createRental($this->user->id, $this->car->id, '2026-10-01', '2026-10-05');

        // Same dates should conflict
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Car is not available for the selected dates.');
        $this->service->createRental($this->user->id, $this->car->id, '2026-10-01', '2026-10-05');
    }

    public function test_rental_rejected_when_new_overlaps_start(): void
    {
        // Existing: Oct 5-10
        $this->service->createRental($this->user->id, $this->car->id, '2026-10-05', '2026-10-10');

        // New: Oct 3-7 overlaps the start
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Car is not available for the selected dates.');
        $this->service->createRental($this->user->id, $this->car->id, '2026-10-03', '2026-10-07');
    }

    public function test_rental_rejected_when_new_overlaps_end(): void
    {
        // Existing: Oct 1-5
        $this->service->createRental($this->user->id, $this->car->id, '2026-10-01', '2026-10-05');

        // New: Oct 4-8 overlaps the end
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Car is not available for the selected dates.');
        $this->service->createRental($this->user->id, $this->car->id, '2026-10-04', '2026-10-08');
    }

    public function test_rental_rejected_when_new_encloses_existing(): void
    {
        // Existing: Oct 3-5
        $this->service->createRental($this->user->id, $this->car->id, '2026-10-03', '2026-10-05');

        // New: Oct 1-10 fully encloses existing
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Car is not available for the selected dates.');
        $this->service->createRental($this->user->id, $this->car->id, '2026-10-01', '2026-10-10');
    }

    public function test_rental_succeeds_when_dates_do_not_overlap(): void
    {
        // First: Oct 1-3
        $this->service->createRental($this->user->id, $this->car->id, '2026-10-01', '2026-10-03');

        // Second: Oct 4-6 — no overlap, should succeed
        $result = $this->service->createRental($this->user->id, $this->car->id, '2026-10-04', '2026-10-06');

        $this->assertEquals($this->car->id, $result->car_id);
        $this->assertEquals('ACTIVE', $result->status);
    }

    public function test_end_date_before_start_date_rejected(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('End date must be on or after start date.');
        $this->service->createRental($this->user->id, $this->car->id, '2026-10-05', '2026-10-01');
    }
}
