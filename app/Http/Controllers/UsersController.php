<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::with('role')
            ->orderBy('id','desc')
            ->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::where('is_active', true)->orderBy('name')->get();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'role_id'  => ['required','integer','exists:roles,id'],
            'name'     => ['required','string','max:120'],
            'email'    => ['nullable','email','max:180','unique:users,email'],
            'username' => ['nullable','string','max:80','unique:users,username'],
            'password' => ['required','string','min:8'],
            'is_active'=> ['nullable','boolean'],
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = (bool)($data['is_active'] ?? true);

        $user = User::create($data);

        ActivityLogger::log(auth()->id(), 'CREATE', 'users', 'users', $user->id, null, $user->toArray());

        return redirect()->route('users.index')->with('ok', 'Usuario creado.');
    }

    public function edit(User $user)
    {
        $roles = Role::where('is_active', true)->orderBy('name')->get();
        return view('users.edit', compact('user','roles'));
    }

    public function update(Request $request, User $user)
    {
        $before = $user->toArray();

        $data = $request->validate([
            'role_id'  => ['required','integer','exists:roles,id'],
            'name'     => ['required','string','max:120'],
            'email'    => ['nullable','email','max:180','unique:users,email,'.$user->id],
            'username' => ['nullable','string','max:80','unique:users,username,'.$user->id],
            'password' => ['nullable','string','min:8'],
            'is_active'=> ['nullable','boolean'],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['is_active'] = (bool)($data['is_active'] ?? false);

        $user->update($data);

        ActivityLogger::log(auth()->id(), 'UPDATE', 'users', 'users', $user->id, $before, $user->fresh()->toArray());

        return redirect()->route('users.index')->with('ok', 'Usuario actualizado.');
    }

    public function destroy(User $user)
    {
        $before = $user->toArray();

        // Baja lógica: usa SoftDeletes (deleted_at) y/o is_active=false
        $user->update(['is_active' => false]);
        $user->delete();

        ActivityLogger::log(auth()->id(), 'DELETE', 'users', 'users', $user->id, $before, ['is_active'=>false,'deleted_at'=>now()]);

        return back()->with('ok', 'Usuario dado de baja.');
    }
}
