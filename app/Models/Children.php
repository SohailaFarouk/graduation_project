<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Children extends Model
{
    use HasFactory;
    Public $timestamps = false;
    protected $table = 'childrens';
    protected $primaryKey = 'user_id';
    protected $fillable = [
        'user_id',
        'name',
        'gender',
        'date_of_birth'
    ];

    public function parent()
    {
        return $this->belongsTo(Parents::class , 'user_id');
    }

    public function getAgeAttribute()
    {
        return Carbon::parse($this->date_of_birth)->age;
    }

}
