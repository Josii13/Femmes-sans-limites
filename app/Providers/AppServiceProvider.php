<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
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
        Schema::defaultStringLength(191);
        Paginator::defaultView('vendor.pagination.fsl');
        Paginator::defaultSimpleView('vendor.pagination.fsl-simple');

        // Les redirections d'authentification se configurent dans bootstrap/app.php :
        // withMiddleware() réapplique sa valeur par défaut à la résolution du kernel
        // HTTP, donc APRÈS le boot des providers, et écraserait tout réglage posé ici.
    }
}
