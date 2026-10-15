<?php

use App\Models\Cart;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;

/**
 * Every message the framework writes, in the visitor's language. Without
 * lang/ar/{validation,auth,passwords} they all fell back to English, and the
 * checkout's Arabic field names made them read "The الهاتف field is required."
 */
it('refuses a checkout field in Arabic, naming the field in Arabic', function () {
    $user = User::factory()->create();
    $cart = Cart::create(['user_id' => $user->id]);

    $this->actingAs($user)->withSession(['locale' => 'ar'])
        ->post(route('checkout.process'), ['country' => 'الأردن', 'city' => 'عمّان', 'street' => 'شارع'])
        ->assertSessionHasErrors(['phone' => 'حقل الهاتف مطلوب.']);
});

it('refuses a wrong password in Arabic', function () {
    $user = User::factory()->create(['password' => bcrypt('right-pass')]);

    $this->withSession(['locale' => 'ar'])
        ->post(route('login'), ['email' => $user->email, 'password' => 'wrong-pass'])
        ->assertSessionHasErrors(['email' => __('auth.failed', [], 'ar')]);

    expect(__('auth.failed', [], 'ar'))->not->toBe(__('auth.failed', [], 'en'));
});

it('says a duplicate email is already registered, and what to do', function () {
    $taken = User::factory()->create();

    $this->withSession(['locale' => 'ar'])
        ->post(route('register'), ['name' => 'Rana', 'email' => $taken->email, 'password' => 'secret-pass-1', 'password_confirmation' => 'secret-pass-1'])
        ->assertSessionHasErrors(['email' => 'هذا البريد مسجّل لدينا. سجّل دخولك، أو استعد كلمة المرور.']);
});

it('confirms a saved profile in words, not a slug', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->withSession(['locale' => 'ar'])
        ->patch(route('profile.update'), ['name' => 'Rana', 'email' => $user->email])
        ->assertSessionHas('success', __('diwan.flash.profile_updated', [], 'ar'));

    $this->actingAs($user)->get(route('profile.edit'))->assertDontSee('profile-updated');
});

it('keeps the Arabic framework files in step with the English ones', function () {
    foreach (['auth', 'passwords', 'pagination', 'validation'] as $file) {
        $en = require lang_path("en/{$file}.php");
        $ar = require lang_path("ar/{$file}.php");

        $missing = array_diff(array_keys(Arr::dot($en)), array_keys(Arr::dot($ar)));
        // Laravel's placeholder example under `custom` is not a message.
        $missing = array_filter($missing, fn ($k) => ! str_starts_with($k, 'custom') && $k !== 'attributes');

        expect(array_values($missing))->toBe([], "lang/ar/{$file}.php is missing keys");
    }
});

it('sends the password link in the shop\'s own email, in the visitor\'s language', function () {
    Notification::fake();
    $user = User::factory()->create(['name' => 'Rana']);

    $this->withSession(['locale' => 'ar'])->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        app()->setLocale('ar');
        $mail = $notification->toMail($user);
        $html = (string) $mail->render();

        return $mail->subject === __('diwan.mail.reset.subject', [], 'ar')
            && str_contains($html, 'dir="rtl"')
            && str_contains($html, __('diwan.mail.reset.cta', [], 'ar'))
            && ! str_contains($html, 'Regards');
    });
});
