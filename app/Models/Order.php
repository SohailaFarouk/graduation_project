<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    Public $timestamps = false;
    public $primaryKey = 'order_id';
    protected $table = 'orders';

    protected $fillable = [
        'cart_id',
        'order_amount',
        'order_details',
        'order_number',
        'user_id',
        'status',
        'payment_status'
    ];


    public function cart()
    {
        return $this->hasOne(Cart::class , 'cart_id');
    }
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
    public function feedback()
    {
        return $this->hasMany(Feedback::class , 'order_id');
    }
    public function parent()
    {
        return $this->belongsTo(Parents::class , 'user_id' );
    }
}

