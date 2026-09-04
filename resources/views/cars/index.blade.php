@extends('layouts.app')

@section('content')
<div ng-app="carSearchApp" ng-controller="CarSearchCtrl">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Cars Management</h2>
        <a href="{{ route('cars.create') }}" class="btn btn-primary">Add New Car</a>
    </div>

    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" ng-model="filters.brand" ng-change="searchCars()" class="form-control" placeholder="Search Brand...">
                </div>
                <div class="col-md-4">
                    <input type="text" ng-model="filters.model" ng-change="searchCars()" class="form-control" placeholder="Search Model...">
                </div>
                <div class="col-md-4">
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" ng-model="filters.available" ng-change="searchCars()" id="ngAvailable">
                        <label class="form-check-label fw-bold" for="ngAvailable">Available Today Only</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info border-0 shadow-sm" ng-show="loading">
        <i class="bi bi-arrow-repeat"></i> Loading...
    </div>

    <div class="alert alert-warning border-0 shadow-sm" ng-show="!loading && cars.length === 0">
        No cars found matching your criteria.
    </div>

    <div class="row" ng-hide="loading || cars.length === 0">
        <div class="col-md-6 col-lg-4 mb-4" ng-repeat="car in cars">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold">@{{ car.brand }} @{{ car.model }}</h5>
                    <p class="mb-3">
                        <span class="badge bg-secondary">@{{ car.license_plate }}</span>
                        <span class="badge" ng-class="car.is_available == 1 ? 'bg-success' : 'bg-danger'">
                            @{{ car.is_available == 1 ? 'Available' : 'Rented' }}
                        </span>
                    </p>
                    <p class="card-text fs-5 fw-bold text-primary mb-0">
                        Rp @{{ car.daily_rate | number:0 }} <small class="fw-normal text-muted" style="font-size: 0.9rem;">/ day</small>
                    </p>
                </div>
                <div class="card-footer bg-white border-0 p-4 pt-0">
                    <a ng-href="/rentals/create?car_id=@{{ car.id }}" class="btn btn-primary w-100 fw-bold" ng-if="car.is_available == 1">
                        Book Now
                    </a>
                    <button class="btn btn-outline-secondary w-100 fw-bold" disabled ng-if="car.is_available == 0">
                        Currently Rented
                    </button>
                    
                    <div class="mt-2" ng-if="car.is_available || !car.is_available">
                        <form action="/cars/@{{ car.id }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this car?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm w-100 fw-bold">Delete Car</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.3/angular.min.js"></script>
<script>
angular.module('carSearchApp', [])
.controller('CarSearchCtrl', ['$scope', '$http', function($scope, $http) {
    $scope.cars = [];
    $scope.loading = true;
    $scope.filters = { brand: '', model: '', available: false };

    $scope.searchCars = function() {
        $scope.loading = true;
        var params = {};
        if ($scope.filters.brand) params.brand = $scope.filters.brand;
        if ($scope.filters.model) params.model = $scope.filters.model;
        if ($scope.filters.available) params.available = 1;

        $http.get('/api/cars', { params: params }).then(function(response) {
            $scope.cars = response.data.data;
            $scope.loading = false;
        }, function() {
            $scope.loading = false;
        });
    };

    // Initial load
    $scope.searchCars();
}]);
</script>
@endpush
