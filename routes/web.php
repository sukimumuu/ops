<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ListingWizardController;
use App\Http\Controllers\PropertyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');
Route::get('/properti', [PropertyController::class, 'index'])->name('properti');
Route::get('/kalkulator-pajak', function () {
    return view('kalkulator-pajak');
})->name('kalkulator-pajak');


Route::get('/masuk', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/masuk', [AuthController::class, 'login']);
Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');
Route::post('/daftar', [AuthController::class, 'register'])->name('register');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/properti-saya', [DashboardController::class, 'myProperties'])->name('my-properties');
    // Listing Wizard (Progressive Listing)
    Route::prefix('listing')->name('listing.')->group(function () {
        Route::get('/buat', [ListingWizardController::class, 'create'])->name('create');
        Route::post('/step-1', [ListingWizardController::class, 'storeStepOne'])->name('step-one');
        Route::post('/step-2/{property}', [ListingWizardController::class, 'storeStepTwo'])->name('step-two');
        Route::get('/review/{property}', [ListingWizardController::class, 'review'])->name('review');
        Route::post('/submit/{property}', [ListingWizardController::class, 'submit'])->name('submit');
        Route::get('/photo-url/{propertyPhoto}', [ListingWizardController::class, 'signedPhotoUrl'])->name('photo-url');
    });
});


