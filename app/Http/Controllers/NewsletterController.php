<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $subscriber = NewsletterSubscriber::firstOrCreate(['email' => $validated['email']]);

        if ($subscriber->wasRecentlyCreated) {
            flash()->success('Thank you for subscribing to our newsletter!');
        } else {
            flash()->info('You are already subscribed to our newsletter.');
        }

        return back();
    }
}
