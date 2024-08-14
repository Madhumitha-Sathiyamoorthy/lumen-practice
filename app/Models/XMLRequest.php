<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class XMLRequest extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'xmlrequest';
}
