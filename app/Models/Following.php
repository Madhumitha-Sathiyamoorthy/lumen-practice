<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Following extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'following';

    public function users()
    {
        return $this->belongsTo(Users::class);
    }
    // public function posts()
    // {
    //     return $this->belongsTo(Posts::class,'userId');
    // }
}
