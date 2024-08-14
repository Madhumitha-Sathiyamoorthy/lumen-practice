<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProductUsers extends Model 
{
    protected $connection = 'mysql';  
    protected $table = 'product_users';

    public function productCart()
    {
        return $this->hasMany(ProductCart::class,'user_id');
    }
    
}