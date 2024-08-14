<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Posts extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'post';
    public function comments()
    {
        return $this->hasMany(Comments::class,'postId')->select('_id','postId','userId','comment');
    }
    public function users()
    {
        return $this->belongsToMany(Users::class);
    }

    // public function following()
    // {
    //     return $this->hasMany(Following::class,'userId');
    // }
}
