<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TaskCommentController;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Group route yang wajib login
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Global Search API
    Route::get('/api/search', [SearchController::class, 'search'])->name('api.search');

    // Activity Log
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    // Notifications
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Tasks (Bisa diakses oleh semua user yang login)
    // Tasks Bulk Actions
    Route::post('/tasks/bulk-delete', [TaskController::class, 'bulkDestroy'])->name('tasks.bulkDestroy');
    Route::post('/tasks/bulk-status', [TaskController::class, 'bulkUpdateStatus'])->name('tasks.bulkUpdateStatus');
    Route::post('/tasks/bulk-assign', [TaskController::class, 'bulkAssign'])->name('tasks.bulkAssign');

    Route::get('/tasks-kanban', [TaskController::class, 'kanban'])->name('tasks.kanban');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::post('/tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('tasks.comments.store');
    Route::delete('/tasks/{task}/comments/{comment}', [TaskCommentController::class, 'destroy'])->name('tasks.comments.destroy');
    Route::patch('/tasks/{task}/comments/{comment}/pin', [TaskCommentController::class, 'togglePin'])->name('tasks.comments.pin');
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
