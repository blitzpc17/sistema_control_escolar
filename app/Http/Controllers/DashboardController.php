<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Requiere login (Laravel auth)
        $this->middleware('auth');
    }

    public function index()
    {
        // Aquí luego puedes cargar métricas: cobros del día, totales, etc.
        return view('dashboard');
    }
}
