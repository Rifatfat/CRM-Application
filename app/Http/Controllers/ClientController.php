<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    // GET /api/clients - Get all clients
    public function index()
    {
        $clients = Client::all();
        return response()->json($clients, 200);
    }

    // POST /api/clients - Create new client
    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'industry'     => 'required|string|max:255',
            'address'      => 'required|string',
            'notes'        => 'nullable|string',
        ]);

        $client = Client::create($request->all());
        return response()->json($client, 201);
    }

    // GET /api/clients/{id} - Get one client
    public function show($id)
    {
        $client = Client::findOrFail($id);
        return response()->json($client, 200);
    }

    // PUT /api/clients/{id} - Update client
    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $client->update($request->all());
        return response()->json($client, 200);
    }

    // DELETE /api/clients/{id} - Delete client
    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();
        return response()->json(['message' => 'Client deleted successfully'], 200);
    }

    // GET /api/clients/{id}/contacts
    public function getContacts($id)
    {
        $client = Client::findOrFail($id);
        return response()->json($client->contacts, 200);
    }

    // GET /api/clients/{id}/contracts
    public function getContracts($id)
    {
        $client = Client::findOrFail($id);
        return response()->json($client->contracts, 200);
    }

    // GET /api/clients/{id}/documents
    public function getDocuments($id)
    {
        $client = Client::findOrFail($id);
        return response()->json($client->documents, 200);
    }
}