<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
// use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\Model;

class Comments extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'comments';
    protected $fillable = [
        'userId',
        'postId',
        'comment',
        "created_at",
        "updated_at"
    ];
    public function posts()
    {
        return $this->belongsTo(Posts::class);
    }
    public function userDetails()
    {
        return $this->belongsTo(Users::class,'userId')->select('name','email','mobile');
    }
    public function subComments()
    {
        return $this->hasMany(SubComments::class,'commentId')->select('commentId','userId','comment');
    }
}
