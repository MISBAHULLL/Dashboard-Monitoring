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

test('admin can bulk restore selected deleted clients', function () {
    $admin = User::factory()->admin()->create();
    $clients = Client::factory()->count(3)->create();
    $clients->each->delete();

    $this->actingAs($admin)
        ->patch(route('clients.bulkRestore'), [
            'ids' => $clients->take(2)->pluck('id')->all(),
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('clients', [
        'id' => $clients[0]->id,
        'deleted_at' => null,
    ]);
    $this->assertDatabaseHas('clients', [
        'id' => $clients[1]->id,
        'deleted_at' => null,
    ]);
    $this->assertSoftDeleted('clients', ['id' => $clients[2]->id]);
});

test('admin can bulk restore all deleted clients', function () {
    $admin = User::factory()->admin()->create();
    $clients = Client::factory()->count(3)->create();
    $clients->each->delete();

    $this->actingAs($admin)
        ->patch(route('clients.bulkRestore'), [
            'restore_all' => true,
        ])
        ->assertRedirect();

    foreach ($clients as $client) {
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'deleted_at' => null,
        ]);
    }
});

test('admin can bulk restore selected deleted teams', function () {
    $admin = User::factory()->admin()->create();
    $teams = Team::factory()->count(3)->product()->create();
    $teams->each->delete();

    $this->actingAs($admin)
        ->patch(route('teams.bulkRestore'), [
            'ids' => $teams->take(2)->pluck('id')->all(),
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('teams', [
        'id' => $teams[0]->id,
        'deleted_at' => null,
    ]);
    $this->assertDatabaseHas('teams', [
        'id' => $teams[1]->id,
        'deleted_at' => null,
    ]);
    $this->assertSoftDeleted('teams', ['id' => $teams[2]->id]);
});

test('admin can bulk restore all deleted teams', function () {
    $admin = User::factory()->admin()->create();
    $teams = Team::factory()->count(3)->product()->create();
    $teams->each->delete();

    $this->actingAs($admin)
        ->patch(route('teams.bulkRestore'), [
            'restore_all' => true,
        ])
        ->assertRedirect();

    foreach ($teams as $team) {
        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'deleted_at' => null,
        ]);
    }
});
