<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    private function authorizeContact(Contact $contact, Request $request): void
    {
        if ((int) $contact->client?->user_id !== (int) $request->user()->id) {
            abort(403, 'Unauthorized');
        }
    }

    // GET /api/contacts
    public function index(Request $request)
    {
        $contacts = Contact::with('client')
            ->ownedBy((int) $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $contacts
        ]);
    }

    // GET /api/contacts/{contact}
    public function show(Request $request, Contact $contact)
    {
        $contact->load('client');
        $this->authorizeContact($contact, $request);

        return response()->json([
            'status' => 'success',
            'data' => $contact
        ]);
    }

    // POST /api/contacts
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => [
                'required',
                Rule::exists('clients', 'id')->where('user_id', $request->user()->id),
            ],
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
        $contact->load('client');
        $this->authorizeContact($contact, $request);

        $data = $request->validate([
            'client_id' => [
                'sometimes',
                Rule::exists('clients', 'id')->where('user_id', $request->user()->id),
            ],
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
    public function destroy(Request $request, Contact $contact)
    {
        $contact->load('client');
        $this->authorizeContact($contact, $request);

        $contact->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Contact deleted successfully'
        ]);
    }
}
