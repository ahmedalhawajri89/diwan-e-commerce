<?php

use App\Mail\ContactMail;
use App\Models\Contact;
use Illuminate\Support\Facades\Mail;

/**
 * The contact form mailed every message to admin@furnish.com — a domain the
 * shop does not own — and, when mail failed, told the visitor in English to
 * "check your Mailtrap configuration".
 */
$message = [
    'name' => 'Rana',
    'email' => 'rana@example.test',
    'subject' => 'Sofa depth',
    'message' => 'Will the sofa pass a 78 cm door?',
];

it('sends the message to the configured address, never a hard-coded one', function () use ($message) {
    Mail::fake();
    config(['mail.contact_to' => 'owner@diwan.test']);

    $this->post(route('contact.store'), $message)->assertSessionHas('success');

    // ContactMail is queued (ShouldQueue), so it is pushed rather than sent here.
    Mail::assertQueued(ContactMail::class, fn ($mail) => $mail->hasTo('owner@diwan.test') && ! $mail->hasTo('admin@furnish.com'));
});

it('defaults to a reserved test address until the owner sets one', function () {
    expect(config('mail.contact_to'))->toEndWith('.test');
});

it('thanks the visitor in their language even when mail fails, because the message is saved', function () use ($message) {
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP down'));

    $this->withSession(['locale' => 'ar'])
        ->post(route('contact.store'), $message)
        ->assertSessionHas('success', __('diwan.flash.contact_sent', [], 'ar'))
        ->assertSessionMissing('error');

    expect(Contact::count())->toBe(1);
});
