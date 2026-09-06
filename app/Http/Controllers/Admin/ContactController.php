<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;

class ContactController extends Controller
{
    public function index()
    {
        $messages = Contact::latest()->paginate(15);

        return view('admin.contacts.index', compact('messages'));
    }

    public function destroy($id)
    {
        $message = Contact::findOrFail($id);
        $message->delete();

        flash()->success('Message deleted successfully.');

        return redirect()->route('admin.contacts.index');
    }
}
