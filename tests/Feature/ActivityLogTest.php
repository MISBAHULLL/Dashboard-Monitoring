<?php

use App\Models\User;

test('admin can view activity logs', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('activity-logs.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('ActivityLogs/Index'));
});

test('member can not view activity logs', function () {
    $member = User::factory()->member()->create();

    $this->actingAs($member)
        ->get(route('activity-logs.index'))
        ->assertForbidden();
});
