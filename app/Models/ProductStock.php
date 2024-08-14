<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProductStock extends Model 
{
    protected $connection = 'mysql';  
    protected $table = 'product_stock';
}