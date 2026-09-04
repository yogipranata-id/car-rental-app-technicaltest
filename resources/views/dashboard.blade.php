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
        <div class="card bg-primary text-white h-100 shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-car-front"></i> Browse Cars</h5>
                <p class="card-text">View available cars for rent and book your next trip.</p>
                <a href="{{ route('cars.index') }}" class="btn btn-light mt-2">View Cars</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card bg-success text-white h-100 shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-card-checklist"></i> My Rentals</h5>
                <p class="card-text">Check your active and past rentals.</p>
                <a href="{{ route('rentals.my') }}" class="btn btn-light mt-2">View History</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card bg-warning text-dark h-100 shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-arrow-return-left"></i> Return Car</h5>
                <p class="card-text">Finished with your rental? Return it here.</p>
                <a href="{{ route('returns.create') }}" class="btn btn-dark mt-2">Return Car</a>
            </div>
        </div>
    </div>
</div>
@endsection
