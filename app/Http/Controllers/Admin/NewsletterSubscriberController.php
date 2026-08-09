<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterSubscriberController extends Controller
{
    public function index()
    {
        $subscribers = NewsletterSubscriber::latest()->paginate(20);
        return view('admin.newsletters.index', compact('subscribers'));
    }

    public function destroy(NewsletterSubscriber $newsletter)
    {
        $newsletter->delete();
        flash()->success('Subscriber removed successfully.');
        return back();
    }
}
