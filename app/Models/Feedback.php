<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;
    Public $timestamps = false;

    public $primaryKey = 'feedback_id';
    protected $table = 'feedbacks';


    protected $fillable = [
        'feedback_content',
        'order_id',
        'rating',
    ];


    public function order()
    {
        return $this->belongsTo(Order::class , 'order_id');
    }
    public function parent()
    {
        return $this->belongsToMany(Parents::class , 'parent_feedback' , 'feedback_id' , 'user_id');
    }
}
