<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContractController extends Controller
{
    private function authorizeContract(Contract $contract, Request $request): void
    {
        if ((int) $contract->client?->user_id !== (int) $request->user()->id) {
            abort(403, 'Unauthorized');
        }
    }

    // GET /api/contracts
    public function index(Request $request)
    {
        $contracts = Contract::with(['client', 'service'])
            ->ownedBy((int) $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $contracts
        ]);
    }

    // GET /api/contracts/{contract}
    public function show(Request $request, Contract $contract)
    {
        $contract->load(['client', 'service', 'payments', 'documents']);
        $this->authorizeContract($contract, $request);

        return response()->json([
            'status' => 'success',
            'data' => $contract
        ]);
    }

    // POST /api/contracts
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'      => [
                'required',
                Rule::exists('clients', 'id')->where('user_id', $request->user()->id),
            ],
            'service_id'     => [
                'required',
                Rule::exists('services', 'id')->where('user_id', $request->user()->id),
            ],
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
        $contract->load('client');
        $this->authorizeContract($contract, $request);

        $data = $request->validate([
            'client_id'      => [
                'sometimes',
                Rule::exists('clients', 'id')->where('user_id', $request->user()->id),
            ],
            'service_id'     => [
                'sometimes',
                Rule::exists('services', 'id')->where('user_id', $request->user()->id),
            ],
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
    public function destroy(Request $request, Contract $contract)
    {
        $contract->load('client');
        $this->authorizeContract($contract, $request);

        $contract->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Contract deleted successfully'
        ]);
    }

    // PATCH /api/contracts/{contract}/status
    public function updateStatus(Request $request, Contract $contract)
    {
        $contract->load('client');
        $this->authorizeContract($contract, $request);

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
    public function client(Request $request, Contract $contract)
    {
        $contract->load('client');
        $this->authorizeContract($contract, $request);

        return response()->json([
            'status' => 'success',
            'data' => $contract->client
        ]);
    }

    // GET /api/contracts/{contract}/service
    public function service(Request $request, Contract $contract)
    {
        $contract->load(['client', 'service']);
        $this->authorizeContract($contract, $request);

        return response()->json([
            'status' => 'success',
            'data' => $contract->service
        ]);
    }

    // GET /api/contracts/{contract}/payments
    public function payments(Request $request, Contract $contract)
    {
        $contract->load(['client', 'payments']);
        $this->authorizeContract($contract, $request);

        return response()->json([
            'status' => 'success',
            'data' => $contract->payments
        ]);
    }
}
