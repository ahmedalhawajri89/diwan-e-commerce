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
            session()->flash('success', __('diwan.flash.subscribed'));
        } else {
            session()->flash('status', __('diwan.flash.already_subscribed'));
        }

        return back();
    }
}
