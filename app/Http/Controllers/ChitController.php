<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChitCustomer;
use App\Models\ChitPlans;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Traits\CommonTrait;
use Illuminate\Support\Facades\Redis;
use App\Repositories\ChitRepository;

// namespace App\Repositories;
class ChitController extends Controller
{
    /**use Carbon\Carbon;
     * Create a new controller instance.
     *
     * @return void
     */
    use CommonTrait;
    public function __construct(ChitRepository $chitRepo)
    {
        $this->chitRepo = $chitRepo;
    }

    public function createCustomer(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email',
                'mobile' => 'required|numeric|digits:10',
                'kycNumber' => 'required|numeric|digits:12',
                'salary' => 'required'

            ]);
            if ($validated->fails()) {
                return response()->json($validated->errors(), 400);
            }
            $getResponse = $this->chitRepo->createCustomer($request);
            $response['status'] = 'success';
            $response['message'] = 'Customer Created Sucessfully!';
            $response['data'] = $getResponse;

        } catch (\Throwable $e) {
            \Log::error("createCustomer request Failed --->" . $e->getMessage());
            $response['code'] = '500';
            $response['status'] = 'failure';
            $response['data'] = $e->getMessage();
        }
        return $response;
    }

    public function createChit(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'customerId' => 'required',
            ]);
            if ($validated->fails()) {
                return response()->json($validated->errors(), 400);
            }
            $getResponse = $this->chitRepo->createChit($request);
            $response['status'] = 'success';
            $response['message'] = $getResponse['Message'];
            $response['data'] = $getResponse['data'];
        } catch (\Throwable $e) {
            \Log::error("createChit request Failed --->" . $e->getMessage());
            $response['code'] = '500';
            $response['status'] = 'failure';
            $response['data'] = $e->getMessage();
        }
        return $response;
    }

}
