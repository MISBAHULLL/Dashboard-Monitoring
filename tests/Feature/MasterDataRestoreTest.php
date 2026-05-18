<?php

use App\Models\Client;
use App\Models\Team;
use App\Models\User;

test('admin can restore a deleted client', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $client->delete();

    $this->assertSoftDeleted('clients', ['id' => $client->id]);

    $this->actingAs($admin)
        ->patch(route('clients.restore', $client->id))
        ->assertRedirect();

    $this->assertDatabaseHas('clients', [
        'id' => $client->id,
        'deleted_at' => null,
    ]);
});

test('member cannot restore a deleted client', function () {
    $member = User::factory()->member()->create();
    $client = Client::factory()->create();
    $client->delete();

    $this->actingAs($member)
        ->patch(route('clients.restore', $client->id))
        ->assertForbidden();
});

test('admin can restore a deleted team', function () {
    $admin = User::factory()->admin()->create();
    $team = Team::factory()->product()->create();
    $team->delete();

    $this->assertSoftDeleted('teams', ['id' => $team->id]);

    $this->actingAs($admin)
        ->patch(route('teams.restore', $team->id))
        ->assertRedirect();

    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'deleted_at' => null,
    ]);
});

test('member cannot restore a deleted team', function () {
    $member = User::factory()->member()->create();
    $team = Team::factory()->product()->create();
    $team->delete();

    $this->actingAs($member)
        ->patch(route('teams.restore', $team->id))
        ->assertForbidden();
});
