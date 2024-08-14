<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProductCart extends Model 
{
    protected $connection = 'mysql';  
    protected $table = 'product_cart';
    public function product()
    {
        return $this->belongsTo(Products::class);
    }
    
}