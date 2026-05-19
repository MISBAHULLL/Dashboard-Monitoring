<?php

use App\Models\Client;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
            'doc_url' => 'https://example.com/document.pdf',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('documents', [
        'title' => 'Dokumen UAT Test',
        'client_id' => $client->id,
        'doc_url' => 'https://example.com/document.pdf',
        'created_by' => $admin->id,
    ]);
});

test('admin can upload an allowed document file', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $file = UploadedFile::fake()->create('uat.pdf', 100, 'application/pdf');

    $this->actingAs($admin)
        ->post(route('documents.store'), [
            'client_id' => $client->id,
            'title' => 'Dokumen PDF Test',
            'type' => 'UAT',
            'file' => $file,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $document = Document::query()->where('title', 'Dokumen PDF Test')->firstOrFail();

    expect($document->file_name)->toBe('uat.pdf');
    Storage::disk('public')->assertExists($document->file_path);
});

test('admin cannot upload a disallowed document file type', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $file = UploadedFile::fake()->create('payload.exe', 100, 'application/x-msdownload');

    $this->actingAs($admin)
        ->from(route('documents.index'))
        ->post(route('documents.store'), [
            'client_id' => $client->id,
            'title' => 'Unsafe Upload',
            'type' => 'UAT',
            'file' => $file,
        ])
        ->assertRedirect(route('documents.index'))
        ->assertSessionHasErrors('file');

    $this->assertDatabaseMissing('documents', [
        'title' => 'Unsafe Upload',
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

test('admin can restore a deleted document with its version history intact', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $doc = Document::create([
        'client_id' => $client->id,
        'title' => 'Restore Me',
        'type' => 'UAT',
        'current_version' => 1,
        'created_by' => $admin->id,
    ]);
    $version = DocumentVersion::create([
        'document_id' => $doc->id,
        'version_number' => 1,
        'file_path' => 'documents/example.pdf',
        'doc_url' => '/storage/documents/example.pdf',
        'file_size' => 100,
        'uploaded_by' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('documents.destroy', $doc))
        ->assertRedirect();

    $this->assertSoftDeleted('documents', ['id' => $doc->id]);
    $this->assertDatabaseHas('document_versions', ['id' => $version->id]);

    $this->actingAs($admin)
        ->patch(route('documents.restore', $doc->id))
        ->assertRedirect();

    $this->assertDatabaseHas('documents', [
        'id' => $doc->id,
        'deleted_at' => null,
    ]);
    $this->assertDatabaseHas('document_versions', ['id' => $version->id]);
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

test('member cannot restore a deleted document', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->member()->create();
    $client = Client::factory()->create();
    $doc = Document::create([
        'client_id' => $client->id,
        'title' => 'Member Restore Forbidden',
        'type' => 'UAT',
        'current_version' => 1,
        'created_by' => $admin->id,
    ]);
    $doc->delete();

    $this->actingAs($member)
        ->patch(route('documents.restore', $doc->id))
        ->assertForbidden();
});

test('admin can bulk restore selected deleted documents', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $docs = collect(['Doc A', 'Doc B', 'Doc C'])->map(fn (string $title) => Document::create([
        'client_id' => $client->id,
        'title' => $title,
        'type' => 'UAT',
        'current_version' => 1,
        'created_by' => $admin->id,
    ]));
    $docs->each->delete();

    $this->actingAs($admin)
        ->patch(route('documents.bulkRestore'), [
            'ids' => $docs->take(2)->pluck('id')->all(),
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('documents', [
        'id' => $docs[0]->id,
        'deleted_at' => null,
    ]);
    $this->assertDatabaseHas('documents', [
        'id' => $docs[1]->id,
        'deleted_at' => null,
    ]);
    $this->assertSoftDeleted('documents', ['id' => $docs[2]->id]);
});

test('admin can bulk restore all deleted documents', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $docs = collect(['Doc A', 'Doc B', 'Doc C'])->map(fn (string $title) => Document::create([
        'client_id' => $client->id,
        'title' => $title,
        'type' => 'UAT',
        'current_version' => 1,
        'created_by' => $admin->id,
    ]));
    $docs->each->delete();

    $this->actingAs($admin)
        ->patch(route('documents.bulkRestore'), [
            'restore_all' => true,
        ])
        ->assertRedirect();

    foreach ($docs as $doc) {
        $this->assertDatabaseHas('documents', [
            'id' => $doc->id,
            'deleted_at' => null,
        ]);
    }
});
