<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AccessLimit extends Model 
{
    protected $connection = 'mysql';  
    protected $table = 'accessLimit';
    protected $fillable = [
        'action_id', 'action_type',
    ];
}