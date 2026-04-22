<?php

use App\Http\Controllers\TeamController;
use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    // Team CRUD routes → /teams, /teams/{team}
    // resource() otomatis membuat: index, store, update, destroy
    Route::resource('teams', TeamController::class)->only(['index', 'store', 'update', 'destroy']);

    // Client CRUD routes → /clients, /clients/{client}
    Route::resource('clients', ClientController::class)->only(['index', 'store', 'update', 'destroy']);
});

require __DIR__.'/settings.php';
