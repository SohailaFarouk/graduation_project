<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;
    Public $timestamps = false;

    public $primaryKey='subscription_id';

    protected $fillable=[
        'subscription_plan',
        'subscription_price',

    ];


    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
    public function parents()
    {
        return $this->hasMany(Parent::class, 'subscription_id');
    }
}
