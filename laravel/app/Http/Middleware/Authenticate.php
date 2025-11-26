<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        if ($request->expectsJson()) {
            // Return null or throw JSON response
            abort(response()->json(['error' => 'Unauthorized'], 401));
        }
        
        // Fallback for non-API requests
        return route('login');
    }
}
