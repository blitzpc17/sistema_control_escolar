<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\MenuService;
use Illuminate\Support\Facades\View;

class SharedMenu
{
    public function __construct(private MenuService $menuService){}

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        View::share('authUser', $user);
        View::share('drawerMenu', $this->menuService->buildForUser($user));

        return $next($request);
    }
}
