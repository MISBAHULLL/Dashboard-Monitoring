<?php

use App\Models\Client;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('admin can import tasks from csv', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create(['name' => 'RS Harapan']);
    $product = Team::factory()->product()->create(['name' => 'EMR']);

    // Create a CSV file with valid data
    $csvContent = "Judul Task,Client / Faskes,Product,Jenis,Prioritas,Status,Deskripsi\n";
    $csvContent .= "Task Import 1,RS Harapan,EMR,Regulasi,high,open,Deskripsi task 1\n";
    $csvContent .= "Task Import 2,RS Harapan,EMR,Saran Fitur,medium,open,Deskripsi task 2\n";

    $file = UploadedFile::fake()->createWithContent('import_test.csv', $csvContent);

    $this->actingAs($admin)
        ->post(route('tasks.import'), ['file' => $file])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('tasks', [
        'title' => 'Task Import 1',
        'client_id' => $client->id,
        'product_id' => $product->id,
        'category' => 'Regulasi',
    ]);

    $this->assertDatabaseHas('tasks', [
        'title' => 'Task Import 2',
        'category' => 'Saran Fitur',
    ]);
});

test('member cannot import tasks', function () {
    $member = User::factory()->member()->create();

    $file = UploadedFile::fake()->createWithContent('test.csv', "Judul Task\nTest");

    $this->actingAs($member)
        ->post(route('tasks.import'), ['file' => $file])
        ->assertForbidden();
});

test('import validates file type', function () {
    $admin = User::factory()->admin()->create();

    $file = UploadedFile::fake()->create('test.txt', 100);

    $this->actingAs($admin)
        ->post(route('tasks.import'), ['file' => $file])
        ->assertSessionHasErrors('file');
});

test('import requires a file', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('tasks.import'), [])
        ->assertSessionHasErrors('file');
});

test('import skips rows with missing client', function () {
    $admin = User::factory()->admin()->create();
    Team::factory()->product()->create(['name' => 'EMR']);

    $csvContent = "Judul Task,Client / Faskes,Product,Jenis\n";
    $csvContent .= "Task Gagal,RS Tidak Ada,EMR,Regulasi\n";

    $file = UploadedFile::fake()->createWithContent('import_fail.csv', $csvContent);

    $this->actingAs($admin)
        ->post(route('tasks.import'), ['file' => $file])
        ->assertRedirect();

    $this->assertDatabaseMissing('tasks', ['title' => 'Task Gagal']);
});

test('import handles invalid category gracefully', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create(['name' => 'RS Test']);
    $product = Team::factory()->product()->create(['name' => 'HIS']);

    $csvContent = "Judul Task,Client / Faskes,Product,Jenis\n";
    $csvContent .= "Task Default,RS Test,HIS,Jenis Invalid\n";

    $file = UploadedFile::fake()->createWithContent('import_default.csv', $csvContent);

    $this->actingAs($admin)
        ->post(route('tasks.import'), ['file' => $file])
        ->assertRedirect();

    // Should default to 'Saran Fitur' for invalid category
    $this->assertDatabaseHas('tasks', [
        'title' => 'Task Default',
        'category' => 'Saran Fitur',
    ]);
});
