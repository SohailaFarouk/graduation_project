<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;
    Public $timestamps = false;

    public $primaryKey = 'voucher_id';
    protected $table = 'vouchers';
    protected $fillable=[
        'voucher_code',
        'voucher_percentage'
    ];

    public function payment()
    {
        return $this->hasMany(Payment::class);
    }
    public function parents()
    {
        return $this->belongsToMany(Parents::class , 'parent_voucher', 'voucher_id', 'user_id');
    }
    public function admin()
    {
        return $this->belongsToMany(admin::class , 'admin_voucher' ,  'voucher_id', 'user_id');
    }
}
