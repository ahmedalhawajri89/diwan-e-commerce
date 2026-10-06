<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        $contact = Contact::create($data);

        // The message is saved before any mail is attempted, and the panel's
        // Messages page reads it from there. So a mail failure is the shop's
        // problem, not the visitor's: it is logged, and they are told their
        // message arrived, which it did.
        try {
            Mail::to(config('mail.contact_to'))->send(new ContactMail($contact));
        } catch (\Throwable $e) {
            Log::error('Contact mail failed', ['contact_id' => $contact->id, 'error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', __('diwan.flash.contact_sent'));
    }
}
