<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DesktopAppAuth
{
    public function handle(Request $request, Closure $next)
    {
        $headerKey = $request->header('X-Desktop-App-Key');

        if (!$headerKey || $headerKey !== env('DESKTOP_APP_KEY')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }

}
