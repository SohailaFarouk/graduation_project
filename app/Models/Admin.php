<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends Model
{
    use HasFactory;
    Public $timestamps = false;
    public $primaryKey = 'user_id';

    protected $fillable = [
        'user_id',
        'event_id',
    ];
    public function product()
    {
        return $this->belongsToMany(Product::class , 'admin_product','product_id','user_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class , 'event_id');
    }

    public function payments()
    {
        return $this->belongsToMany(Payment::class)->withPivot('sales_report_path');
    }

    public function user()
    {
        return $this->belongsToMany(User::class);
    }
    public function voucher()
    {
        return $this->belongsToMany (voucher::class , 'admin_voucher' ,  'voucher_id', 'user_id');
    }
}
