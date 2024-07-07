<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    Public $timestamps = false;

    protected $fillable=[
        'event_name',
        'event_description',
        'start_date',
        'end_date',
        'event_price',
        'event_location',
        'event_status',
        'start_time',
        'end_time',
        'image',
        'number_of_tickets',
    ];
    protected $primaryKey = 'event_id';

    public function admin()
    {
        return $this->hasMany(Admin::class , 'event_id');
    }

    public function parent()
    {
        return $this->hasMany(Parents::class);
    }
    public function cart()
    {
        return $this->hasMany(Cart::class , 'event_id');
    }
}
