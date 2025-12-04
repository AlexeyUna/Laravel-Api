<?php

use App\Exceptions\HoldStatusException;
use App\Exceptions\NoSlotsAvailableException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $e) {
                return response()->json([
                    'error' => 'not_found',
                    'message' => 'Resource not found'
                ], Response::HTTP_NOT_FOUND);
        });

        $exceptions->render(function (ValidationException $e) {
                return response()->json([
                    'error' => 'validation_error',
                    'message' => 'The given data is invalid.',
                    'details' => $e->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $exceptions->render(function (HoldStatusException $e) {
            return response()->json([
                'error' => 'hold_invalid_status',
                'message' => $e->getMessage(),
            ], Response::HTTP_CONFLICT);
        });

        $exceptions->render(function (NoSlotsAvailableException $e) {
            return response()->json([
                'error' => 'no_slots_available',
                'message' => $e->getMessage(),
            ], Response::HTTP_CONFLICT);
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            dd($e);
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => 'server_error',
                    'message' => 'An internal server error occurred.'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });

    })->create();
