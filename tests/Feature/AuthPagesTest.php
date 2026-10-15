<?php

use App\Models\User;

/**
 * The sign-in family has its own shell: half photograph, half form, with the
 * shop's header and footer set aside so a visitor signing in has one thing to
 * do — and a way back to the shop.
 */
it('gives the sign-in pages a focused shell with a way back', function (string $route) {
    $html = $this->get(route($route))->assertOk()->getContent();

    expect($html)
        ->toContain(route('furnish.index'))
        ->toContain(__('diwan.auth.back_to_shop'))
        ->not->toContain('x-data="siteHeader"')
        ->not->toContain('id="newsletterEmail"');
})->with(['login', 'register', 'password.request']);

it('lets the visitor switch between signing in and creating an account', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('href="'.route('register').'"', false)
        ->assertSee('aria-current="page"', false);
});

it('renders the sign-in page in Arabic, right to left', function () {
    $this->withSession(['locale' => 'ar'])
        ->get(route('login'))
        ->assertOk()
        ->assertSee('dir="rtl"', false)
        ->assertSee(__('diwan.auth.back_to_shop', [], 'ar'));
});

it('says which field was wrong, next to the field', function () {
    $user = User::factory()->create();

    $this->from(route('login'))
        ->post(route('login'), ['email' => $user->email, 'password' => 'not-the-password'])
        ->assertRedirect(route('login'));

    $this->get(route('login'))
        ->assertSee('aria-invalid="true"', false)
        ->assertSee('aria-describedby="field-email-error"', false);
});

it('offers to show the password without giving it a value', function () {
    $html = $this->get(route('login'))->getContent();

    expect($html)
        ->toContain('x-data="passwordField"')
        ->toMatch('/<input id="field-password" name="password" type="password"(?![^>]*value=)/');
});
