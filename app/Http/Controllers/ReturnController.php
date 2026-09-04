<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReturnRequest;
use App\Models\CarReturn;
use App\Services\RentalService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReturnController extends Controller
{
    protected RentalService $rentalService;

    public function __construct(RentalService $rentalService)
    {
        $this->rentalService = $rentalService;
    }

    public function create(Request $request)
    {
        $plate = $request->get('plate');
        return view('returns.create', compact('plate'));
    }

    public function store(ReturnRequest $request)
    {
        try {
            // Process return via stored procedure
            $result = $this->rentalService->returnRental(
                $request->license_plate,
                Auth::id()
            );

            // Stored procedure returns the created 'returns' record (with id, rental_id, total_days, total_cost, etc)
            // But it's returned as a stdClass object from DB::select()
            // We can fetch the Eloquent model using the rental_id for the result view
            $carReturn = CarReturn::with(['rental', 'rental.car', 'rental.user'])
                ->where('rental_id', $result->rental_id)
                ->firstOrFail();

            return view('returns.result', compact('carReturn'));
            
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
