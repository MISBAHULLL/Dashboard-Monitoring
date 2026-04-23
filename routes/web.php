<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\TaskController;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Group route yang wajib login
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Tasks (Bisa diakses oleh semua user yang login)
    Route::resource('tasks', TaskController::class);

    // Group khusus Admin (Menggunakan alias middleware 'role' yang kita buat)
    Route::middleware(['role:admin'])->group(function () {
        
        // Master Data (Hanya Index, Store, Update, Destroy)
        Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);
        Route::resource('teams', TeamController::class)->except(['create', 'show', 'edit']);
        Route::resource('clients', ClientController::class)->except(['create', 'show', 'edit']);
        
    });
});

require __DIR__.'/settings.php';