<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total_price',
        'order_number'
    ];

    protected $appends = ['items_count'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function items(){
        return $this->hasMany(OrderItem::class);
    }

     // Generating random order number automatically if not set
    protected static function booted()
    {
        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = 'ORD-' . strtoupper(uniqid());
            }
        });
    }

    // Accessor for item count
    public function getItemsCountAttribute()
    {
        return $this->items()->count();
    }
}
