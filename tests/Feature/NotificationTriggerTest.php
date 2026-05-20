<?php

use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use App\Services\NotificationService;

// -----------------------------------------------------------------------------
// STATUS CHANGE NOTIFICATIONS
// -----------------------------------------------------------------------------

test('admin changing status notifies assignee', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    $task = Task::factory()->assignedTo($member)->create(['status' => 'open']);

    $this->actingAs($admin)
        ->patch(route('tasks.updateStatus', $task), [
            'status' => 'revision',
            'review_note' => 'Perlu perbaikan pada hasil pengerjaan.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('notifications', [
        'user_id' => $member->id,
        'type' => 'status_changed',
    ]);
});

test('member changing status notifies admins', function () {
    $admin1 = User::factory()->admin()->create();
    $admin2 = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    $task = Task::factory()->assignedTo($member)->create(['status' => 'open']);

    $this->actingAs($member)
        ->patch(route('tasks.updateStatus', $task), ['status' => 'in_progress'])
        ->assertRedirect();

    // Both admins should receive notification
    expect(Notification::where('user_id', $admin1->id)->where('type', 'status_changed')->count())->toBe(1);
    expect(Notification::where('user_id', $admin2->id)->where('type', 'status_changed')->count())->toBe(1);
});

test('no notification when status does not change', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    $task = Task::factory()->assignedTo($member)->create(['status' => 'open']);

    $this->actingAs($admin)
        ->patch(route('tasks.updateStatus', $task), ['status' => 'open'])
        ->assertRedirect();

    $this->assertDatabaseMissing('notifications', [
        'type' => 'status_changed',
    ]);
});

test('admin does not get self-notification for status change', function () {
    $admin = User::factory()->admin()->create();
    $task = Task::factory()->assignedTo($admin)->create(['status' => 'open']);

    $this->actingAs($admin)
        ->patch(route('tasks.updateStatus', $task), ['status' => 'completed'])
        ->assertRedirect();

    // Admin should not notify themselves
    $this->assertDatabaseMissing('notifications', [
        'user_id' => $admin->id,
        'type' => 'status_changed',
    ]);
});

// -----------------------------------------------------------------------------
// BULK STATUS CHANGE NOTIFICATIONS
// -----------------------------------------------------------------------------

test('task notifications are dismissed when task is completed', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    $task = Task::factory()->assignedTo($member)->create(['status' => 'open']);

    Notification::create([
        'user_id' => $member->id,
        'type' => 'deadline_soon',
        'title' => 'Deadline task mendekat',
        'body' => 'Task mendekati deadline.',
        'link' => route('tasks.show', $task),
        'is_read' => false,
    ]);

    Notification::create([
        'user_id' => $admin->id,
        'type' => 'deadline_overdue',
        'title' => 'Task melewati deadline',
        'body' => 'Task sudah melewati deadline.',
        'link' => route('tasks.show', $task),
        'is_read' => false,
    ]);

    $this->actingAs($admin)
        ->patch(route('tasks.updateStatus', $task), ['status' => 'completed'])
        ->assertRedirect();

    Notification::query()
        ->where('link', route('tasks.show', $task))
        ->get()
        ->each(function (Notification $notification) {
            expect($notification->is_read)->toBeTrue();
            expect($notification->dismissed_at)->not->toBeNull();
        });
});

test('dismiss read notifications hides only current user read notifications', function () {
    $user = User::factory()->member()->create();
    $otherUser = User::factory()->member()->create();

    $readNotification = Notification::create([
        'user_id' => $user->id,
        'type' => 'status_changed',
        'title' => 'Read notification',
        'body' => 'Already read.',
        'is_read' => true,
    ]);
    $unreadNotification = Notification::create([
        'user_id' => $user->id,
        'type' => 'new_comment',
        'title' => 'Unread notification',
        'body' => 'Still unread.',
        'is_read' => false,
    ]);
    $otherNotification = Notification::create([
        'user_id' => $otherUser->id,
        'type' => 'status_changed',
        'title' => 'Other notification',
        'body' => 'Belongs to another user.',
        'is_read' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('notifications.dismiss-read'))
        ->assertRedirect();

    expect($readNotification->fresh()->dismissed_at)->not->toBeNull();
    expect($unreadNotification->fresh()->dismissed_at)->toBeNull();
    expect($otherNotification->fresh()->dismissed_at)->toBeNull();
});

test('dismiss all notifications hides only current user notifications', function () {
    $user = User::factory()->member()->create();
    $otherUser = User::factory()->member()->create();

    $readNotification = Notification::create([
        'user_id' => $user->id,
        'type' => 'status_changed',
        'title' => 'Read notification',
        'body' => 'Already read.',
        'is_read' => true,
    ]);
    $unreadNotification = Notification::create([
        'user_id' => $user->id,
        'type' => 'new_comment',
        'title' => 'Unread notification',
        'body' => 'Still unread.',
        'is_read' => false,
    ]);
    $otherNotification = Notification::create([
        'user_id' => $otherUser->id,
        'type' => 'status_changed',
        'title' => 'Other notification',
        'body' => 'Belongs to another user.',
        'is_read' => false,
    ]);

    $this->actingAs($user)
        ->patch(route('notifications.dismiss-all'))
        ->assertRedirect();

    expect($readNotification->fresh()->dismissed_at)->not->toBeNull();
    expect($unreadNotification->fresh()->is_read)->toBeTrue();
    expect($unreadNotification->fresh()->dismissed_at)->not->toBeNull();
    expect($otherNotification->fresh()->dismissed_at)->toBeNull();
});

test('bulk status change sends notifications', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    $tasks = Task::factory()->count(2)->assignedTo($member)->create(['status' => 'open']);
    $ids = $tasks->pluck('id')->toArray();

    $this->actingAs($admin)
        ->post(route('tasks.bulkUpdateStatus'), [
            'ids' => $ids,
            'status' => 'completed',
        ])
        ->assertRedirect();

    // Member should get 2 notifications (one per task)
    expect(Notification::where('user_id', $member->id)->where('type', 'status_changed')->count())->toBe(2);
});

// -----------------------------------------------------------------------------
// ASSIGNMENT NOTIFICATIONS
// -----------------------------------------------------------------------------

test('assigning task sends notification to assignee', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    $task = Task::factory()->create();

    $this->actingAs($admin)
        ->put(route('tasks.update', $task), [
            'title' => $task->title,
            'client_id' => $task->client_id,
            'product_id' => $task->product_id,
            'category' => $task->category,
            'priority' => $task->priority,
            'status' => $task->status,
            'assigned_to' => $member->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('notifications', [
        'user_id' => $member->id,
        'type' => 'task_assigned',
    ]);
});

test('reassigning to same user does not create duplicate notification', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    $task = Task::factory()->assignedTo($member)->create();

    $this->actingAs($admin)
        ->put(route('tasks.update', $task), [
            'title' => $task->title,
            'client_id' => $task->client_id,
            'product_id' => $task->product_id,
            'category' => $task->category,
            'priority' => $task->priority,
            'status' => $task->status,
            'assigned_to' => $member->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseMissing('notifications', [
        'user_id' => $member->id,
        'type' => 'task_assigned',
    ]);
});

// -----------------------------------------------------------------------------
// COMMENT NOTIFICATIONS
// -----------------------------------------------------------------------------

test('commenting on task notifies the assignee', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    $task = Task::factory()->assignedTo($member)->create();

    $this->actingAs($admin)
        ->post(route('tasks.comments.store', $task), [
            'body' => 'Please check this.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('notifications', [
        'user_id' => $member->id,
        'type' => 'new_comment',
    ]);
});

test('assignee commenting on own task does not self-notify', function () {
    $member = User::factory()->member()->create();
    $task = Task::factory()->assignedTo($member)->create();

    $this->actingAs($member)
        ->post(route('tasks.comments.store', $task), [
            'body' => 'My own update.',
        ])
        ->assertRedirect();

    $this->assertDatabaseMissing('notifications', [
        'user_id' => $member->id,
        'type' => 'new_comment',
    ]);
});

test('commenting on unassigned task creates no notification', function () {
    $admin = User::factory()->admin()->create();
    $task = Task::factory()->create(['assigned_to' => null]);

    $this->actingAs($admin)
        ->post(route('tasks.comments.store', $task), [
            'body' => 'A comment.',
        ])
        ->assertRedirect();

    $this->assertDatabaseMissing('notifications', [
        'type' => 'new_comment',
    ]);
});

// -----------------------------------------------------------------------------
// DEADLINE NOTIFICATIONS
// -----------------------------------------------------------------------------

test('due soon notification is sent to task assignee once per day', function () {
    $member = User::factory()->member()->create();
    $task = Task::factory()->assignedTo($member)->create([
        'status' => 'open',
        'release_date' => now()->addDays(2)->toDateString(),
    ]);

    $service = app(NotificationService::class);

    expect($service->sendDueSoonNotifications())->toBe(1);
    expect($service->sendDueSoonNotifications())->toBe(0);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $member->id,
        'type' => 'deadline_soon',
        'link' => route('tasks.show', $task),
    ]);
});

test('overdue notification is sent to assignee and active admins once per day', function () {
    $admin = User::factory()->admin()->create();
    $inactiveAdmin = User::factory()->admin()->create(['is_active' => false]);
    $member = User::factory()->member()->create();
    $task = Task::factory()->assignedTo($member)->create([
        'created_by' => $member->id,
        'status' => 'open',
        'release_date' => now()->subDays(2)->toDateString(),
    ]);

    $service = app(NotificationService::class);

    expect($service->sendOverdueNotifications())->toBe(2);
    expect($service->sendOverdueNotifications())->toBe(0);

    $this->assertDatabaseHas('notifications', [
        'user_id' => $member->id,
        'type' => 'deadline_overdue',
        'link' => route('tasks.show', $task),
    ]);
    $this->assertDatabaseHas('notifications', [
        'user_id' => $admin->id,
        'type' => 'deadline_overdue',
        'link' => route('tasks.show', $task),
    ]);
    $this->assertDatabaseMissing('notifications', [
        'user_id' => $inactiveAdmin->id,
        'type' => 'deadline_overdue',
    ]);
});
