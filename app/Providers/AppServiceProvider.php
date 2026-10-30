<?php

namespace App\Providers;

use App\Demo\Trace\RequestTrace;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Satu instance per request agar middleware, controller, dan
        // exception handler menulis ke trace yang sama.
        $this->app->singleton(RequestTrace::class);
    }

    public function boot(): void
    {
        //
    }
}
