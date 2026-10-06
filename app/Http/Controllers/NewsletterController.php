<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        // Its own error bag: a refused address showed its error under the
        // contact or profile page's email field, whichever page it was sent
        // from (B15), and never beside the field it came from.
        $validated = $request->validateWithBag('newsletter', [
            'email' => 'required|email|max:255',
        ]);

        $subscriber = NewsletterSubscriber::firstOrCreate(['email' => $validated['email']]);

        if ($subscriber->wasRecentlyCreated) {
            session()->flash('success', __('diwan.flash.subscribed'));
        } else {
            session()->flash('status', __('diwan.flash.already_subscribed'));
        }

        return back();
    }
}
