<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{


    // Submit contact form (public)
    public function store(ContactRequest $request): JsonResponse
    {
        $contact = Contact::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Your message has been submitted successfully.',
            'data' => [
                'id' => $contact->id,
            ],
        ], 201);
    }

    // Get all contact submissions (admin)
    public function index(): JsonResponse
    {
        $contacts = Contact::latest()->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $contacts,
        ]);
    }

    // Delete a contact submission (admin)
    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Contact message deleted successfully.',
        ]);
    }


}

