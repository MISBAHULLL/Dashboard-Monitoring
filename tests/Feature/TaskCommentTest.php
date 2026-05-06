<?php

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;

test('admin can add comment to a task', function () {
    $admin = User::factory()->admin()->create();
    $task = Task::factory()->create();

    $this->actingAs($admin)
        ->post(route('tasks.comments.store', $task), [
            'body' => 'This is a test comment.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('task_comments', [
        'task_id' => $task->id,
        'user_id' => $admin->id,
        'body' => 'This is a test comment.',
    ]);
});

test('member can add comment to assigned task', function () {
    $member = User::factory()->member()->create();
    $task = Task::factory()->assignedTo($member)->create();

    $this->actingAs($member)
        ->post(route('tasks.comments.store', $task), [
            'body' => 'Member comment.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('task_comments', [
        'task_id' => $task->id,
        'user_id' => $member->id,
    ]);
});

test('member cannot comment on unassigned task', function () {
    $member = User::factory()->member()->create();
    $task = Task::factory()->create();

    $this->actingAs($member)
        ->post(route('tasks.comments.store', $task), [
            'body' => 'Should fail.',
        ])
        ->assertForbidden();
});

test('user can delete own comment', function () {
    $member = User::factory()->member()->create();
    $task = Task::factory()->assignedTo($member)->create();
    $comment = TaskComment::create([
        'task_id' => $task->id,
        'user_id' => $member->id,
        'body' => 'My comment',
        'is_pinned' => false,
    ]);

    $this->actingAs($member)
        ->delete(route('tasks.comments.destroy', [$task, $comment]))
        ->assertRedirect();

    $this->assertDatabaseMissing('task_comments', ['id' => $comment->id]);
});

test('user cannot delete other users comment', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    $task = Task::factory()->assignedTo($member)->create();
    $comment = TaskComment::create([
        'task_id' => $task->id,
        'user_id' => $admin->id,
        'body' => 'Admin comment',
        'is_pinned' => false,
    ]);

    $this->actingAs($member)
        ->delete(route('tasks.comments.destroy', [$task, $comment]))
        ->assertForbidden();
});

test('admin can pin a comment', function () {
    $admin = User::factory()->admin()->create();
    $task = Task::factory()->create();
    $comment = TaskComment::create([
        'task_id' => $task->id,
        'user_id' => $admin->id,
        'body' => 'Pin me',
        'is_pinned' => false,
    ]);

    $this->actingAs($admin)
        ->patch(route('tasks.comments.pin', [$task, $comment]))
        ->assertRedirect();

    expect($comment->fresh()->is_pinned)->toBeTrue();
});

test('member cannot pin a comment', function () {
    $member = User::factory()->member()->create();
    $task = Task::factory()->assignedTo($member)->create();
    $comment = TaskComment::create([
        'task_id' => $task->id,
        'user_id' => $member->id,
        'body' => 'Cannot pin',
        'is_pinned' => false,
    ]);

    $this->actingAs($member)
        ->patch(route('tasks.comments.pin', [$task, $comment]))
        ->assertForbidden();
});

test('comment body is required', function () {
    $admin = User::factory()->admin()->create();
    $task = Task::factory()->create();

    $this->actingAs($admin)
        ->post(route('tasks.comments.store', $task), [
            'body' => '',
        ])
        ->assertSessionHasErrors('body');
});
