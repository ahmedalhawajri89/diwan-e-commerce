<?php

use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\ShopSetting;
use App\Models\User;

/**
 * The shop's own facts and policies: set by an administrator, each one
 * optional, checked, and in the activity log when changed.
 */
function settingsAdmin(): User
{
    $user = User::factory()->create(['name' => 'Operator']);
    $user->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    return $user;
}

it('starts with every policy undecided, and says what that means', function () {
    $settings = ShopSetting::current();

    expect($settings->exists)->toBeFalse()
        ->and($settings->issuesTaxInvoices())->toBeFalse()
        ->and($settings->booksDeliveries())->toBeFalse();

    $this->actingAs(settingsAdmin())->get(route('admin.settings'))
        ->assertOk()
        ->assertSee(__('admin.settings.status.invoice.off'))
        ->assertSee(__('admin.settings.status.returns.off'))
        ->assertSee(__('admin.settings.status.delivery.off'));
});

it('saves the shop\'s facts, and the log says what changed', function () {
    $admin = settingsAdmin();

    $this->actingAs($admin)->put(route('admin.settings.update'), [
        'legal_name' => 'Diwan Furniture Trading Est.', 'vat_number' => '300000000000003',
        'return_days' => '14', 'delivery_lead_days' => '2', 'delivery_weekdays' => ['6', '0', '1', '6'],
    ])->assertRedirect(route('admin.settings'))->assertSessionHas('success');

    $settings = ShopSetting::current();
    expect($settings->only(['legal_name', 'vat_number', 'return_days', 'delivery_lead_days', 'delivery_weekdays']))->toBe([
        'legal_name' => 'Diwan Furniture Trading Est.', 'vat_number' => '300000000000003',
        'return_days' => 14, 'delivery_lead_days' => 2, 'delivery_weekdays' => [0, 1, 6],
    ])
        ->and($settings->issuesTaxInvoices())->toBeTrue()
        ->and($settings->booksDeliveries())->toBeTrue();

    expect(ActivityLog::where('entity_type', ShopSetting::class)->exists())->toBeTrue();

    // Emptied again: undecided again, not a default. (Changing the delivery
    // days, an array, used to crash the activity log's diff half-way.)
    $this->actingAs($admin)->put(route('admin.settings.update'), [])->assertRedirect(route('admin.settings'));
    expect(ShopSetting::current()->only(['vat_number', 'return_days', 'delivery_weekdays']))
        ->toBe(['vat_number' => null, 'return_days' => null, 'delivery_weekdays' => null]);
});

it('refuses a VAT number that is not a Saudi one, and a policy out of range', function () {
    $admin = settingsAdmin();

    foreach (['30000000000000', '100000000000003', '30000000000000A'] as $bad) {
        $this->actingAs($admin)->put(route('admin.settings.update'), ['vat_number' => $bad])->assertSessionHasErrors('vat_number');
    }
    $this->actingAs($admin)->put(route('admin.settings.update'), ['return_days' => '0'])->assertSessionHasErrors('return_days');
    $this->actingAs($admin)->put(route('admin.settings.update'), ['delivery_weekdays' => ['9']])->assertSessionHasErrors('delivery_weekdays.0');

    expect(ShopSetting::count())->toBe(0);
});

it('keeps customers out', function () {
    $this->actingAs(User::factory()->create())->get(route('admin.settings'))->assertRedirect();
    $this->actingAs(User::factory()->create())->put(route('admin.settings.update'), ['return_days' => '30']);

    expect(ShopSetting::count())->toBe(0);
});
