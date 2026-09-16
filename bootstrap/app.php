<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckStudentIdentity;
use App\Http\Middleware\NoPrefetch;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            NoPrefetch::class,
        ]);
        $middleware->alias([
            'student.identity' => CheckStudentIdentity::class,
            'role' => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (TokenMismatchException $e, $request) {
            return redirect()->route('login')->with('info', 'Sesi halaman telah berakhir. Silakan masuk kembali.');
        });
    })->create();
