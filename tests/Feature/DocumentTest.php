<?php

use App\Models\Client;
use App\Models\Document;
use App\Models\User;

test('guests cannot view documents', function () {
    $this->get(route('documents.index'))->assertRedirect(route('login'));
});

test('admin can view document list', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('documents.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('Documents/Index'));
});

test('member can view document list', function () {
    $member = User::factory()->member()->create();

    $this->actingAs($member)
        ->get(route('documents.index'))
        ->assertSuccessful();
});

test('admin can create a document', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    $this->actingAs($admin)
        ->post(route('documents.store'), [
            'client_id' => $client->id,
            'title' => 'Dokumen UAT Test',
            'type' => 'UAT',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('documents', [
        'title' => 'Dokumen UAT Test',
        'client_id' => $client->id,
        'created_by' => $admin->id,
    ]);
});

test('member cannot create a document', function () {
    $member = User::factory()->member()->create();
    $client = Client::factory()->create();

    $this->actingAs($member)
        ->post(route('documents.store'), [
            'client_id' => $client->id,
            'title' => 'Forbidden Doc',
            'type' => 'UAT',
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('documents', ['title' => 'Forbidden Doc']);
});

test('admin can view document detail', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $doc = Document::create([
        'client_id' => $client->id,
        'title' => 'Test Doc',
        'type' => 'BAST',
        'current_version' => 1,
        'created_by' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->get(route('documents.show', $doc))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('Documents/Show'));
});

test('admin can delete a document', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $doc = Document::create([
        'client_id' => $client->id,
        'title' => 'Delete Me',
        'type' => 'UAT',
        'current_version' => 1,
        'created_by' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('documents.destroy', $doc))
        ->assertRedirect();

    $this->assertSoftDeleted('documents', ['id' => $doc->id]);
});

test('member cannot delete a document', function () {
    $member = User::factory()->member()->create();
    $client = Client::factory()->create();
    $doc = Document::create([
        'client_id' => $client->id,
        'title' => 'Protected Doc',
        'type' => 'UAT',
        'current_version' => 1,
        'created_by' => $member->id,
    ]);

    $this->actingAs($member)
        ->delete(route('documents.destroy', $doc))
        ->assertForbidden();
});
