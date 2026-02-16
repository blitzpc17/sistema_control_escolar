<?php

namespace App\Http\Middleware;

use App\Models\Module;
use Closure;
use Illuminate\Support\Facades\DB;

class CheckModuleView
{
    public function handle($request, Closure $next, string $moduleKey)
    {
        $user = $request->user();
        if (!$user) abort(401);

        // admin shortcut por nombre de rol
        if ($user->role && $user->role->name === 'admin') {
            return $next($request);
        }

        $moduleId = Module::where('key', $moduleKey)->value('id');
        if (!$moduleId) abort(403);

        $can = DB::table('role_modules')
            ->where('role_id', $user->role_id)
            ->where('module_id', $moduleId)
            ->where('can_view', true)
            ->exists();

        if (!$can) abort(403);

        return $next($request);
    }
}
