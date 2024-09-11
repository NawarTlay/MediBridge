<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckStorehouse
{
    public function handle($request, Closure $next)
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            $user = Auth::user();

        if ($user->role === 0) {
                return $next($request);
            }
        }

        return response()->json([
            'error' => true,
            'message' => "ليس لديك صلاحية "
        ]);
    }
}
