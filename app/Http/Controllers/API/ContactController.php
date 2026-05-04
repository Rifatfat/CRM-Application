<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    // GET /api/contacts
    public function index()
    {
        $contacts = Contact::latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $contacts
        ]);
    }

    // GET /api/contacts/{contact}
    public function show(Contact $contact)
    {
        return response()->json([
            'status' => 'success',
            'data' => $contact
        ]);
    }

    // POST /api/contacts
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20'
        ]);

        $contact = Contact::create($data);

        return response()->json([
            'status' => 'success',
            'data' => $contact
        ], 201);
    }

    // PUT /api/contacts/{contact}
    public function update(Request $request, Contact $contact)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'position' => 'nullable|string|max:255',
            'email' => 'sometimes|required|email',
            'phone' => 'sometimes|required|string|max:20'
        ]);

        $contact->update($data);

        return response()->json([
            'status' => 'success',
            'data' => $contact
        ]);
    }

    // DELETE /api/contacts/{contact}
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Contact deleted successfully'
        ]);
    }
}