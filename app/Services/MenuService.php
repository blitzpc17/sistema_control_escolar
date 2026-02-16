<?php

namespace App\Services;

use App\Models\Module;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MenuService
{
    public function buildForUser($user): array
    {
        if (!$user) return [];

        // Admin: ve todo lo activo y de menú (puedes cambiar regla si quieres)
        $isAdmin = optional($user->role)->name === 'admin';

        if ($isAdmin) {
            $modules = Module::query()
                ->where('is_active', true)
                ->where('is_menu', true)
                ->orderBy('sort_order')
                ->get();
        } else {
            // Por rol: role_modules.can_view = true
            $modules = Module::query()
                ->join('role_modules as rm', 'rm.module_id', '=', 'modules.id')
                ->where('rm.role_id', $user->role_id)
                ->where('rm.can_view', true)
                ->where('modules.is_active', true)
                ->where('modules.is_menu', true)
                ->select('modules.*')
                ->orderBy('modules.sort_order')
                ->get();
        }

        return $this->toTree($modules);
    }

    private function toTree(Collection $modules): array
    {
        $byId = [];
        foreach ($modules as $m) {
            $byId[$m->id] = [
                'id' => $m->id,
                'key' => $m->key,
                'name' => $m->name,
                'route' => $m->route,      // puede ser null en padres
                'icon' => $m->icon ?: 'fa-circle',
                'parent_id' => $m->parent_id,
                'children' => [],
            ];
        }

        $tree = [];
        foreach ($byId as $id => $node) {
            if ($node['parent_id'] && isset($byId[$node['parent_id']])) {
                $byId[$node['parent_id']]['children'][] = &$byId[$id];
            } else {
                $tree[] = &$byId[$id];
            }
        }

        // opcional: ordena hijos por sort_order ya viene ordenado, pero por si acaso
        return $tree;
    }
}
