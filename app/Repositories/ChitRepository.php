<?php

namespace App\Repositories;

use App\Models\ChitCustomer;
use App\Traits\CommonTrait;
use App\Traits\RedisTrait;
use App\Models\ChitPlans;

class ChitRepository
{
    use CommonTrait, RedisTrait;
    public function createCustomer($request)
    {
        $cusId = 'CUS_' . $this->generateUniqueKey();
        $customer = new ChitCustomer;
        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->mobile = $request->mobile;
        $customer->kycNumber = $request->kycNumber;
        $customer->salary = $request->salary;
        $customer->cutomerId = $cusId;
        $customer->eligibility = $request->eligibility ? 1 : 0;
        $customer->save();
        return $cusId;
    }


    public function createChit($request)
    {
        if ($request->has('chitPlanId')) {
            $data = ['planId' => $request->chitPlanId, 'customerId' => $request->customerId];
            $checkMembers = $this->checkMembers($data);
            if (isset ($checkMembers['planId'])) {
                $this->delCache("planid_" . $checkMembers['planId']);
                $remKeys = $this->getAllkeys();
                foreach ($remKeys as $key) {
                    $plansDetails[] = json_decode($this->getCache($key));
                }
                $response['Status'] = 'Success';
                $response['Message'] = $checkMembers['message'];
                $response['data'] = $plansDetails;
            } else {
                $response['Status'] = 'Success';
                $response['Message'] = $checkMembers['message'];
                $response['data'] = [];
            }
        } else {
            $plans = ChitPlans::select('chitName', 'description', 'chitValue', 'months', 'members')->get();
            foreach ($plans as $plan) {
                $createkey = "planid_" . $plan['_id'];
                $this->setCache($createkey, $plan);
            }
            $response['Message'] = 'All chit plans';
            $response['data'] = $plans;
        }
        return $response;
    }
}