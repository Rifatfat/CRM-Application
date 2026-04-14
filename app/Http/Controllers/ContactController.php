<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $contact = Contact::create($validator->validated());

        return response()->json([
            'message' => 'Contact created successfully',
            'data' => $contact
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);

        $contact->update($request->only([
            'name', 'position', 'email', 'phone'
        ]));

        return response()->json([
            'message' => 'Contact updated',
            'data' => $contact
        ]);
    }

    public function destroy($id)
    {
        Contact::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Contact deleted'
        ]);
    }
}