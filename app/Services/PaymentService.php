<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Contract;

class PaymentService
{
    public function create(array $data)
    {
        $payment = Payment::create($data);

        if ($payment->status === 'paid') {
            $payment->contract->update([
                'status' => 'paid'
            ]);
        }

        return $payment;
    }
}