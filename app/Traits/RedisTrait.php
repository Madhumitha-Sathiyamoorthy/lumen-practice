<?php

namespace App\Traits;

use Illuminate\Support\Str;
use App\Models\ChitCustomer;
use App\Models\ChitPlans;
use Illuminate\Support\Facades\Redis;


trait RedisTrait
{
    private $redis;

    public function __construct()
    {
        $this->redis = Redis::connection();
    }

    public function setCache($keyName, $value)
    {
        return $this->redis->set(
            $keyName,
            $value
        );
    }

    public function getCache($keyVal)
    {
        return $this->redis->get($keyVal);
    }

    public function delCache($delVal)
    {
        return $this->redis->del($delVal);
    }

    public function getAllkeys()
    {
        return $this->redis->keys('*');
    }
}