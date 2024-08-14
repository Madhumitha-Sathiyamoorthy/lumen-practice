<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model 
{
    protected $connection = 'mysql';  
    protected $table = 'otp';
    protected $fillable = [
        'number', 'otp',
    ];
}