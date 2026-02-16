<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
    'transaction_id',
    'external_id',
    'xendit_id',
    'checkout_link',
    'status',
    'amount',
    'payment_method',
    'payment_channel',
    'paid_at',
    ];
    
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
