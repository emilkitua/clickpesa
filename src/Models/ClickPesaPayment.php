<?php

namespace EmilKitua\ClickPesa\Models;

use Illuminate\Database\Eloquent\Model;

class ClickPesaPayment extends Model
{
    protected $table = 'clickpesa_payments';

    protected $fillable = [
        'reference_id',
        'external_id',
        'payment_method',
        'phone_number',
        'card_number_masked',
        'amount',
        'currency',
        'status',
        'status_detail',
        'request_payload',
        'response_payload',
        'paid_at',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'paid_at' => 'datetime',
    ];

    // Optional: default values
    protected $attributes = [
        'currency' => 'TZS',
        'status' => 'pending',
    ];
}