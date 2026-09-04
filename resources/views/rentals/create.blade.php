@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h4 class="mb-0">Book a Car</h4></div>
            <div class="card-body">
                <form method="POST" action="{{ route('rentals.store') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="car_id" class="form-label">Select Car</label>
                        <select class="form-select @error('car_id') is-invalid @enderror" id="car_id" name="car_id" required>
                            <option value="">-- Choose a Car --</option>
                            @foreach($cars as $c)
                                <option value="{{ $c->id }}" data-rate="{{ $c->daily_rate }}" {{ (old('car_id', $car?->id) == $c->id) ? 'selected' : '' }}>
                                    {{ $c->brand }} {{ $c->model }} ({{ $c->license_plate }}) - Rp {{ number_format($c->daily_rate, 0, ',', '.') }}/day
                                </option>
                            @endforeach
                        </select>
                        @error('car_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', date('Y-m-d', strtotime('+1 day'))) }}" required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <strong>Estimated Cost:</strong> <span id="estimated_cost">Rp 0</span>
                        <div class="small text-muted">Cost is calculated based on the selected dates (inclusive) and the car's daily rate.</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-2">Confirm Booking</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const carSelect = document.getElementById('car_id');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const costDisplay = document.getElementById('estimated_cost');

    function calculateCost() {
        const option = carSelect.options[carSelect.selectedIndex];
        if (!option.value) {
            costDisplay.textContent = 'Rp 0';
            return;
        }

        const rate = parseFloat(option.getAttribute('data-rate'));
        const start = new Date(startDateInput.value);
        const end = new Date(endDateInput.value);

        if (isNaN(start.getTime()) || isNaN(end.getTime()) || start > end) {
            costDisplay.textContent = 'Invalid dates';
            return;
        }

        // Calculate days difference (inclusive, so +1)
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        
        const total = diffDays * rate;
        costDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total) + ' (' + diffDays + ' days)';
    }

    carSelect.addEventListener('change', calculateCost);
    startDateInput.addEventListener('change', calculateCost);
    endDateInput.addEventListener('change', calculateCost);
    
    // Initial calculation
    calculateCost();
});
</script>
@endpush
