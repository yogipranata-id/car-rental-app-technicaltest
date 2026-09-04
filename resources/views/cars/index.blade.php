@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Cars Management</h2>
    <a href="{{ route('cars.create') }}" class="btn btn-primary">Add New Car</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('cars.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="brand" class="form-control" placeholder="Search Brand..." value="{{ request('brand') }}">
            </div>
            <div class="col-md-4">
                <input type="text" name="model" class="form-control" placeholder="Search Model..." value="{{ request('model') }}">
            </div>
            <div class="col-md-2">
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="available" id="available" value="1" {{ request('available') ? 'checked' : '' }}>
                    <label class="form-check-label" for="available">
                        Available Today Only
                    </label>
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">Search</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Brand & Model</th>
                        <th>License Plate</th>
                        <th>Daily Rate</th>
                        <th>Status (Today)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cars as $car)
                        <tr>
                            <td>{{ $car->brand }} {{ $car->model }}</td>
                            <td><span class="badge bg-secondary">{{ $car->license_plate }}</span></td>
                            <td>Rp {{ number_format($car->daily_rate, 0, ',', '.') }}</td>
                            <td>
                                @if($car->is_available)
                                    <span class="badge bg-success">Available</span>
                                @else
                                    <span class="badge bg-danger">Rented</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('rentals.create', ['car_id' => $car->id]) }}" class="btn btn-primary">Book</a>
                                    <a href="{{ route('cars.edit', $car) }}" class="btn btn-outline-secondary">Edit</a>
                                    <form action="{{ route('cars.destroy', $car) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this car?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger border-start-0 rounded-start-0">Del</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">No cars found matching your criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($cars->hasPages())
        <div class="card-footer bg-white border-top-0 pt-3">
            {{ $cars->links() }}
        </div>
    @endif
</div>
@endsection
