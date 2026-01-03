<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Allowed roles (comma-separated or multiple params)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Flatten roles if passed as comma-separated
        $allowedRoles = collect($roles)
            ->flatMap(fn($role) => explode(',', $role))
            ->map(fn($role) => trim($role))
            ->toArray();

        // Check if user's role is in allowed roles
        if (!in_array($user->role->value, $allowedRoles)) {
            abort(403, 'წვდომა აკრძალულია. თქვენ არ გაქვთ ამ გვერდზე შესვლის უფლება.');
        }

        return $next($request);
    }
}
