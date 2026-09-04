@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white"><h4 class="mb-0">Return a Car</h4></div>
            <div class="card-body p-4">
                <p class="mb-4">Enter the license plate number of the car you wish to return. Note that you can only return cars that you have actively rented.</p>
                
                <form method="POST" action="{{ route('returns.store') }}">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="license_plate" class="form-label fw-bold">License Plate Number</label>
                        <input type="text" class="form-control form-control-lg text-uppercase @error('license_plate') is-invalid @enderror" id="license_plate" name="license_plate" value="{{ old('license_plate', $plate ?? '') }}" placeholder="e.g. BM 1234 AB" required autofocus>
                        @error('license_plate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-dark btn-lg w-100">Process Return</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
