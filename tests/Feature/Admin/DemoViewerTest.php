<?php

use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DemoAccountsSeeder;
use Illuminate\Support\Facades\Hash;

/**
 * The public demo's viewer: sees the whole panel, changes nothing, and
 * cannot be locked by a visitor who knows its (published) password.
 */
function demoViewer(): User
{
    $viewer = User::factory()->create(['email' => 'viewer@demo.test', 'password' => 'look-but-do-not-touch']);
    $viewer->roles()->attach(Role::firstOrCreate(['name' => User::VIEWER_ROLE]));

    return $viewer;
}

it('lets the viewer into every page of the panel', function () {
    $viewer = demoViewer();

    foreach (['admin.dashboard', 'admin.products.index', 'admin.orders.index', 'admin.settings', 'admin.returns.index'] as $page) {
        $this->actingAs($viewer)->get(route($page))->assertOk()->assertSee(__('admin.demo.banner'));
    }
});

it('turns back every write from the viewer, and changes nothing', function () {
    $viewer = demoViewer();
    $category = Category::factory()->create(['name' => 'Office']);

    $this->actingAs($viewer)->from(route('admin.categories.index'))
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect(route('admin.categories.index'))
        ->assertSessionHas('error', __('admin.demo.read_only'));
    expect(Category::find($category->id))->not->toBeNull();

    $this->actingAs($viewer)->put(route('admin.settings.update'), ['return_days' => '30'])->assertSessionHas('error');
    $this->actingAs($viewer)->postJson(route('admin.notifications.read-all'))->assertStatus(403)->assertJson(['message' => __('admin.demo.read_only')]);
});

it('does not let anyone change, lock or delete the demo account', function () {
    $viewer = demoViewer();

    $this->actingAs($viewer)->patch(route('profile.update'), ['name' => 'Taken', 'email' => 'mine@evil.test'])->assertSessionHas('error');
    $this->actingAs($viewer)->put(route('password.update'), ['current_password' => 'look-but-do-not-touch', 'password' => 'locked-out-1', 'password_confirmation' => 'locked-out-1'])->assertSessionHas('error');
    $this->actingAs($viewer)->delete(route('profile.destroy'), ['password' => 'look-but-do-not-touch'])->assertSessionHas('error');

    $viewer->refresh();
    expect($viewer->email)->toBe('viewer@demo.test')
        ->and(Hash::check('look-but-do-not-touch', $viewer->password))->toBeTrue();
});

it('never stops an administrator', function () {
    $admin = User::factory()->create();
    $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    $this->actingAs($admin)->put(route('admin.settings.update'), ['return_days' => '30'])->assertSessionHas('success');
    $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee(__('admin.demo.banner'));
});

it('keeps a plain customer out of the panel', function () {
    $this->actingAs(User::factory()->create())->get(route('admin.dashboard'))->assertRedirect();
});

it('creates the demo accounts only when the deployment asks, and prints only the viewer\'s password', function () {
    (new DemoAccountsSeeder)->run();
    expect(User::count())->toBe(0);

    config(['demo.viewer_email' => 'viewer@diwan.test', 'demo.viewer_password' => 'demo-1234',
        'demo.owner_email' => 'owner@diwan.test', 'demo.owner_password' => 'a-long-secret-nobody-sees']);
    (new DemoAccountsSeeder)->run();

    expect(User::where('email', 'viewer@diwan.test')->sole()->isViewer())->toBeTrue()
        ->and(User::where('email', 'owner@diwan.test')->sole()->isAdmin())->toBeTrue();

    $this->get(route('login'))
        ->assertSee('viewer@diwan.test · demo-1234')
        ->assertDontSee('a-long-secret-nobody-sees');
});
