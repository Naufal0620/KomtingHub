<?php

namespace App\Providers;

use App\Models\AssignmentSubmission;
use App\Models\User;
use App\Policies\AssignmentPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        RateLimiter::for('mutations', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Gate::define('download', fn (User $user, AssignmentSubmission $submission): bool => app(AssignmentPolicy::class)->download($user, $submission));

        Gate::define('deleteSubmission', fn (User $user, AssignmentSubmission $submission): bool => app(AssignmentPolicy::class)->deleteSubmission($user, $submission));
    }
}
