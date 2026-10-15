<?php

use App\Models\Role;
use App\Models\User;

/**
 * The panel's frame: every control in the top bar keeps a name a screen
 * reader can say, at every width.
 */
it('keeps the top bar\'s icon links named when their words are hidden on a phone', function () {
    $admin = User::factory()->create();
    $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    $html = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->getContent();

    // Visually hidden on a phone, never removed: display:none would leave a bare "link".
    expect($html)->toContain('<span class="sr-only md:not-sr-only">'.__('admin.view_shop').'</span>')
        ->toContain('<span class="sr-only md:not-sr-only">العربية</span>')
        ->not->toContain('<span class="hidden md:inline">');
});
