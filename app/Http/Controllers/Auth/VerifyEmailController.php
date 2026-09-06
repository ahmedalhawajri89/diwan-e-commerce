<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended($this->destination($request).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended($this->destination($request).'?verified=1');
    }

    /**
     * Where a freshly verified user lands.
     *
     * This used to send everyone to the admin category list, so an ordinary
     * customer who verified their email was bounced straight back out by the
     * admin middleware and told they lacked administrative privileges. The
     * destination now mirrors AuthenticatedSessionController: admins to the
     * admin area, everyone else to the storefront.
     */
    private function destination(EmailVerificationRequest $request): string
    {
        $isAdmin = $request->user()->isAdmin();

        return $isAdmin
            ? route('admin.categories.index', absolute: false)
            : route('furnish.index', absolute: false);
    }
}
