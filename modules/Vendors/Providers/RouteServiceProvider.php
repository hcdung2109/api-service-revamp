<?php

namespace Digisource\Vendors\Providers;

use Digisource\Core\Providers\BaseServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends BaseServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     */
    protected $moduleNamespace = 'Digisource\Vendors\Http\Controllers';
    protected $moduleNameLower = 'vendors';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        $this->map();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapApiRoutes();

    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::group(
            $this->getGroupPrefix(),
            function ($router) {
                require module_path('Vendors', '/Routes/api.php');
            }
        );
    }
}
