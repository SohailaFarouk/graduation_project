<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    Public $timestamps = false;

    public $primaryKey = 'payment_id';
    protected $table = 'payments';

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
    public function doctor()
    {
        return $this->belongsToMany(Doctor::class , 'doctor_payment', 'payment_id' , 'user_id');
    }
    public function parents()
    {
        return $this->belongsToMany(Parents::class , 'parent_payment', 'payment_id' , 'user_id');
    }
    public function admin()
    {
        return $this->belongsToMany(Admin::class , 'admin_payment', 'payment_id' , 'user_id');
    }

}
