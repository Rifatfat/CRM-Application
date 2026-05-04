<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $service;

    public function __construct(PaymentService $service)
    {
        $this->service = $service;
    }

    // GET /api/payments
    public function index()
    {
        $payments = Payment::with('contract')->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $payments
        ]);
    }

    // GET /api/payments/{payment}
    public function show(Payment $payment)
    {
        $payment->load('contract');

        return response()->json([
            'status' => 'success',
            'data' => $payment
        ]);
    }

    // POST /api/payments
    public function store(Request $request)
    {
        $data = $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'status' => 'required|in:pending,paid,failed'
        ]);

        try {
            $payment = $this->service->create($data);

            return response()->json([
                'status' => 'success',
                'data' => $payment
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process payment'
            ], 500);
        }
    }

    // DELETE /api/payments/{payment}
    public function destroy(Payment $payment)
    {
        $payment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Payment deleted successfully'
        ]);
    }
}