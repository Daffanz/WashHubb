<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions)
    {
        $user = $request->user();
        if (! $user || ! $user->role) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }
        return response()->json(['message' => 'Forbidden.'], 403);
    }
}
