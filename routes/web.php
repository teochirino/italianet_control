<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\UserStationAssignmentController;
use App\Http\Controllers\ExternalUserController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ColorChangeHistoryController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
   
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard-data', [DashboardController::class, 'getData'])->name('dashboard.data');
    
    Route::post('/attributes/{attribute}/update-color', [AttributeController::class, 'updateColor'])
        ->middleware('can-update-attribute')
        ->name('attributes.update-color');
});

Route::middleware(['auth', 'is-admin'])->prefix('admin')->group(function () {
    Route::resource('divisions', DivisionController::class);
    Route::resource('stations', StationController::class);
    Route::resource('attributes', AttributeController::class);
    
    Route::get('/user-assignments', [UserStationAssignmentController::class, 'index'])
        ->name('user-assignments.index');
    Route::post('/user-assignments/remove', [UserStationAssignmentController::class, 'removeStation'])
        ->name('user-assignments.remove');
    Route::post('/user-assignments/assign', [UserStationAssignmentController::class, 'assignStation'])
        ->name('user-assignments.assign');
    
    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->name('users.destroy');
    
    Route::get('/external-users', [ExternalUserController::class, 'index'])
        ->name('external-users.index');
    Route::post('/external-users/import', [ExternalUserController::class, 'import'])
        ->name('external-users.import');
    
    Route::get('/stations/{station}/history', [ColorChangeHistoryController::class, 'getStationHistory'])
        ->name('stations.history');
    Route::get('/attributes/{attribute}/history', [ColorChangeHistoryController::class, 'getAttributeHistory'])
        ->name('attributes.history');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
