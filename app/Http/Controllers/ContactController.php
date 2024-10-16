<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;
use App\Models\Contact;
use App\Mail\AdminNotificationMail;
use OpenApi\Annotations as OA;

class ContactController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/contacts",
     *     tags={"Contact"},
     *     summary="Retrieve all contact messages",
     *     description="Fetches a list of all contact messages, ordered by the most recent first.",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ContactSchema")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $messages = Contact::orderBy('created_at', 'desc')->get(); // or paginate(10);
        return response()->json($messages);
    }
    /**
     * @OA\Post(
     *     path="/api/contact",
     *     tags={"Contact"},
     *     summary="Submit a contact form message",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ContactSchema")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Message sent successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ContactSchema")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string|min:3',
        ]);
        $contact = Contact::create($validatedData);

        Mail::to(config('mail.admin_email'))->send(new AdminNotificationMail($contact));
        Mail::to($contact->email)->send(new ContactMail($contact));

        return response()->json([
            'message' => 'Thank you for contacting us! Your message has been sent.',
            'data' => $contact
        ], 201);
    }
/**
     * @OA\Delete(
     *     path="/api/contact/{id}",
     *     tags={"Contact"},
     *     summary="Delete a contact message",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the contact message to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact message deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contact message not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        return response()->json(['message' => 'Contact message deleted successfully']);
    }
}
