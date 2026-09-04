@extends('layouts.app')

@section('content')
<div ng-app="carSearchApp" ng-controller="CarSearchCtrl">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Search Cars <small class="text-muted fs-6">(AngularJS)</small></h2>
        <a href="{{ route('cars.index') }}" class="btn btn-outline-secondary btn-sm">Switch to Blade View</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
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
                        <label class="form-check-label" for="ngAvailable">Available Today Only</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info" ng-show="loading">
        <i class="bi bi-arrow-repeat"></i> Loading...
    </div>

    <div class="alert alert-warning" ng-show="!loading && cars.length === 0">
        No cars found matching your criteria.
    </div>

    <div class="row" ng-hide="loading || cars.length === 0">
        <div class="col-md-6 col-lg-4 mb-4" ng-repeat="car in cars">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">@{{ car.brand }} @{{ car.model }}</h5>
                    <p class="mb-1">
                        <span class="badge bg-secondary">@{{ car.license_plate }}</span>
                        <span class="badge" ng-class="car.is_available ? 'bg-success' : 'bg-danger'">
                            @{{ car.is_available ? 'Available' : 'Rented' }}
                        </span>
                    </p>
                    <p class="card-text fs-5 fw-bold text-primary mt-2">
                        Rp @{{ car.daily_rate | number:0 }} <small class="fw-normal text-muted">/ day</small>
                    </p>
                </div>
                <div class="card-footer bg-white">
                    <a ng-href="/rentals/create?car_id=@{{ car.id }}" class="btn btn-primary btn-sm w-100" ng-if="car.is_available">
                        Book Now
                    </a>
                    <button class="btn btn-secondary btn-sm w-100" disabled ng-if="!car.is_available">
                        Currently Rented
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center text-muted mt-3">
        <small>Showing @{{ cars.length }} car(s) — powered by AngularJS + REST API</small>
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
