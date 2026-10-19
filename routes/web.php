<?php

use App\Http\Controllers\LicenseController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';

// --- ライセンス管理(練習用の業務機能) ---
Route::middleware(['auth'])->group(function () {
    Route::get('licenses', [LicenseController::class, 'index'])->name('licenses.index');
    Route::post('licenses', [LicenseController::class, 'store'])->name('licenses.store');
    Route::get('licenses/{license}', [LicenseController::class, 'show'])->name('licenses.show');
    Route::post('licenses/{license}/renew', [LicenseController::class, 'renew'])->name('licenses.renew');
    Route::post('licenses/{license}/assignments', [LicenseController::class, 'assign'])->name('licenses.assign');
    Route::post('licenses/{license}/assignments/{assignment}/release', [LicenseController::class, 'release'])->name('licenses.release');
});
