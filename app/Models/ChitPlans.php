<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class ChitPlans extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'chitPlans';
}
