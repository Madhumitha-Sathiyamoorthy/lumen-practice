<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class ChitCustomer extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'chitCustomer';
}
