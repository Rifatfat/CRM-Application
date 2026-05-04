<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    // GET /api/contracts
    public function index()
    {
        $contracts = Contract::with(['client', 'service'])->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $contracts
        ]);
    }

    // GET /api/contracts/{contract}
    public function show(Contract $contract)
    {
        $contract->load(['client', 'service', 'payments', 'documents']);

        return response()->json([
            'status' => 'success',
            'data' => $contract
        ]);
    }

    // POST /api/contracts
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'      => 'required|exists:clients,id',
            'service_id'     => 'required|exists:services,id',
            'contract_value' => 'required|numeric|min:0',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after:start_date',
            'status'         => 'required|in:active,inactive,pending,expired',
        ]);

        $contract = Contract::create($data);

        return response()->json([
            'status' => 'success',
            'data' => $contract
        ], 201);
    }

    // PUT /api/contracts/{contract}
    public function update(Request $request, Contract $contract)
    {
        $data = $request->validate([
            'client_id'      => 'sometimes|exists:clients,id',
            'service_id'     => 'sometimes|exists:services,id',
            'contract_value' => 'sometimes|numeric|min:0',
            'start_date'     => 'sometimes|date',
            'end_date'       => 'sometimes|date|after:start_date',
            'status'         => 'sometimes|in:active,inactive,pending,expired',
        ]);

        $contract->update($data);

        return response()->json([
            'status' => 'success',
            'data' => $contract
        ]);
    }

    // DELETE /api/contracts/{contract}
    public function destroy(Contract $contract)
    {
        $contract->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Contract deleted successfully'
        ]);
    }

    // PATCH /api/contracts/{contract}/status
    public function updateStatus(Request $request, Contract $contract)
    {
        $data = $request->validate([
            'status' => 'required|in:active,inactive,pending,expired',
        ]);

        $contract->update($data);

        return response()->json([
            'status' => 'success',
            'data' => $contract
        ]);
    }

    // GET /api/contracts/{contract}/client
    public function client(Contract $contract)
    {
        return response()->json([
            'status' => 'success',
            'data' => $contract->client
        ]);
    }

    // GET /api/contracts/{contract}/service
    public function service(Contract $contract)
    {
        return response()->json([
            'status' => 'success',
            'data' => $contract->service
        ]);
    }

    // GET /api/contracts/{contract}/payments
    public function payments(Contract $contract)
    {
        return response()->json([
            'status' => 'success',
            'data' => $contract->payments
        ]);
    }
}