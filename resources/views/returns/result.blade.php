@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill flex-shrink-0 me-2" style="font-size: 1.5rem;"></i>
            <div>
                <strong>Return Successful!</strong> The car has been returned successfully.
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h4 class="mb-0">Return Receipt</h4>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Car Details:</div>
                    <div class="col-sm-8 fw-bold">
                        {{ $carReturn->rental->car->brand }} {{ $carReturn->rental->car->model }} 
                        <span class="badge bg-secondary ms-1">{{ $carReturn->rental->car->license_plate }}</span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Rented By:</div>
                    <div class="col-sm-8">
                        {{ $carReturn->rental->user->name }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Rental Period:</div>
                    <div class="col-sm-8">
                        {{ $carReturn->rental->start_date->format('d M Y') }} to {{ $carReturn->rental->end_date->format('d M Y') }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Returned At:</div>
                    <div class="col-sm-8">
                        {{ $carReturn->returned_at->format('d M Y, H:i:s') }}
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Duration:</div>
                    <div class="col-sm-8">
                        {{ $carReturn->total_days }} days
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-4 text-muted fw-bold">Total Cost:</div>
                    <div class="col-sm-8 fw-bold text-success" style="font-size: 1.25rem;">
                        Rp {{ number_format($carReturn->total_cost, 0, ',', '.') }}
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light text-center py-3">
                <a href="{{ route('rentals.my') }}" class="btn btn-primary me-2">Back to My Rentals</a>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Go to Dashboard</a>
            </div>
        </div>
    </div>
</div>
@endsection
