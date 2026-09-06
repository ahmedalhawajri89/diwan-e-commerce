<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        $contact = Contact::create($request->all());

        try {
            Mail::to('admin@furnish.com')->send(new ContactMail($contact));
        } catch (\Exception $e) {
            // If Mailtrap is not configured, it will fail here, but we still saved it to DB.
            return redirect()->back()->with('error', 'Message saved, but email failed to send. Check your Mailtrap configuration.');
        }

        return redirect()->back()->with('success', 'Thank you! Your message has been sent successfully.');
    }
}
