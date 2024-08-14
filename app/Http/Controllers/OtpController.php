<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Otp;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;

class OtpController extends Controller
{
    /**use Carbon\Carbon;
     * Create a new controller instance.
     *
     * @return void
     */
    public function generateOtp(Request $request)
    {
        try {

            $validated = Validator::make($request->all(), [
                'phone_number' => 'required|numeric|digits:10'
            ]);
            if ($validated->fails()) {
                return response()->json($validated->errors(), 400);
            }
            $generateOtp = random_int(100000, 999999);
            $requestLimit = 5;
            $expire = Carbon::now()->subMinutes(2);
            Log::info("expire-->" . ($expire));
            $otpRequestCount = Otp::where('number', $request->phone_number)->where('created_at', '>', $expire)->count();
            $otpRequestPrevCount = Otp::where('number', $request->phone_number)->where('created_at', '<', $expire)->count();
            Log::info("otpRequestCount-->" . json_encode($otpRequestCount));
            if ($otpRequestCount < $requestLimit) {
                if ($otpRequestPrevCount > 0) {
                    $otpRequestCount = Otp::where('number', $request->phone_number)->where('created_at', '<=', $expire)->delete();
                }
                $otp = new Otp();
                $otp->number = $request->phone_number;
                $otp->otp = $generateOtp;
                $otp->save();
                $response['phoneNumber'] = $request->phone_number;
                $response['otp'] = $generateOtp;
                $response['Status'] = 'success';
            } else {
                $response['Message'] = "OTP send timeout! Try again after 10 Minutes.";
                $response['Status'] = 'failure';
            }
            Log::info("otp-->" . json_encode($generateOtp));
        } catch (\Throwable $e) {
            \Log::error("OTP Generation Failed --->" . $e->getMessage());
        }
        return $response;
    }

    public function verifyOtp(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'phone_number' => 'required|numeric|digits:10',
                'otp' => 'required|numeric|digits:6'
            ]);
            if ($validated->fails()) {
                return response()->json($validated->errors(), 400);
            }
            $getValue = Otp::where('number', $request->phone_number)->orderBy('id', 'DESC')
                ->first();
            if ($getValue['otp'] === $request->otp) {
                $response['Status'] = 'success';
                $response['Message'] = 'OTP verified successfully!';
            } else {
                $response['Status'] = 'failure';
                $response['Message'] = 'Invalid OTP. Please try again!';
            }
            return $response;
        } catch (\Throwable $e) {
            \Log::error("OTP Verification Failed --->" . $e->getMessage());
        }
    }

    public function fromArrayValidation(Request $request)
    {
        try {
            $validated = Validator::make(
                $request->all(),
                [
                    'data' => 'required',
                    'data.name' => 'required',
                    'data.name.firstname' => 'required',
                    'data.name.middlename' => 'required',
                    'data.name.lastname' => 'required',
                    'data.age' => 'required',
                    'data.fieldOfInterest' => 'required',
                ],
                [
                    'data.name.required' => 'name field is required',
                    'data.name.firstname.required' => 'firstname field is required',
                    'data.name.lastname.required' => 'lastname field is required',
                    'data.name.middlename.required' => 'middlename field is required',
                    'data.age.required' => 'age field is required',
                    'data.fieldOfInterest.required' => 'field of interest field is required'
                ]
            );
            $errors = [];
            foreach ($validated->errors()->messages() as $key => $value) {
                if ($key == 'data.name') {
                    $key = 'name';
                } else if ($key == 'data.name.firstname') {
                    $key = 'firstname';
                } else if ($key == 'data.name.lastname') {
                    $key = 'lastname';
                } else if ($key == 'data.name.middlename') {
                    $key = 'middlename';
                } else if ($key == 'data.age') {
                    $key = 'age';
                } else if ($key == 'data.fieldOfInterest') {
                    $key = 'fieldOfInterest';
                }
                $errors[$key] = $value;
            }
            if ($validated->fails()) {
                return response()->json(["status" => 400, 'data' => $errors]);
            } else {
                return response()->json(['status' => 200, 'message' => 'You have successfully passed the validation :)']);
            }
        } catch (\Throwable $e) {
            \Log::error("fromArrayValidation Failed --->" . $e->getMessage());
        }
    }

}
