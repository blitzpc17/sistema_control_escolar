<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Module;
use App\Models\RoleModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionsController extends Controller
{
    public function index(Request $request)
    {
        // Si no mandan role_id, toma el primer rol activo
        $roleId = (int)($request->get('role_id') ?: (Role::where('is_active', true)->orderBy('name')->value('id') ?? 0));

        $roles = Role::where('is_active', true)->orderBy('name')->get();

        // Permisos existentes del rol
        $permMap = RoleModule::where('role_id', $roleId)->get()->keyBy('module_id');

        // Arma árbol de módulos (padre->hijos) solo para UI
        $modules = Module::where('is_active', true)
            ->orderByRaw('COALESCE(parent_id, 0), sort_order NULLS LAST, name')
            ->get();

        $parents = $modules->whereNull('parent_id');
        $childrenByParent = $modules->whereNotNull('parent_id')->groupBy('parent_id');

        $tree = [];
        foreach ($parents as $p) {
            $tree[] = [
                'module' => $p,
                'perm' => $permMap->get($p->id),
                'children' => collect($childrenByParent->get($p->id, []))->map(function ($c) use ($permMap) {
                    return [
                        'module' => $c,
                        'perm' => $permMap->get($c->id),
                    ];
                })->values()->all(),
            ];
        }

        return view('permissions.index', compact('roles', 'roleId', 'tree'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'role_id' => ['required','integer'],
            'permissions' => ['nullable','array'],
        ]);

        $roleId = (int)$request->role_id;
        $perms = $request->input('permissions', []); // permissions[module_id][can_view]=on

        // Normaliza: can_view true/false por module_id
        $want = [];
        foreach ($perms as $moduleId => $payload) {
            $want[(int)$moduleId] = isset($payload['can_view']) ? 1 : 0;
        }

        // Para los módulos no enviados (unchecked) => 0
        $allModuleIds = Module::where('is_active', true)->pluck('id')->map(fn($v)=>(int)$v)->all();
        foreach ($allModuleIds as $mid) {
            if (!array_key_exists($mid, $want)) $want[$mid] = 0;
        }

        DB::transaction(function () use ($roleId, $want) {
            // Opción A (recomendada): guardar solo los que tienen can_view=1
            RoleModule::where('role_id', $roleId)->delete();

            $now = now();
            $rows = [];
            foreach ($want as $moduleId => $canView) {
                if ($canView === 1) {
                    $rows[] = [
                        'role_id' => $roleId,
                        'module_id' => $moduleId,
                        'can_view' => 1,
                        'created_at' => $now,
                    ];
                }
            }
            if (!empty($rows)) {
                RoleModule::insert($rows);
            }
        });

        return redirect()
            ->route('permissions.index', ['role_id' => $roleId])
            ->with('ok', 'Permisos guardados correctamente.');
    }
}
