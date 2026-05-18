<?php

use App\Models\SlaConfig;
use App\Models\User;

test('admin can view sla config page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('sla-config.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('settings/SlaConfig')
            ->has('slaList', 4)
        );
});

test('member can not view sla config page', function () {
    $member = User::factory()->member()->create();

    $this->actingAs($member)
        ->get(route('sla-config.index'))
        ->assertForbidden();
});

test('admin can save sla config', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('sla-config.upsert'), [
            'configs' => [
                [
                    'category' => 'Regulasi',
                    'max_days' => 14,
                    'warning_days' => 3,
                ],
            ],
        ])
        ->assertRedirect();

    expect(SlaConfig::where('category', 'Regulasi')->first())
        ->max_days->toBe(14)
        ->warning_days->toBe(3);
});

test('warning days must be smaller than max days', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('sla-config.upsert'), [
            'configs' => [
                [
                    'category' => 'Regulasi',
                    'max_days' => 3,
                    'warning_days' => 3,
                ],
            ],
        ])
        ->assertSessionHasErrors('configs');
});
