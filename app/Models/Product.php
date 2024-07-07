<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    Public $timestamps = false;
    public $primaryKey = 'product_id';
    protected $fillable=[
        'product_name',
        'product_description',
        'product_specification',
        'product_price',
        'product_type',
        'product_image',
        'quantity',
    ];

    public function parents()
    {
        return $this->belongsToMany(Parents::class , 'parent_product','product_id','user_id' );
    }
    public function admin()
    {
        return $this->belongsToMany(Admin::class , 'admin_product','product_id','user_id');
    }
    public function cart()
    {
        return $this->belongsToMany(Cart::class ,'product_cart' , 'product_id' , 'cart_id');
    }

}
