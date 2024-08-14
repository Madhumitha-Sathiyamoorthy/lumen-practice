<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Products extends Model 
{
    protected $connection = 'mysql';  
    protected $table = 'products';

    public function productUsers()
    {
        return $this->belongsTo(ProductUsers::class);
    }   
}