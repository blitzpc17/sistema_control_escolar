<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\AuditService;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    public function __construct(private AuditService $audit){}

    public function index()
    {
        $roles = Role::orderBy('id','desc')->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.form', ['role' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:80','unique:roles,name'],
            'description' => ['nullable','string','max:200'],
            'is_active' => ['nullable'],
        ]);

        $data['is_active'] = (bool)($data['is_active'] ?? true);

        $r = Role::create($data);

        $this->audit->log([
            'user_id' => $request->user()->id,
            'action' => 'CREATE',
            'entity_table' => 'roles',
            'entity_id' => $r->id,
            'after' => $r->toArray(),
        ]);

        return redirect()->route('roles.index')->with('ok','Rol creado');
    }

    public function edit(Role $role)
    {
        return view('roles.form', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $before = $role->toArray();

        $data = $request->validate([
            'name' => ['required','string','max:80','unique:roles,name,'.$role->id],
            'description' => ['nullable','string','max:200'],
            'is_active' => ['nullable'],
        ]);

        $data['is_active'] = (bool)($data['is_active'] ?? $role->is_active);

        $role->update($data);

        $this->audit->log([
            'user_id' => $request->user()->id,
            'action' => 'UPDATE',
            'entity_table' => 'roles',
            'entity_id' => $role->id,
            'before' => $before,
            'after' => $role->fresh()->toArray(),
        ]);

        return redirect()->route('roles.index')->with('ok','Rol actualizado');
    }

    public function destroy(Request $request, Role $role)
    {
        $before = $role->toArray();

        $role->update(['is_active' => false]);

        $this->audit->log([
            'user_id' => $request->user()->id,
            'action' => 'DELETE',
            'entity_table' => 'roles',
            'entity_id' => $role->id,
            'before' => $before,
            'after' => $role->fresh()->toArray(),
        ]);

        return redirect()->route('roles.index')->with('ok','Rol dado de baja');
    }
}
