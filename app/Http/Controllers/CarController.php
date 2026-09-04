<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarRequest;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarController extends Controller
{
    public function index(Request $request)
    {
        $query = Car::query();

        // Search by brand
        if ($request->filled('brand')) {
            $query->where('brand', 'like', '%' . $request->brand . '%');
        }

        // Search by model
        if ($request->filled('model')) {
            $query->where('model', 'like', '%' . $request->model . '%');
        }

        // Filter by availability (if checked, show cars that are NOT currently active in rentals)
        // Using raw SQL for the availability check based on the business rule:
        // Car is NOT available if there is an ACTIVE rental where start_date <= TODAY and end_date >= TODAY
        // But the brief says "pencarian ketersediaan", we can simplify it for the view by showing 
        // cars that don't have an ACTIVE rental at the current moment, OR we can just join and show availability status.
        // Let's get all cars and calculate their current availability status for the view.
        
        $today = now()->format('Y-m-d');
        
        $query->select('cars.*', DB::raw("
            CASE WHEN EXISTS (
                SELECT 1 FROM rentals 
                WHERE rentals.car_id = cars.id 
                AND rentals.status = 'ACTIVE' 
                AND rentals.start_date <= '$today' 
                AND rentals.end_date >= '$today'
            ) THEN 0 ELSE 1 END as is_available
        "));

        if ($request->filled('available') && $request->available == 1) {
            $query->whereNotExists(function($q) use ($today) {
                $q->select(DB::raw(1))
                  ->from('rentals')
                  ->whereColumn('rentals.car_id', 'cars.id')
                  ->where('rentals.status', 'ACTIVE')
                  ->where('rentals.start_date', '<=', $today)
                  ->where('rentals.end_date', '>=', $today);
            });
        }

        $cars = $query->orderBy('brand')->orderBy('model')->paginate(10);

        return view('cars.index', compact('cars'));
    }

    public function create()
    {
        return view('cars.create');
    }

    public function store(CarRequest $request)
    {
        Car::create($request->validated());

        return redirect()->route('cars.index')->with('success', 'Car added successfully.');
    }

    public function edit(Car $car)
    {
        return view('cars.edit', compact('car'));
    }

    public function update(CarRequest $request, Car $car)
    {
        $car->update($request->validated());

        return redirect()->route('cars.index')->with('success', 'Car updated successfully.');
    }

    public function destroy(Car $car)
    {
        // Simple check if car has rentals before deleting
        if ($car->rentals()->exists()) {
            return back()->with('error', 'Cannot delete car because it has rental history.');
        }
        
        $car->delete();

        return redirect()->route('cars.index')->with('success', 'Car deleted successfully.');
    }
}
