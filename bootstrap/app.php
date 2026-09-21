<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'tenant' => \App\Http\Middleware\ResolveTenant::class,
            'role' => \App\Http\Middleware\EnsureRole::class,
            'preview.token' => \App\Http\Middleware\VerifyPreviewToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Render halaman error kustom untuk status HTTP umum
        $exceptions->render(function (Throwable $e, $request) {
            // Biarkan AuthenticationException & ValidationException ditangani Laravel
            // (redirect ke login / redirect back dengan error)
            if ($e instanceof \Illuminate\Auth\AuthenticationException
                || $e instanceof \Illuminate\Validation\ValidationException) {
                return null;
            }

            if ($request->is('api/*')) {
                return null; // biarkan handler default untuk API
            }

            $status = method_exists($e, 'getStatusCode')
                ? $e->getStatusCode()
                : (method_exists($e, 'getCode') && $e->getCode() >= 400 ? $e->getCode() : 500);

            $view = match ($status) {
                404 => 'errors.404',
                403 => 'errors.403',
                419 => 'errors.419',
                429 => 'errors.429',
                default => 'errors.500',
            };

            if (view()->exists($view)) {
                return response()->view($view, ['exception' => $e], $status);
            }

            return null;
        });
    })->create();
