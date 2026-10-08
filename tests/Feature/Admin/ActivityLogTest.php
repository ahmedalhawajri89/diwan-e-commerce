<?php

use App\Models\ActivityLog;
use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * The activity log: what an administrator did, each field before and after,
 * their sign-ins, and nothing a customer did. Kept for a year.
 */
function actAdmin(string $name = 'Operator'): User
{
    $user = User::factory()->create(['name' => $name, 'email' => strtolower($name).'@diwan.test', 'password' => bcrypt('secret-123')]);
    $user->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    return $user->fresh();
}

function actCustomer(): User
{
    $user = User::factory()->create(['name' => 'Rana', 'password' => bcrypt('secret-123')]);
    $user->roles()->attach(Role::firstOrCreate(['name' => 'customer']));

    return $user->fresh();
}

function actPiece(array $product = []): Product
{
    $p = Product::factory()->create(array_merge(['category_id' => Category::factory()->create()->id, 'name' => 'Oak stool', 'status' => 'active'], $product));
    ProductVariant::factory()->create(['product_id' => $p->id, 'stock' => 5, 'price' => 100]);

    return $p;
}

it('writes what an administrator changed, field by field, and nothing for a save that changed nothing', function () {
    $admin = actAdmin();
    $product = actPiece();
    ActivityLog::query()->delete();

    $this->actingAs($admin);
    $product->update(['status' => 'draft', 'name_ar' => 'مقعد بلوط']);
    $product->update(['status' => 'draft']);

    $log = ActivityLog::sole();
    expect($log->action)->toBe('updated')
        ->and($log->user_id)->toBe($admin->id)
        ->and($log->entity_label)->toBe('Oak stool')
        ->and($log->changes['status'])->toBe(['active', 'draft'])
        ->and($log->changes['name_ar'][1])->toBe('مقعد بلوط')
        ->and(array_keys($log->changes))->toEqualCanonicalizing(['status', 'name_ar'])
        ->and($log->changes)->not->toHaveKey('updated_at');
});

it('keeps a deleted thing readable by the name it had', function () {
    $admin = actAdmin();
    $category = Category::factory()->create(['name' => 'Study']);

    $this->actingAs($admin)->delete(route('admin.categories.destroy', $category));

    $log = ActivityLog::where('action', 'deleted')->sole();
    expect($log->entity_label)->toBe('Study')->and($log->url())->toBeNull();
});

it('never writes a password or a remember token', function () {
    $admin = actAdmin();
    $other = actAdmin('Second');
    ActivityLog::query()->delete();

    $this->actingAs($admin);
    $other->update(['password' => bcrypt('new-secret'), 'remember_token' => 'abc', 'name' => 'Second Operator']);

    expect(ActivityLog::sole()->changes)->toBe(['name' => ['Second', 'Second Operator']]);
});

it('leaves out what a customer does on their own', function () {
    Mail::fake();
    $customer = actCustomer();
    $product = actPiece();
    ActivityLog::query()->delete();

    $this->actingAs($customer)->post(route('reviews.store', $product), ['rating' => 5, 'comment' => 'Sturdy.']);
    $this->actingAs($customer);
    $customer->update(['name' => 'Rana H.']);

    expect(ActivityLog::count())->toBe(0);
});

it('writes one line per row of a bulk action', function () {
    $admin = actAdmin();
    $a = actPiece(['name' => 'A']);
    $b = actPiece(['name' => 'B']);
    ActivityLog::query()->delete();

    $this->actingAs($admin)->post(route('admin.products.bulk'), ['ids' => [$a->id, $b->id], 'action' => 'inactive']);

    expect(ActivityLog::where('action', 'updated')->pluck('entity_label')->sort()->values()->all())->toBe(['A', 'B']);
});

it('writes an order moved by an administrator, by its number', function () {
    Mail::fake();
    $admin = actAdmin();
    $customer = actCustomer();
    $address = Address::create(['user_id' => $customer->id, 'country' => 'Jordan', 'city' => 'Amman', 'street' => 'Street 1']);
    $order = Order::create(['user_id' => $customer->id, 'shipping_address_id' => $address->id, 'total_price' => 100, 'status' => 'pending', 'payment_status' => 'pending']);

    $this->actingAs($admin)->post(route('admin.orders.advance', $order));

    $log = ActivityLog::where('entity_type', Order::class)->sole();
    expect($log->entity_label)->toBe($order->number())->and($log->changes)->toBe(['status' => ['pending', 'processing']]);
});

it('writes an administrator\'s sign-in and a failed attempt on their address, never a customer\'s', function () {
    $admin = actAdmin();
    $customer = actCustomer();

    $this->post(route('login'), ['email' => $admin->email, 'password' => 'secret-123']);
    auth()->logout();
    $this->post(route('login'), ['email' => $admin->email, 'password' => 'wrong']);
    $this->post(route('login'), ['email' => $customer->email, 'password' => 'secret-123']);

    expect(ActivityLog::pluck('action')->all())->toBe(['signed_in', 'sign_in_failed'])
        ->and(ActivityLog::first()->ip_address)->toBe('127.0.0.1')
        ->and(ActivityLog::first()->entity_type)->toBe('session');
});

it('lists the log by kind, person and name, in the reader\'s language', function () {
    $admin = actAdmin();
    $other = actAdmin('Second');
    $product = actPiece();
    ActivityLog::query()->delete();

    $this->actingAs($admin);
    $product->update(['status' => 'draft']);
    $this->actingAs($other);
    Category::factory()->create(['name' => 'Hallway']);

    $page = $this->actingAs($admin)->get(route('admin.activity.index'))->assertOk();
    $chips = collect($page->viewData('chips'))->keyBy(fn ($c) => $c['value'] ?? 'all');
    expect($chips['all']['count'])->toBe(2)->and($chips['catalogue']['count'])->toBe(2)->and($chips['orders']['count'])->toBe(0);

    expect($this->actingAs($admin)->get(route('admin.activity.index', ['person' => $other->id]))->viewData('logs')->pluck('entity_label')->all())->toBe(['Hallway'])
        ->and($this->actingAs($admin)->get(route('admin.activity.index', ['q' => 'Oak']))->viewData('logs')->pluck('entity_label')->all())->toBe(['Oak stool']);

    $this->actingAs($admin)->withSession(['locale' => 'ar'])->get(route('admin.activity.index'))
        ->assertSee(__('admin.activity.sentence.updated', ['subject' => __('admin.activity.subjects.product', [], 'ar')], 'ar'))
        ->assertSee(__('admin.products.status_values.draft', [], 'ar'));
});

it('exports the log as shown, and keeps customers out', function () {
    $admin = actAdmin();
    $product = actPiece();
    $this->actingAs($admin);
    $product->update(['price' => 1]);
    $product->update(['status' => 'draft']);

    $csv = $this->actingAs($admin)->get(route('admin.activity.export', ['kind' => 'catalogue']))->streamedContent();
    expect($csv)->toContain('Oak stool')->toContain('status');

    $this->actingAs(actCustomer())->get(route('admin.activity.index'))->assertRedirect(route('furnish.index'));
});

it('lets a year go, and keeps the rest', function () {
    $admin = actAdmin();
    ActivityLog::query()->delete();
    $old = ActivityLog::create(['user_id' => $admin->id, 'action' => 'signed_in', 'entity_type' => 'session']);
    $old->forceFill(['created_at' => now()->subYear()->subDay()])->save();
    ActivityLog::create(['user_id' => $admin->id, 'action' => 'signed_in', 'entity_type' => 'session']);

    $this->artisan('model:prune', ['--model' => [ActivityLog::class]])->assertSuccessful();

    expect(ActivityLog::count())->toBe(1);
});

it('shows a piece\'s latest changes on its page, and an administrator\'s on theirs', function () {
    $admin = actAdmin();
    $product = actPiece();
    $this->actingAs($admin);
    $product->variants()->first()->update(['stock' => 2]);

    $this->actingAs($admin)->get(route('admin.products.show', $product))
        ->assertSee(__('admin.activity.latest'))
        ->assertSee('Oak stool');

    $this->actingAs($admin)->get(route('admin.users.show', $admin))->assertSee(__('admin.activity.their_latest'));
});
