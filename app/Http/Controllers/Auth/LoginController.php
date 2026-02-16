<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Muestra la vista del login (blade).
     */
    public function show()
    {
        return view('auth.login');
    }

    /**
     * Login por AJAX.
     * Recibe: user, pass, remember (opcional)
     * Devuelve JSON: { ok: bool, message: string, redirect?: string, errors?: ... }
     */
    public function ajaxLogin(Request $request)
    {
        $data = $request->validate([
            'user' => ['required', 'string'],
            'pass' => ['required', 'string', 'min:6'],
            'remember' => ['nullable'],
        ]);

        $login = trim($data['user']);
        $password = (string) $data['pass'];
        $remember = (bool) ($data['remember'] ?? false);

        // Intentar por email o username
        $ok = Auth::attempt([
            'email' => $login,
            'password' => $password,
            'is_active' => true,
        ], $remember);

        if (!$ok) {
            $ok = Auth::attempt([
                'username' => $login,
                'password' => $password,
                'is_active' => true,
            ], $remember);
        }

        if (!$ok) {
            return response()->json([
                'ok' => false,
                'message' => 'Credenciales inválidas.',
                'errors' => [
                    'user' => ['Usuario/Correo o contraseña incorrectos.']
                ]
            ], 422);
        }

        // Usuario autenticado
        $user = Auth::user();

        // ✅ Validación extra: no baja (deleted_at) y activo
        // (ajusta aquí si tu baja lógica cambia)
        if (!$user || !$user->is_active || !is_null($user->deleted_at)) {
            Auth::logout();

            return response()->json([
                'ok' => false,
                'message' => 'Usuario inactivo o dado de baja.',
            ], 403);
        }

        // ✅ Protege sesión (importante)
        $request->session()->regenerate();

        return response()->json([
            'ok' => true,
            'message' => 'Login correcto.',
            'redirect' => url('/dashboard'),
        ]);
    }

    /**
     * Logout normal (POST).
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
