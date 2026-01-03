<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * Only allows admin users to proceed.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'წვდომა აკრძალულია. მხოლოდ ადმინისტრატორისთვის.',
                ], 403);
            }

            abort(403, 'წვდომა აკრძალულია. მხოლოდ ადმინისტრატორისთვის.');
        }

        return $next($request);
    }
}
