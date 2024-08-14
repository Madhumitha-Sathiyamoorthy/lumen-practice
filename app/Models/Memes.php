<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Memes extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'memes';
}
