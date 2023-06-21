<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionProduct extends Model
{
    use HasFactory;
    public $timestamps = false;
    
    protected $fillable = [
        'transaction_id',
        'inventory_id',
        'quantity',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
