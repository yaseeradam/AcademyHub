<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Student::class => \App\Policies\StudentPolicy::class,
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Score::class => \App\Policies\ScorePolicy::class,
        \App\Models\AttendanceSheet::class => \App\Policies\AttendancePolicy::class,
        \App\Models\AttendanceMark::class => \App\Policies\AttendancePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
