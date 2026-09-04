<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\Api\CarApiController;

// Public Routes
Route::get('/', function () {
    return view('home');
})->name('home');

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Cars CRUD (Blade)
    Route::resource('cars', CarController::class);

    // Cars Search (AngularJS)
    Route::get('/cars-search', function () {
        return view('cars.search');
    })->name('cars.search');

    // Rentals
    Route::get('/rentals/create', [RentalController::class, 'create'])->name('rentals.create');
    Route::post('/rentals', [RentalController::class, 'store'])->name('rentals.store');
    Route::get('/my-rentals', [RentalController::class, 'myRentals'])->name('rentals.my');

    // Returns
    Route::get('/return-car', [ReturnController::class, 'create'])->name('returns.create');
    Route::post('/return-car', [ReturnController::class, 'store'])->name('returns.store');
});

// API Routes (Prefix manually since we're using web.php for simplicity without api routes setup)
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/cars', [CarApiController::class, 'index'])->name('cars.index');
});
