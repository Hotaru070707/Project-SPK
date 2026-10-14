<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectOwnerFromStorefront
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isOwner()) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
