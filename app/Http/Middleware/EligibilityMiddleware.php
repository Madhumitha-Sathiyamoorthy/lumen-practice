<?php

namespace App\Http\Middleware;

use Closure;

class EligibilityMiddleware
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
        $elibility = false;
        if ($request->salary >= 30000) {
            $elibility = true;
        }
        $request->merge(array("eligibility" => $elibility));
        return $next($request);
    }
}
