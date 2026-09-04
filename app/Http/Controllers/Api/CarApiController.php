<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarApiController extends Controller
{
    /**
     * Get list of cars with optional filters for AngularJS frontend
     */
    public function index(Request $request)
    {
        $query = Car::query();

        if ($request->filled('brand')) {
            $query->where('brand', 'like', '%' . $request->brand . '%');
        }

        if ($request->filled('model')) {
            $query->where('model', 'like', '%' . $request->model . '%');
        }

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

        $cars = $query->orderBy('brand')->orderBy('model')->get();

        return response()->json([
            'status' => 'success',
            'data' => $cars
        ]);
    }
}
