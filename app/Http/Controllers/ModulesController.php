<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ModulesController extends Controller
{
    public function __construct(private AuditService $audit){}

    public function index(){
        $modules = Module::with('parent')->orderBy('id','desc')->get();
        $parents = Module::whereNull('parent_id')->where('is_active',true)->orderBy('name')->get();
        return view('modules.index', compact('modules','parents'));
    }


    public function create()
    {
        $parents = Module::whereNull('parent_id')->where('is_active',true)->orderBy('sort_order')->get();
        return view('modules.form', ['module' => null, 'parents' => $parents]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key' => ['required','string','max:80','unique:modules,key'],
            'name' => ['required','string','max:120'],
            'route' => ['nullable','string','max:200'],
            'icon' => ['nullable','string','max:80'],
            'parent_id' => ['nullable','integer'],
            'sort_order' => ['nullable','integer'],
            'is_menu' => ['nullable'],
            'is_active' => ['nullable'],
        ]);

        $data['sort_order'] = (int)($data['sort_order'] ?? 0);
        $data['is_menu'] = (bool)($data['is_menu'] ?? true);
        $data['is_active'] = (bool)($data['is_active'] ?? true);

        $m = Module::create($data);

        $this->audit->log([
            'user_id' => $request->user()->id,
            'action' => 'CREATE',
            'entity_table' => 'modules',
            'entity_id' => $m->id,
            'after' => $m->toArray(),
        ]);

        return redirect()->route('modules.index')->with('ok','Módulo creado');
    }

    public function edit(Module $module)
    {
        $parents = Module::whereNull('parent_id')->where('is_active',true)->where('id','!=',$module->id)->orderBy('sort_order')->get();
        return view('modules.form', compact('module','parents'));
    }

    public function update(Request $request, Module $module)
    {
        $before = $module->toArray();

        $data = $request->validate([
            'key' => ['required','string','max:80','unique:modules,key,'.$module->id],
            'name' => ['required','string','max:120'],
            'route' => ['nullable','string','max:200'],
            'icon' => ['nullable','string','max:80'],
            'parent_id' => ['nullable','integer'],
            'sort_order' => ['nullable','integer'],
            'is_menu' => ['nullable'],
            'is_active' => ['nullable'],
        ]);

        $data['sort_order'] = (int)($data['sort_order'] ?? $module->sort_order);
        $data['is_menu'] = (bool)($data['is_menu'] ?? $module->is_menu);
        $data['is_active'] = (bool)($data['is_active'] ?? $module->is_active);

        $module->update($data);

        $this->audit->log([
            'user_id' => $request->user()->id,
            'action' => 'UPDATE',
            'entity_table' => 'modules',
            'entity_id' => $module->id,
            'before' => $before,
            'after' => $module->fresh()->toArray(),
        ]);

        return redirect()->route('modules.index')->with('ok','Módulo actualizado');
    }

    public function destroy(Request $request, Module $module)
    {
        $before = $module->toArray();

        $module->update(['is_active' => false]);

        $this->audit->log([
            'user_id' => $request->user()->id,
            'action' => 'DELETE',
            'entity_table' => 'modules',
            'entity_id' => $module->id,
            'before' => $before,
            'after' => $module->fresh()->toArray(),
        ]);

        return redirect()->route('modules.index')->with('ok','Módulo dado de baja');
    }
}
