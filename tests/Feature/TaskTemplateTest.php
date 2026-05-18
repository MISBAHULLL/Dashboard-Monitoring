<?php

use App\Models\Client;
use App\Models\TaskTemplate;
use App\Models\Team;
use App\Models\User;

test('admin can create a task template', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $product = Team::factory()->product()->create();

    $this->actingAs($admin)
        ->post(route('task-templates.store'), [
            'name' => 'Template UAT',
            'client_id' => $client->id,
            'product_id' => $product->id,
            'category' => 'Regulasi',
            'priority' => 'medium',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('task_templates', [
        'name' => 'Template UAT',
        'created_by' => $admin->id,
    ]);
});

test('member can not create a task template', function () {
    $member = User::factory()->member()->create();
    $client = Client::factory()->create();
    $product = Team::factory()->product()->create();

    $this->actingAs($member)
        ->post(route('task-templates.store'), [
            'name' => 'Forbidden Template',
            'client_id' => $client->id,
            'product_id' => $product->id,
            'category' => 'Regulasi',
            'priority' => 'medium',
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('task_templates', [
        'name' => 'Forbidden Template',
    ]);
});

test('member can not delete a task template', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    $template = TaskTemplate::create([
        'name' => 'Protected Template',
        'created_by' => $admin->id,
    ]);

    $this->actingAs($member)
        ->delete(route('task-templates.destroy', $template))
        ->assertForbidden();

    $this->assertDatabaseHas('task_templates', [
        'id' => $template->id,
    ]);
});
