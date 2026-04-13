<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    // GET /api/contracts - Get all contracts
    public function index()
    {
        $contracts = Contract::all();
        return response()->json($contracts, 200);
    }

    // POST /api/contracts - Create new contract
    public function store(Request $request)
    {
        $request->validate([
            'client_id'      => 'required|exists:clients,id',
            'service_id'     => 'required|exists:services,id',
            'contract_value' => 'required|numeric|min:0',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after:start_date',
            'status'         => 'required|string|in:active,inactive,pending,expired',
        ]);

        $contract = Contract::create($request->all());
        return response()->json($contract, 201);
    }

    // GET /api/contracts/{id} - Get one contract
    public function show($id)
    {
        $contract = Contract::findOrFail($id);
        return response()->json($contract, 200);
    }

    // PUT /api/contracts/{id} - Update contract
    public function update(Request $request, $id)
    {
        $contract = Contract::findOrFail($id);
        $contract->update($request->all());
        return response()->json($contract, 200);
    }

    // DELETE /api/contracts/{id} - Delete contract
    public function destroy($id)
    {
        $contract = Contract::findOrFail($id);
        $contract->delete();
        return response()->json(['message' => 'Contract deleted successfully'], 200);
    }

    // PATCH /api/contracts/{id}/status - Update only status
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:active,inactive,pending,expired',
        ]);

        $contract = Contract::findOrFail($id);
        $contract->update(['status' => $request->status]);
        return response()->json($contract, 200);
    }

    // GET /api/contracts/{id}/client
    public function getClient($id)
    {
        $contract = Contract::findOrFail($id);
        return response()->json($contract->client, 200);
    }

    // GET /api/contracts/{id}/service
    public function getService($id)
    {
        $contract = Contract::findOrFail($id);
        return response()->json($contract->service, 200);
    }

    // GET /api/contracts/{id}/payments
    public function getPayments($id)
    {
        $contract = Contract::findOrFail($id);
        return response()->json($contract->payments, 200);
    }
}