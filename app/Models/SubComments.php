<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class SubComments extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'subComments';

    public function comments()
    {
        return $this->belongsTo(Comments::class);
    }

    public function userDetails()
    {
        return $this->belongsTo(Users::class,'userId')->select('name','email','mobile');
    }
}
