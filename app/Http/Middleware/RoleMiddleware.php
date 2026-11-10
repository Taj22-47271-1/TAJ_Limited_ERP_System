<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            abort(403, 'You do not have permission to access this section.');
        }

        // Administrators always have full operational permission
        if ($user->role === 'admin' || (method_exists($user, 'isAdmin') && $user->isAdmin())) {
            return $next($request);
        }

        $userRole = strtolower(trim((string) $user->role));
        $allowed = [];
        foreach ($roles as $r) {
            foreach (explode(',', (string) $r) as $sub) {
                $trimmed = strtolower(trim($sub));
                if ($trimmed !== '') {
                    $allowed[] = $trimmed;
                }
            }
        }

        if (! in_array($userRole, $allowed, true)) {
            abort(403, 'You do not have permission to access this section.');
        }

        return $next($request);
    }
}
