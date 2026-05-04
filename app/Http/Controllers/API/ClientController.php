<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    // GET /api/clients
    public function index()
    {
        $clients = Client::latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $clients
        ]);
    }

    // POST /api/clients
    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'industry'     => 'required|string|max:255',
            'address'      => 'required|string',
            'notes'        => 'nullable|string',
        ]);

        $client = Client::create($data);

        return response()->json([
            'status' => 'success',
            'data' => $client
        ], 201);
    }

    // GET /api/clients/{client}
    public function show(Client $client)
    {
        return response()->json([
            'status' => 'success',
            'data' => $client
        ]);
    }

    // PUT /api/clients/{client}
    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'company_name' => 'sometimes|required|string|max:255',
            'industry'     => 'sometimes|required|string|max:255',
            'address'      => 'sometimes|required|string',
            'notes'        => 'nullable|string',
        ]);

        $client->update($data);

        return response()->json([
            'status' => 'success',
            'data' => $client
        ]);
    }

    // DELETE /api/clients/{client}
    public function destroy(Client $client)
    {
        $client->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Client deleted successfully'
        ]);
    }

    // GET /api/clients/{client}/contacts
    public function contacts(Client $client)
    {
        $client->load('contacts');

        return response()->json([
            'status' => 'success',
            'data' => $client->contacts
        ]);
    }

    // GET /api/clients/{client}/contracts
    public function contracts(Client $client)
    {
        $client->load('contracts');

        return response()->json([
            'status' => 'success',
            'data' => $client->contracts
        ]);
    }

    // GET /api/clients/{client}/documents
    public function documents(Client $client)
    {
        $client->load('documents');

        return response()->json([
            'status' => 'success',
            'data' => $client->documents
        ]);
    }
}