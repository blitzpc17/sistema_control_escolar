<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function __construct(private AuditService $audit){}    

    public function index(){
        $users = User::with('role')->orderBy('id','desc')->get();
        $roles = Role::where('is_active',true)->orderBy('name')->get();
        return view('users.index', compact('users','roles'));
    }

    public function create()
    {
        $roles = Role::where('is_active', true)->orderBy('name')->get();
        return view('users.form', ['user' => null, 'roles' => $roles]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'role_id' => ['required','integer'],
            'name' => ['required','string','max:120'],
            'email' => ['nullable','email','max:180','unique:users,email'],
            'username' => ['nullable','string','max:80','unique:users,username'],
            'password' => ['required','string','min:6'],
            'is_active' => ['nullable'],
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = (bool)($data['is_active'] ?? true);

        $u = User::create($data);

        $this->audit->log([
            'user_id' => $request->user()->id,
            'action' => 'CREATE',
            'entity_table' => 'users',
            'entity_id' => $u->id,
            'after' => $u->toArray(),
        ]);

        return redirect()->route('users.index')->with('ok','Usuario creado');
    }

    public function edit(User $user)
    {
        $roles = Role::where('is_active', true)->orderBy('name')->get();
        return view('users.form', compact('user','roles'));
    }

    public function update(Request $request, User $user)
    {
        $before = $user->toArray();

        $data = $request->validate([
            'role_id' => ['required','integer'],
            'name' => ['required','string','max:120'],
            'email' => ['nullable','email','max:180','unique:users,email,'.$user->id],
            'username' => ['nullable','string','max:80','unique:users,username,'.$user->id],
            'password' => ['nullable','string','min:6'],
            'is_active' => ['nullable'],
        ]);

        if (!empty($data['password'])) $data['password'] = Hash::make($data['password']);
        else unset($data['password']);

        $data['is_active'] = (bool)($data['is_active'] ?? $user->is_active);

        $user->update($data);

        $this->audit->log([
            'user_id' => $request->user()->id,
            'action' => 'UPDATE',
            'entity_table' => 'users',
            'entity_id' => $user->id,
            'before' => $before,
            'after' => $user->fresh()->toArray(),
        ]);

        return redirect()->route('users.index')->with('ok','Usuario actualizado');
    }

    public function destroy(Request $request, User $user)
    {
        $before = $user->toArray();

        $user->update([
            'is_active' => false,
            'deleted_at' => now(),
        ]);

        $this->audit->log([
            'user_id' => $request->user()->id,
            'action' => 'DELETE', // baja lógica (pero acción DELETE para auditar)
            'entity_table' => 'users',
            'entity_id' => $user->id,
            'before' => $before,
            'after' => $user->fresh()->toArray(),
        ]);

        return redirect()->route('users.index')->with('ok','Usuario dado de baja');
    }
}
