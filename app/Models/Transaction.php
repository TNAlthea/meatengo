<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    public $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'cashier_id',
        'payment_method',
        'payment_gateway',
        'total_before_discount',
        'discount',
        'plastic_bag',
        'payment_method',
        'total'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sold_at' => 'datetime',
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
