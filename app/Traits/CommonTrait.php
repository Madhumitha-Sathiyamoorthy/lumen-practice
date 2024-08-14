<?php

namespace App\Traits;

use Illuminate\Support\Str;
use App\Models\ChitCustomer;
use App\Models\ChitPlans;
use Illuminate\Support\Facades\Redis;


trait CommonTrait
{
    public function generateUniqueKey()
    {
        $token_key = Str::random(20);
        return $token_key;
    }
    public function checkMembers($request)
    {
        $storeChit = ChitCustomer::where('cutomerId', $request['customerId'])->first();
        $planMemCnt = ChitCustomer::select('chits')->get();
        $chitData = ChitPlans::select('members', 'chitName')->where('_id', $request['planId'])->first();
        $planMembers = 0;
        foreach ($planMemCnt as $val) {
            if ($val['chits']) {
                $diffcheck = count($val['chits']) - (count(array_diff($val['chits'], (array) $request['planId'])));
                $planMembers += $diffcheck;
            }
        }

        $response['planId'] = $request['planId'];
        $checkPlanExist = $storeChit->chits ? array_count_values($storeChit->chits) : 0;
        $checUserChitCnt = (isset ($checkPlanExist[$request['planId']])) ? $checkPlanExist[$request['planId']] : 0;
        if ($checUserChitCnt < 2) {
            if ($planMembers >= $chitData['members']) {
                $response['message'] = 'This Chit Plan has enough members. Please select a another plan !';
            } else {
                $storeChit->push('chits', $request['planId']);
                $storeChit->save();
                unset($response['planId']);
                // Redis::flushAll();
                $response['message'] = "You have successfully registered for " . $chitData['chitName'] . " plan successfully!";
            }
        } else {
            $response['message'] = 'Sorry:( You already registerd for this plan 2 times.You can select another plans!';
        }
        return $response;
    }
}