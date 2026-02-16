<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'invoice_number',
        'subtotal',        // Sesuai Migration
        'shipping_cost',   // Sesuai Migration
        'grand_total',     // Sesuai Migration
        'status',
        'payment_status',
        'note',         // Sesuai Migration
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        // Pastikan foreign key di tabel order_items adalah 'transaction_id'
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Helper untuk mengambil pembayaran terbaru/aktif
    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

}


