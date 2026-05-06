<?php

use App\Models\Notification;
use App\Models\Task;
use App\Models\User;

// ──────────────────────────────────────────────────────────────
// STATUS CHANGE NOTIFICATIONS
// ──────────────────────────────────────────────────────────────

test('admin changing status notifies assignee', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    $task = Task::factory()->assignedTo($member)->create(['status' => 'open']);

    $this->actingAs($admin)
        ->patch(route('tasks.updateStatus', $task), ['status' => 'revision'])
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

// ──────────────────────────────────────────────────────────────
// BULK STATUS CHANGE NOTIFICATIONS
// ──────────────────────────────────────────────────────────────

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

// ──────────────────────────────────────────────────────────────
// ASSIGNMENT NOTIFICATIONS
// ──────────────────────────────────────────────────────────────

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

// ──────────────────────────────────────────────────────────────
// COMMENT NOTIFICATIONS
// ──────────────────────────────────────────────────────────────

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
