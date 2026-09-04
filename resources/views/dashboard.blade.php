@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h2>Dashboard</h2>
        <p class="text-muted">Welcome back, {{ Auth::user()->name }}!</p>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0">
            <div class="card-body p-4 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 64px; height: 64px;">
                    <i class="bi bi-car-front fs-2"></i>
                </div>
                <h5 class="card-title fw-bold">Browse Cars</h5>
                <p class="card-text text-muted mb-4">View available cars for rent and book your next trip today.</p>
                <a href="{{ route('cars.index') }}" class="btn btn-primary w-100">View Cars</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0">
            <div class="card-body p-4 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle mb-3" style="width: 64px; height: 64px;">
                    <i class="bi bi-card-checklist fs-2"></i>
                </div>
                <h5 class="card-title fw-bold">My Rentals</h5>
                <p class="card-text text-muted mb-4">Check your active and past rentals, including total costs.</p>
                <a href="{{ route('rentals.my') }}" class="btn btn-outline-secondary w-100">View History</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0">
            <div class="card-body p-4 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-dark bg-opacity-10 text-dark rounded-circle mb-3" style="width: 64px; height: 64px;">
                    <i class="bi bi-arrow-return-left fs-2"></i>
                </div>
                <h5 class="card-title fw-bold">Return Car</h5>
                <p class="card-text text-muted mb-4">Finished with your rental? Process your car return here.</p>
                <a href="{{ route('returns.create') }}" class="btn btn-dark w-100">Return Car</a>
            </div>
        </div>
    </div>
</div>
@endsection
