<?php

use App\Models\Client;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('admin dashboard task stats follow period while team and client totals stay current', function () {
    Carbon::setTestNow(Carbon::parse('2026-05-20 12:00:00'));

    $admin = User::factory()->admin()->create();
    $creator = User::factory()->admin()->create();
    $baseProduct = Team::factory()->product()->create(['created_at' => '2026-01-01 00:00:00']);
    $baseClient = Client::factory()->create(['created_at' => '2026-01-01 00:00:00']);

    Task::factory()->count(3)->open()->create([
        'product_id' => $baseProduct->id,
        'client_id' => $baseClient->id,
        'created_by' => $creator->id,
        'created_at' => '2026-05-18 10:00:00',
        'updated_at' => '2026-05-18 10:00:00',
    ]);
    Task::factory()->completed()->create([
        'product_id' => $baseProduct->id,
        'client_id' => $baseClient->id,
        'created_by' => $creator->id,
        'created_at' => '2026-05-17 10:00:00',
        'updated_at' => '2026-05-17 10:00:00',
    ]);
    Task::factory()->open()->create([
        'product_id' => $baseProduct->id,
        'client_id' => $baseClient->id,
        'created_by' => $creator->id,
        'created_at' => '2026-05-10 10:00:00',
        'updated_at' => '2026-05-10 10:00:00',
    ]);

    $deletedTask = Task::factory()->open()->create([
        'product_id' => $baseProduct->id,
        'client_id' => $baseClient->id,
        'created_by' => $creator->id,
        'created_at' => '2026-05-19 10:00:00',
        'updated_at' => '2026-05-19 10:00:00',
    ]);
    $deletedTask->delete();

    Team::factory()->count(2)->product()->create(['created_at' => '2026-05-18 10:00:00']);
    Team::factory()->product()->create(['created_at' => '2026-05-08 10:00:00']);
    Client::factory()->create(['created_at' => '2026-05-18 10:00:00']);
    Client::factory()->count(3)->create(['created_at' => '2026-05-08 10:00:00']);

    $this->actingAs($admin)
        ->get(route('dashboard', ['period' => '7d']))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/AdminDashboard')
            ->where('dashboard_period', '7d')
            ->where('stats.total_tasks', 4)
            ->where('stats.total_tasks_with_trashed', 5)
            ->where('stats.trashed_tasks', 1)
            ->where('stats.open_tasks', 3)
            ->where('stats.completed_tasks', 1)
            ->where('stats.total_teams', 4)
            ->where('stats.total_clients', 5)
            ->where('trends.tasks', 3)
            ->where('trends.pending', 2)
            ->where('trends.teams', 2)
            ->where('trends.clients', 1)
        );
});

test('admin dashboard hides team and client trend badges when master data is stable in selected period', function () {
    Carbon::setTestNow(Carbon::parse('2026-05-20 12:00:00'));

    $admin = User::factory()->admin()->create();
    $creator = User::factory()->admin()->create();
    $baseProduct = Team::factory()->product()->create(['created_at' => '2026-01-01 00:00:00']);
    $baseClient = Client::factory()->create(['created_at' => '2026-01-01 00:00:00']);

    Task::factory()->open()->create([
        'product_id' => $baseProduct->id,
        'client_id' => $baseClient->id,
        'created_by' => $creator->id,
        'created_at' => '2026-05-18 10:00:00',
        'updated_at' => '2026-05-18 10:00:00',
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard', ['period' => '7d']))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/AdminDashboard')
            ->where('dashboard_period', '7d')
            ->where('stats.total_teams', 1)
            ->where('stats.total_clients', 1)
            ->where('trends.teams', 0)
            ->where('trends.clients', 0)
        );
});

test('admin dashboard all period hides stat card trend badges', function () {
    Carbon::setTestNow(Carbon::parse('2026-05-20 12:00:00'));

    $admin = User::factory()->admin()->create();
    $creator = User::factory()->admin()->create();
    $baseProduct = Team::factory()->product()->create(['created_at' => '2026-01-01 00:00:00']);
    $baseClient = Client::factory()->create(['created_at' => '2026-01-01 00:00:00']);

    Task::factory()->count(2)->open()->create([
        'product_id' => $baseProduct->id,
        'client_id' => $baseClient->id,
        'created_by' => $creator->id,
        'created_at' => '2026-05-18 10:00:00',
        'updated_at' => '2026-05-18 10:00:00',
    ]);
    Task::factory()->completed()->create([
        'product_id' => $baseProduct->id,
        'client_id' => $baseClient->id,
        'created_by' => $creator->id,
        'created_at' => '2026-04-20 10:00:00',
        'updated_at' => '2026-04-20 10:00:00',
    ]);
    Team::factory()->product()->create(['created_at' => '2026-05-18 10:00:00']);
    Client::factory()->create(['created_at' => '2026-05-18 10:00:00']);

    $this->actingAs($admin)
        ->get(route('dashboard', ['period' => 'all']))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/AdminDashboard')
            ->where('dashboard_period', 'all')
            ->where('stats.total_tasks', 3)
            ->where('stats.open_tasks', 2)
            ->where('stats.completed_tasks', 1)
            ->where('stats.total_teams', 2)
            ->where('stats.total_clients', 2)
            ->where('trends.tasks', 0)
            ->where('trends.pending', 0)
            ->where('trends.teams', 0)
            ->where('trends.clients', 0)
        );
});
