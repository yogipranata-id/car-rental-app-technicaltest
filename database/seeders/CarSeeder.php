<?php

namespace Database\Seeders;

use App\Models\Car;
use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
    public function run(): void
    {
        $cars = [
            ['brand' => 'Toyota', 'model' => 'Avanza', 'license_plate' => 'BM 1234 XY', 'daily_rate' => 300000],
            ['brand' => 'Honda', 'model' => 'Brio', 'license_plate' => 'BM 5678 AB', 'daily_rate' => 250000],
            ['brand' => 'Toyota', 'model' => 'Innova', 'license_plate' => 'BM 9012 CD', 'daily_rate' => 450000],
            ['brand' => 'Daihatsu', 'model' => 'Xenia', 'license_plate' => 'BM 3456 EF', 'daily_rate' => 280000],
        ];

        foreach ($cars as $car) {
            Car::create($car);
        }
    }
}
