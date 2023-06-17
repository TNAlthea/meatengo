<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cashier_id',
        'sold_at',
        'total_amount',
        // Add any additional fillable columns for the Transaction entity
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cashier()
    {
        return $this->belongsTo(Cashier::class);
    }

    public function transactionProducts()
    {
        return $this->hasMany(TransactionProduct::class);
    }
}
