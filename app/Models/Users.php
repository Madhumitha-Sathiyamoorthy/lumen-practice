<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Users extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'users';
    public function comments()
    {
        return $this->hasManyThrough(comments::class, SubComments::class,'userId');
    }
    public function subComments()
    {
        return $this->hasMany(SubComments::class,"userId");
    }
    public function posts()
    {
        return $this->hasMany(Posts::class,"userId");
    }
    public function following()
    {
        return $this->hasMany(Following::class,'userId');
    }
}
