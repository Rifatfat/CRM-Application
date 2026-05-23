<?php

namespace App\Services;

use App\Models\Payment;

class PaymentService
{
    public function create(array $data)
    {
        return Payment::create($data);
    }
}
