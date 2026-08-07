<?php

namespace App\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RespondentMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session('user_role') !== 'respondent') {
            abort(403, 'Halaman ini hanya dapat diakses oleh responden.');
        }

        return $next($request);
    }
}
