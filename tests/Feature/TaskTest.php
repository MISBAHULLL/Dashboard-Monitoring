<?php

use App\Models\Client;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;

// ──────────────────────────────────────────────────────────────
// INDEX
// ──────────────────────────────────────────────────────────────

test('guests cannot view tasks', function () {
    $this->get(route('tasks.index'))->assertRedirect(route('login'));
});

test('admin can view all tasks', function () {
    $admin = User::factory()->admin()->create();
    Task::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('tasks.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Tasks/Index')
            ->has('tasks.data', 3)
        );
});

test('member can only see assigned tasks', function () {
    $member = User::factory()->member()->create();
    Task::factory()->count(3)->create(); // unassigned
    Task::factory()->assignedTo($member)->create();

    $this->actingAs($member)
        ->get(route('tasks.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Tasks/Index')
            ->has('tasks.data', 1)
        );
});

// ──────────────────────────────────────────────────────────────
// CREATE / STORE
// ──────────────────────────────────────────────────────────────

test('admin can access task create page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('tasks.create'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('Tasks/Create'));
});

test('member cannot access task create page', function () {
    $member = User::factory()->member()->create();

    $this->actingAs($member)
        ->get(route('tasks.create'))
        ->assertForbidden();
});

test('admin can create a task', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $product = Team::factory()->product()->create();

    $this->actingAs($admin)
        ->post(route('tasks.store'), [
            'title' => 'Test Task',
            'client_id' => $client->id,
            'product_id' => $product->id,
            'category' => 'Regulasi',
            'priority' => 'high',
            'status' => 'open',
        ])
        ->assertRedirect(route('tasks.index'));

    $this->assertDatabaseHas('tasks', [
        'title' => 'Test Task',
        'client_id' => $client->id,
        'created_by' => $admin->id,
    ]);
});

test('member cannot create a task', function () {
    $member = User::factory()->member()->create();
    $client = Client::factory()->create();
    $product = Team::factory()->product()->create();

    $this->actingAs($member)
        ->post(route('tasks.store'), [
            'title' => 'Forbidden Task',
            'client_id' => $client->id,
            'product_id' => $product->id,
            'category' => 'Regulasi',
            'priority' => 'medium',
            'status' => 'open',
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('tasks', ['title' => 'Forbidden Task']);
});

// ──────────────────────────────────────────────────────────────
// SHOW
// ──────────────────────────────────────────────────────────────

test('admin can view any task detail', function () {
    $admin = User::factory()->admin()->create();
    $task = Task::factory()->create();

    $this->actingAs($admin)
        ->get(route('tasks.show', $task))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Tasks/Show')
            ->has('task')
        );
});

test('member can view own assigned task', function () {
    $member = User::factory()->member()->create();
    $task = Task::factory()->assignedTo($member)->create();

    $this->actingAs($member)
        ->get(route('tasks.show', $task))
        ->assertSuccessful();
});

test('member cannot view unassigned task', function () {
    $member = User::factory()->member()->create();
    $task = Task::factory()->create();

    $this->actingAs($member)
        ->get(route('tasks.show', $task))
        ->assertForbidden();
});

// ──────────────────────────────────────────────────────────────
// UPDATE
// ──────────────────────────────────────────────────────────────

test('admin can update a task', function () {
    $admin = User::factory()->admin()->create();
    $task = Task::factory()->create();

    $this->actingAs($admin)
        ->put(route('tasks.update', $task), [
            'title' => 'Updated Title',
            'client_id' => $task->client_id,
            'product_id' => $task->product_id,
            'category' => $task->category,
            'priority' => $task->priority,
            'status' => $task->status,
        ])
        ->assertRedirect(route('tasks.index'));

    expect($task->fresh()->title)->toBe('Updated Title');
});

test('member cannot update a task', function () {
    $member = User::factory()->member()->create();
    $task = Task::factory()->assignedTo($member)->create();

    $this->actingAs($member)
        ->put(route('tasks.update', $task), [
            'title' => 'Hacked Title',
            'client_id' => $task->client_id,
            'product_id' => $task->product_id,
            'category' => $task->category,
            'priority' => $task->priority,
            'status' => $task->status,
        ])
        ->assertForbidden();
});

test('completing a task sets completed_at', function () {
    $admin = User::factory()->admin()->create();
    $task = Task::factory()->create(['status' => 'open']);

    $this->actingAs($admin)
        ->put(route('tasks.update', $task), [
            'title' => $task->title,
            'client_id' => $task->client_id,
            'product_id' => $task->product_id,
            'category' => $task->category,
            'priority' => $task->priority,
            'status' => 'completed',
        ]);

    $task->refresh();
    expect($task->status)->toBe('completed');
    expect($task->completed_at)->not->toBeNull();
});

// ──────────────────────────────────────────────────────────────
// DELETE
// ──────────────────────────────────────────────────────────────

test('admin can delete a task', function () {
    $admin = User::factory()->admin()->create();
    $task = Task::factory()->create();

    $this->actingAs($admin)
        ->delete(route('tasks.destroy', $task))
        ->assertRedirect();

    $this->assertSoftDeleted('tasks', ['id' => $task->id]);
});

test('member cannot delete a task', function () {
    $member = User::factory()->member()->create();
    $task = Task::factory()->assignedTo($member)->create();

    $this->actingAs($member)
        ->delete(route('tasks.destroy', $task))
        ->assertForbidden();
});

// ──────────────────────────────────────────────────────────────
// UPDATE STATUS (member allowed on own task)
// ──────────────────────────────────────────────────────────────

test('admin can restore a deleted task', function () {
    $admin = User::factory()->admin()->create();
    $task = Task::factory()->create();
    $task->delete();

    $this->assertSoftDeleted('tasks', ['id' => $task->id]);

    $this->actingAs($admin)
        ->patch(route('tasks.restore', $task->id))
        ->assertRedirect();

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'deleted_at' => null,
    ]);
});

test('member cannot restore a deleted task', function () {
    $member = User::factory()->member()->create();
    $task = Task::factory()->assignedTo($member)->create();
    $task->delete();

    $this->actingAs($member)
        ->patch(route('tasks.restore', $task->id))
        ->assertForbidden();
});

test('admin can permanently delete a soft deleted task', function () {
    $admin = User::factory()->admin()->create();
    $task = Task::factory()->create();
    $task->delete();

    $this->actingAs($admin)
        ->delete(route('tasks.forceDestroy', $task->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

test('admin cannot permanently delete an active task directly', function () {
    $admin = User::factory()->admin()->create();
    $task = Task::factory()->create();

    $this->actingAs($admin)
        ->delete(route('tasks.forceDestroy', $task->id))
        ->assertRedirect();

    $this->assertDatabaseHas('tasks', ['id' => $task->id]);
});

test('member cannot permanently delete a soft deleted task', function () {
    $member = User::factory()->member()->create();
    $task = Task::factory()->assignedTo($member)->create();
    $task->delete();

    $this->actingAs($member)
        ->delete(route('tasks.forceDestroy', $task->id))
        ->assertForbidden();

    $this->assertSoftDeleted('tasks', ['id' => $task->id]);
});

test('admin can bulk restore selected deleted tasks', function () {
    $admin = User::factory()->admin()->create();
    $tasks = Task::factory()->count(3)->create();
    $tasks->each->delete();

    $this->actingAs($admin)
        ->patch(route('tasks.bulkRestore'), [
            'ids' => $tasks->take(2)->pluck('id')->all(),
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('tasks', [
        'id' => $tasks[0]->id,
        'deleted_at' => null,
    ]);
    $this->assertDatabaseHas('tasks', [
        'id' => $tasks[1]->id,
        'deleted_at' => null,
    ]);
    $this->assertSoftDeleted('tasks', ['id' => $tasks[2]->id]);
});

test('admin can bulk restore all deleted tasks', function () {
    $admin = User::factory()->admin()->create();
    $tasks = Task::factory()->count(3)->create();
    $tasks->each->delete();

    $this->actingAs($admin)
        ->patch(route('tasks.bulkRestore'), [
            'restore_all' => true,
        ])
        ->assertRedirect();

    foreach ($tasks as $task) {
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'deleted_at' => null,
        ]);
    }
});

test('admin can permanently delete selected soft deleted tasks', function () {
    $admin = User::factory()->admin()->create();
    $tasks = Task::factory()->count(3)->create();
    $tasks->each->delete();

    $this->actingAs($admin)
        ->delete(route('tasks.bulkForceDestroy'), [
            'ids' => $tasks->take(2)->pluck('id')->all(),
        ])
        ->assertRedirect();

    $this->assertDatabaseMissing('tasks', ['id' => $tasks[0]->id]);
    $this->assertDatabaseMissing('tasks', ['id' => $tasks[1]->id]);
    $this->assertSoftDeleted('tasks', ['id' => $tasks[2]->id]);
});

test('admin can permanently delete all soft deleted tasks', function () {
    $admin = User::factory()->admin()->create();
    $tasks = Task::factory()->count(3)->create();
    $tasks->each->delete();

    $this->actingAs($admin)
        ->delete(route('tasks.bulkForceDestroy'), [
            'delete_all' => true,
        ])
        ->assertRedirect();

    foreach ($tasks as $task) {
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
});

test('member can update status of own assigned task', function () {
    $member = User::factory()->member()->create();
    $task = Task::factory()->assignedTo($member)->create(['status' => 'open']);

    $this->actingAs($member)
        ->patch(route('tasks.updateStatus', $task), [
            'status' => 'in_progress',
        ])
        ->assertRedirect();

    expect($task->fresh()->status)->toBe('in_progress');
});

test('member cannot update status of unassigned task', function () {
    $member = User::factory()->member()->create();
    $task = Task::factory()->create(['status' => 'open']);

    $this->actingAs($member)
        ->patch(route('tasks.updateStatus', $task), [
            'status' => 'in_progress',
        ])
        ->assertForbidden();
});

// ──────────────────────────────────────────────────────────────
// BULK ACTIONS
// ──────────────────────────────────────────────────────────────

test('admin can bulk delete tasks', function () {
    $admin = User::factory()->admin()->create();
    $tasks = Task::factory()->count(3)->create();
    $ids = $tasks->pluck('id')->toArray();

    $this->actingAs($admin)
        ->post(route('tasks.bulkDestroy'), ['ids' => $ids])
        ->assertRedirect();

    foreach ($ids as $id) {
        $this->assertSoftDeleted('tasks', ['id' => $id]);
    }
});

test('admin can bulk update status', function () {
    $admin = User::factory()->admin()->create();
    $tasks = Task::factory()->count(2)->create(['status' => 'open']);
    $ids = $tasks->pluck('id')->toArray();

    $this->actingAs($admin)
        ->post(route('tasks.bulkUpdateStatus'), [
            'ids' => $ids,
            'status' => 'completed',
        ])
        ->assertRedirect();

    foreach ($ids as $id) {
        $task = Task::find($id);
        expect($task->status)->toBe('completed');
        expect($task->completed_at)->not->toBeNull();
    }
});

// ──────────────────────────────────────────────────────────────
// KANBAN
// ──────────────────────────────────────────────────────────────

test('admin can view kanban board', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('tasks.kanban'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('Tasks/Kanban'));
});

// ──────────────────────────────────────────────────────────────
// VALIDATION
// ──────────────────────────────────────────────────────────────

test('task creation requires title and category', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('tasks.store'), [])
        ->assertSessionHasErrors(['title', 'client_id', 'product_id', 'category', 'priority', 'status']);
});

test('task status must be valid enum value', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $product = Team::factory()->product()->create();

    $this->actingAs($admin)
        ->post(route('tasks.store'), [
            'title' => 'Test',
            'client_id' => $client->id,
            'product_id' => $product->id,
            'category' => 'Regulasi',
            'priority' => 'medium',
            'status' => 'invalid_status',
        ])
        ->assertSessionHasErrors('status');
});
