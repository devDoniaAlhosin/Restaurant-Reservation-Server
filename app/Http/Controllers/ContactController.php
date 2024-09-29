<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;
use App\Models\Contact;

class ContactController extends Controller
{
    public function index()
    {

        $messages = Contact::orderBy('created_at', 'desc')->get(); // or paginate(10);


        return response()->json($messages);
    }

    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string|min:10',

        ]);


        $contact = Contact::create($validatedData);

        // Optionally, send an email
        Mail::to(config('mail.admin_email'))->send(new ContactMail($contact));

        // Return a response
        return response()->json([
            'message' => 'Thank you for contacting us!',
            'data' => $contact
        ], 201);
    }

    public function destroy($id)
{
    $contact = Contact::findOrFail($id);
    $contact->delete();

    return response()->json(['message' => 'Contact message deleted successfully']);
}

}
