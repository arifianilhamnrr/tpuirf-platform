<?php

use App\Presentation\Http\Middleware\AuthSessionMiddleware;
use App\Presentation\Http\Middleware\EnsureProfileCompleteMiddleware;
use App\Presentation\Http\Middleware\RespondentMiddleware;
use App\Presentation\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.session' => AuthSessionMiddleware::class,
            'profile.complete' => EnsureProfileCompleteMiddleware::class,
            'role' => RoleMiddleware::class,
            'respondent' => RespondentMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->expectsJson() || $request->is('api/*'),
        );
    })->create();
