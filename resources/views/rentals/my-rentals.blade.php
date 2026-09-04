@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>My Rentals</h2>
    <a href="{{ route('cars.index') }}" class="btn btn-primary">Book Another Car</a>
</div>

<ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab">Active Rentals</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">Rental History</button>
    </li>
</ul>

<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="active" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Car</th>
                                <th>License Plate</th>
                                <th>Rental Period</th>
                                <th>Duration</th>
                                <th>Estimated Cost</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeRentals as $rental)
                                <tr>
                                    <td>{{ $rental->brand }} {{ $rental->model }}</td>
                                    <td><span class="badge bg-secondary">{{ $rental->license_plate }}</span></td>
                                    <td>{{ date('d M Y', strtotime($rental->start_date)) }} - {{ date('d M Y', strtotime($rental->end_date)) }}</td>
                                    <td>{{ $rental->total_days }} days</td>
                                    <td>Rp {{ number_format($rental->estimated_cost, 0, ',', '.') }}</td>
                                    <td>
                                        <a href="{{ route('returns.create', ['plate' => $rental->license_plate]) }}" class="btn btn-sm btn-dark">Return Car</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">You have no active rentals.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="tab-pane fade" id="history" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Car</th>
                                <th>License Plate</th>
                                <th>Rental Period</th>
                                <th>Returned On</th>
                                <th>Final Cost</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($returnedRentals as $rental)
                                <tr>
                                    <td>{{ $rental->car->brand }} {{ $rental->car->model }}</td>
                                    <td><span class="badge bg-secondary">{{ $rental->car->license_plate }}</span></td>
                                    <td>{{ $rental->start_date->format('d M Y') }} - {{ $rental->end_date->format('d M Y') }}</td>
                                    <td>
                                        @if($rental->carReturn)
                                            {{ $rental->carReturn->returned_at->format('d M Y H:i') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if($rental->carReturn)
                                            Rp {{ number_format($rental->carReturn->total_cost, 0, ',', '.') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td><span class="badge bg-success">RETURNED</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">No rental history found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
