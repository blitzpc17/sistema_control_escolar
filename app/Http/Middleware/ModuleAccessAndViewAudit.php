<?php

namespace App\Http\Middleware;

use App\Models\Module;
use App\Support\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ModuleAccessAndViewAudit
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) return $next($request);

        // Excluir rutas que no deben resolverse como módulo
        $path = '/' . ltrim($request->path(), '/'); // ej "/students/15/edit"
        if ($path === '/login' || $path === '/logout') {
            return $next($request);
        }

        // 1) Resolver módulo por route prefix más largo
        //    Match: modules.route = '/students' y path comienza con '/students'
        $module = Module::query()
            ->whereNotNull('route')
            ->where('is_active', true)
            ->whereRaw('? LIKE route || \'%\'', [$path])
            ->orderByRaw('length(route) desc')
            ->first();

        // Si no corresponde a un módulo (ej: assets, endpoints internos), continúa
        if (!$module) return $next($request);

        // Guardar en request para usar en controllers (audit de create/update/baja)
        $request->attributes->set('module_id', $module->id);
        $request->attributes->set('module_key', $module->key);

        // 2) Admin bypass por rol (si tu role.name = 'admin')
        $roleName = optional($user->role)->name;
        if ($roleName === 'admin') {
            $this->logViewOncePerDay($user->id, $module->key, $module->id);
            return $next($request);
        }

        // 3) Permiso efectivo: user_permissions si existe, si no role_modules
        $perm = DB::table('user_permissions')
            ->select('can_view')
            ->where('user_id', $user->id)
            ->where('module_id', $module->id)
            ->first();

        $canView = null;

        if ($perm !== null) {
            $canView = (bool)$perm->can_view;
        } else {
            $roleView = DB::table('role_modules')
                ->select('can_view')
                ->where('role_id', $user->role_id)
                ->where('module_id', $module->id)
                ->first();

            $canView = $roleView ? (bool)$roleView->can_view : false;
        }

        if (!$canView) {
            abort(403, 'No tienes permiso para visualizar este módulo.');
        }

        // 4) Auditar VIEW_MODULE (1 vez/día por user+módulo)
        $this->logViewOncePerDay($user->id, $module->key, $module->id);

        return $next($request);
    }

    private function logViewOncePerDay(int $userId, string $moduleKey, int $moduleId): void
    {
        $cacheKey = "viewmod:$userId:$moduleId:" . now()->format('Y-m-d');
        if (Cache::add($cacheKey, 1, now()->addHours(24))) {
            ActivityLogger::log(
                $userId,
                'VIEW_MODULE',
                $moduleKey,
                null,
                null,
                ['path' => request()->path()],
                null
            );
        }
    }
}
