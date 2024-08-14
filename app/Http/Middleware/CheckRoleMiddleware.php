<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Closure;
use App\Models\UserLogin;

class CheckRoleMiddleware
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
        $values = parse_url($request->url());
        $reqName = explode("/", $values['path']);
        if (in_array($reqName[2], $request->access)) {
            return $next($request);
        } else {
            return response()->json(['Status' => 'Unauthorized Access', 'Message' => 'You are not Allowed to Access This URL :('], 401);
        }
    }
}
