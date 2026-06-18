<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        ...$roles
    ): Response
    {
        // belum login
        if (!auth()->check()) {
            return redirect('/login');
        }

        // cek role
        if (!in_array(auth()->user()->role, $roles)) {

            // redirect sesuai role
            if(auth()->user()->role == 'user'){
                return redirect('/my-ticket');
            }

            if(auth()->user()->role == 'admin'){
                return redirect('/admin/dashboard');
            }

            if(
                auth()->user()->role == 'admin' ||
                auth()->user()->role == 'technician'
            ){
                return redirect('/admin/tickets');
            }

            abort(403);
        }

        return $next($request);
    }
}