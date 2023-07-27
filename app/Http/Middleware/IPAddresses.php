<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IPAddresses
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->ip() != env('IP_ADDRESS')) {
            return response()->json([
                'message' => "You havn't permission with your IP"
            ]);
        }
        return $next($request);
    }
}
