<?php

namespace App\Http\Controllers;

use App\Http\Requests\RentalRequest;
use App\Models\Car;
use App\Services\RentalService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RentalController extends Controller
{
    protected RentalService $rentalService;

    public function __construct(RentalService $rentalService)
    {
        $this->rentalService = $rentalService;
    }

    public function create(Request $request)
    {
        $carId = $request->get('car_id');
        $car = $carId ? Car::findOrFail($carId) : null;
        $cars = Car::orderBy('brand')->orderBy('model')->get();
        
        return view('rentals.create', compact('car', 'cars'));
    }

    public function store(RentalRequest $request)
    {
        try {
            $this->rentalService->createRental(
                Auth::id(),
                $request->car_id,
                $request->start_date,
                $request->end_date
            );

            return redirect()->route('rentals.my')->with('success', 'Rental booked successfully!');
            
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function myRentals()
    {
        $userId = Auth::id();
        
        // Active rentals via the required view
        $activeRentals = DB::table('vw_active_rentals')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Returned rentals via Eloquent
        $returnedRentals = Auth::user()->rentals()
            ->with(['car', 'carReturn'])
            ->where('status', 'RETURNED')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('rentals.my-rentals', compact('activeRentals', 'returnedRentals'));
    }
}
