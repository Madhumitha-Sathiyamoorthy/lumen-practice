<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Validator;
use Closure;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use App\Models\UserLogin;


class UserTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $validated = Validator::make($request->all(), [
            'userId' => 'required',
            'token' => 'required',
        ]);
        if ($validated->fails()) {
            return response()->json($validated->errors(), 400);
        }
        $userId = 'user_' . $request->userId;
        $checkUserDetails = UserLogin::with('userRoles')->where('id', $request->userId)->first();
        $accessUrls = [
            "1" => ['getUserDetails', 'getProducts'],
            "2" => ['getUserDetails'],
            "3" => ['addToCart', 'createPayment'],
        ];
        $getDbVal = json_decode(Redis::get($userId));
        if ($getDbVal) {
            $checkSession = Carbon::parse(Carbon::now()->toDateTimeString())->diffInMinutes(Carbon::parse($getDbVal->loginTime));
            if ($checkSession < env('LOGIN_SESSION_EXPIRATION')) {
                if ($getDbVal->token != $request->token) {
                    return response()->json(['Status' => 'failure', 'Message' => 'Token Mismatch!'], 401);
                } else {
                    $request->merge(array("access" => $accessUrls[$checkUserDetails['userRoles']['id']])); //Token is Active
                    return $next($request);
                }
            } else {
                return response()->json(['Status' => 'failure', 'Message' => 'Session Expired! Please Login Again to Continue.'], 400);
            }
        } else {
            return response()->json(['Status' => 'failure', 'Message' => 'Please Login to Continue!'], 400);
        }

    }
}
