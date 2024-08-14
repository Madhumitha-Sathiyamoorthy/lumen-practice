<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLogin extends Model
{
    protected $connection = 'mysql';
    protected $table = 'user_login';
    public function userRoles()
    {
        return $this->belongsTo(UserRoles::class,'id');
    }
}
