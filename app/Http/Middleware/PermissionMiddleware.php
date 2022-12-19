<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        if (Auth::check()) {
            $user = User::findOrFail(Auth::id());
            if (!$user->hasPermission($permission)) {
                return response()->json([
                    'error' => "User with this 'Permission' is not Authorized"
                ], 401);
            }
        }
        return $next($request);
    }
}
