<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        // Redirect to login instead of showing 419 Page Expired
        if ($e instanceof TokenMismatchException) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session expired. Please refresh and try again.'], 419);
            }

            // Student session
            if (session()->has('student_id')) {
                session()->forget(['student_id', 'student_name', 'student_admission', 'student_class', 'login_type']);
            }

            return redirect()
                ->route('login')
                ->with('status', 'Your session expired. Please log in again.');
        }

        return parent::render($request, $e);
    }
}
