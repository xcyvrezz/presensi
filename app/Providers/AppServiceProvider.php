<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use App\Models\User;
use Carbon\Carbon;

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
        // Force root URL from config for subfolder deployment
        if ($root = config('app.url')) {
            URL::forceRootUrl($root);
        }

        // Set Carbon locale to Indonesian
        Carbon::setLocale('id');

        // Set default timezone to WIB
        date_default_timezone_set('Asia/Jakarta');

        // Define Gates for all permissions
        Gate::before(function (User $user, string $ability) {
            // Admin has all permissions
            if ($user->isAdmin()) {
                return true;
            }

            // Check if user has the specific permission
            return $user->hasPermission($ability) ? true : null;
        });
    }
}

