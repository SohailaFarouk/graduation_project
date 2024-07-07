<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;
    Public $timestamps = false;

    protected $primaryKey = 'appointment_id';

    protected $fillable = [
        'appointment_date',
    ];



    public function session()
    {
        return $this->hasOne(Session::class);
    }
    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_appointment', 'appointment_id', 'user_id');
    }
}
