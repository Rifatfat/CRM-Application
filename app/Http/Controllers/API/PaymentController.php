<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Models\Contract;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    protected $service;

    public function __construct(PaymentService $service)
    {
        $this->service = $service;
    }

    private function authorizePayment(Payment $payment, Request $request): void
    {
        if ((int) $payment->contract?->client?->user_id !== (int) $request->user()->id) {
            abort(403, 'Unauthorized');
        }
    }

    // GET /api/payments
    public function index(Request $request)
    {
        $payments = Payment::with(['contract.client', 'contract.service'])
            ->ownedBy((int) $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $payments
        ]);
    }

    // GET /api/payments/{payment}
    public function show(Request $request, Payment $payment)
    {
        $payment->load(['contract.client', 'contract.service']);
        $this->authorizePayment($payment, $request);

        return response()->json([
            'status' => 'success',
            'data' => $payment
        ]);
    }

    // POST /api/payments
    public function store(Request $request)
    {
        $data = $request->validate([
            'contract_id' => [
                'required',
                Rule::exists('contracts', 'id')->where(function ($query) use ($request) {
                    $query->whereIn('client_id', function ($clientQuery) use ($request) {
                        $clientQuery->select('id')
                            ->from('clients')
                            ->where('user_id', $request->user()->id);
                    });
                }),
            ],
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'status' => 'required|in:pending,paid,failed'
        ]);

        $contract = Contract::with('payments')
            ->ownedBy((int) $request->user()->id)
            ->findOrFail($data['contract_id']);

        $paidTotal = $contract->payments()
            ->where('status', 'paid')
            ->sum('amount');
        $pendingTotal = $contract->payments()
            ->where('status', 'pending')
            ->sum('amount');
        $displayRemainingBalance = max(0, (float) $contract->contract_value - (float) $paidTotal);
        $availableBalance = max(0, (float) $contract->contract_value - (float) $paidTotal - (float) $pendingTotal);

        if ($data['status'] !== 'failed' && (float) $data['amount'] > $availableBalance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment amount exceeds the remaining contract balance.',
                'data' => [
                    'remaining_balance' => $displayRemainingBalance,
                    'available_balance' => $availableBalance,
                ],
            ], 422);
        }

        try {
            $payment = $this->service->create($data);

            return response()->json([
                'status' => 'success',
                'data' => $payment,
                'meta' => [
                    'remaining_balance' => max(0, $displayRemainingBalance - ($data['status'] === 'paid' ? (float) $data['amount'] : 0)),
                    'available_balance' => max(0, $availableBalance - ($data['status'] === 'failed' ? 0 : (float) $data['amount'])),
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process payment'
            ], 500);
        }
    }

    // DELETE /api/payments/{payment}
    public function destroy(Request $request, Payment $payment)
    {
        $payment->load('contract.client');
        $this->authorizePayment($payment, $request);

        $payment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Payment deleted successfully'
        ]);
    }
}
