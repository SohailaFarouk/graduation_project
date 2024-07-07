<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;
    Public $timestamps = false;

    protected $fillable = [
        'user_id',
        'experience_years',
        'medical_profession',
        'clinic_address',
    ];

    public function payment()
    {
        return $this->belongsToMany(Payment::class, 'doctor_payment', 'user_id', 'user_id');
    }
    public function appointments()
    {
        return $this->belongsToMany(Appointment::class, 'doctor_appointment', 'user_id', 'appointment_id');
    }
}
