<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
|--------------------------------------------------------------------------
| CATATAN PENTING
|--------------------------------------------------------------------------
| File ini BUKAN untuk ditimpa mentah-mentah. Laravel 11/12 sudah punya
| bootstrap/app.php bawaan (dari composer create-project). Cukup salin
| BAGIAN withMiddleware(...) di bawah ini ke dalam file bootstrap/app.php
| project kamu yang sudah ada, supaya alias 'role' terdaftar.
*/

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
