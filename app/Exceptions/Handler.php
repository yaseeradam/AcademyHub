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
            \Illuminate\Support\Facades\Log::warning('Handler: CSRF token mismatch detected', [
                'host' => $request->getHost(),
                'url' => $request->fullUrl(),
                'has_session' => $request->hasSession(),
                'session_id' => $request->hasSession() ? $request->session()->getId() : 'NO_SESSION',
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session expired. Please refresh and try again.'], 419);
            }

            // Student session
            if (session()->has('student_id')) {
                session()->forget(['student_id', 'student_name', 'student_admission', 'student_class', 'login_type']);
            }

            // Use back() so we stay on the current tenant domain.
            return redirect()
                ->back()
                ->withInput($request->only('email', 'login_type', 'admission_number'))
                ->withErrors(['email' => 'Your session expired. Please try again.']);
        }

        return parent::render($request, $e);
    }
}
