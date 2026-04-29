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
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\SlaConfigController;

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

    Route::get('/tasks/export', [TaskController::class, 'export'])->name('tasks.export');

    Route::get('/tasks-kanban', [TaskController::class, 'kanban'])->name('tasks.kanban');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::post('/tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('tasks.comments.store');
    Route::delete('/tasks/{task}/comments/{comment}', [TaskCommentController::class, 'destroy'])->name('tasks.comments.destroy');
    Route::patch('/tasks/{task}/comments/{comment}/pin', [TaskCommentController::class, 'togglePin'])->name('tasks.comments.pin');
    Route::resource('tasks', TaskController::class);
    Route::post('/task-templates', [\App\Http\Controllers\TaskTemplateController::class, 'store'])->name('task-templates.store');
    Route::delete('/task-templates/{taskTemplate}', [\App\Http\Controllers\TaskTemplateController::class, 'destroy'])->name('task-templates.destroy');

    // Documents (File Versioning)
    Route::post('/documents/{document}/sync-tasks', [DocumentController::class, 'syncTasks'])->name('documents.syncTasks');
    Route::resource('documents', DocumentController::class)->except(['create', 'edit']);


    // Group khusus Admin (Menggunakan alias middleware 'role' yang kita buat)
    Route::middleware(['role:admin'])->group(function () {
        
        // Master Data (Hanya Index, Store, Update, Destroy)
        Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);
        Route::resource('teams', TeamController::class)->except(['create', 'show', 'edit']);
        Route::resource('clients', ClientController::class)->except(['create', 'show', 'edit']);
        
        // Backup & Restore
        Route::post('/settings/backup/create', [BackupController::class, 'create'])->name('backup.create');
        Route::get('/settings/backup/download', [BackupController::class, 'download'])->name('backup.download');
        Route::delete('/settings/backup/delete', [BackupController::class, 'destroy'])->name('backup.destroy');
        Route::post('/settings/backup/restore', [BackupController::class, 'restore'])->name('backup.restore');

        // SLA Config
        Route::get('/settings/sla-config', [SlaConfigController::class, 'index'])->name('sla-config.index');
        Route::post('/settings/sla-config', [SlaConfigController::class, 'upsert'])->name('sla-config.upsert');
    });
});

require __DIR__.'/settings.php';
