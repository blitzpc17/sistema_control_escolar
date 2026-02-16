<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModuleAccessAudit
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) return $next($request);

        // Evita log/validación para rutas no-módulo típicas
        // (assets no pasan por web.php, pero igual lo dejo por seguridad)
        $path = trim($request->path(), '/'); // ej: users/5/edit
        if ($path === '' || str_starts_with($path, 'storage') || str_starts_with($path, 'css') || str_starts_with($path, 'js')) {
            return $next($request);
        }

        // Admin bypass total
        $roleName = DB::table('roles')->where('id', $user->role_id)->value('name');
        $isAdmin = ($roleName === 'admin');
        if ($isAdmin) {
            $this->logViewIfGet($request, $user->id, $this->resolveModuleIdByPath($path));
            return $next($request);
        }

        // 1) Resolver módulo por route (match por prefijo)
        $module = $this->resolveModuleByPath($path);

        // Si no es una ruta registrada como módulo, deja pasar
        // (por ejemplo: rutas internas tipo /profile si no existen en modules)
        if (!$module) return $next($request);

        // 2) Validar acceso: user_permissions override > role_modules
        $allowed = $this->canViewModule($user->id, $user->role_id, $module->id);

        if (!$allowed) {
            // Opcional: log de intento
            $this->logAction($request, $user->id, $module->id, 'DENY_MODULE');
            abort(403, 'No tienes acceso a este módulo.');
        }

        // 3) Auditar VIEW_MODULE (solo GET para no spamear)
        $this->logViewIfGet($request, $user->id, $module->id);

        return $next($request);
    }

    private function resolveModuleByPath(string $path): ?object
    {
        // Buscamos módulos activos con route no null
        // route en BD viene tipo "/users" "/roles" "/modules"
        // match: users/1/edit empieza con users -> route "/users"
        $first = explode('/', $path)[0] ?? '';
        $candidate = '/'.$first;

        return DB::table('modules')
            ->where('is_active', true)
            ->whereNotNull('route')
            ->where('route', $candidate)
            ->first();
    }

    private function resolveModuleIdByPath(string $path): ?int
    {
        $m = $this->resolveModuleByPath($path);
        return $m?->id;
    }

    private function canViewModule(int $userId, int $roleId, int $moduleId): bool
    {
        // override por usuario (si existe fila)
        $up = DB::table('user_permissions')
            ->where('user_id', $userId)
            ->where('module_id', $moduleId)
            ->first();

        if ($up) return (bool)$up->can_view;

        // por rol
        $rm = DB::table('role_modules')
            ->where('role_id', $roleId)
            ->where('module_id', $moduleId)
            ->first();

        if ($rm) return (bool)$rm->can_view;

        // si no hay relación, NO ve
        return false;
    }

    private function logViewIfGet(Request $request, int $userId, ?int $moduleId): void
    {
        if ($request->method() !== 'GET') return;
        if (!$moduleId) return;

        // Evita duplicar en la misma request si por alguna razón entra 2 veces
        if ($request->attributes->get('_view_logged')) return;
        $request->attributes->set('_view_logged', true);

        $this->logAction($request, $userId, $moduleId, 'VIEW_MODULE');
    }

    private function logAction(Request $request, int $userId, ?int $moduleId, string $action): void
    {
        DB::table('user_activity_logs')->insert([
            'user_id'      => $userId,
            'module_id'    => $moduleId,
            'action'       => $action,
            'entity_table' => null,
            'entity_id'    => null,
            'before_json'  => null,
            'after_json'   => null,
            'ip_address'   => $request->ip(),
            'user_agent'   => substr((string)$request->userAgent(), 0, 500),
            'created_at'   => now(),
        ]);
    }
}
