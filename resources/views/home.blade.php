@extends('layouts.app')

@section('content')
<div class="p-5 mb-4 bg-light rounded-3 border">
    <div class="container-fluid py-5">
        <h1 class="display-5 fw-bold">Welcome to Car Rental App</h1>
        <p class="col-md-8 fs-4">Rent your dream car today. We offer the best prices and a wide selection of vehicles for your travel needs.</p>
        
        @guest
            <div class="mt-4">
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-4 me-md-2">Register Now</a>
                <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-lg px-4">Login</a>
            </div>
        @else
            <div class="mt-4">
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg px-4 me-md-2">Go to Dashboard</a>
                <a href="{{ route('cars.index') }}" class="btn btn-outline-secondary btn-lg px-4">Browse Cars</a>
            </div>
        @endguest
    </div>
</div>
@endsection
