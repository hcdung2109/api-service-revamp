<?php

namespace Digisource\Jobs\Providers;

use Digisource\Core\Providers\BaseServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends BaseServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     */
    protected string $moduleNamespace = 'Digisource\Jobs\Http\Controllers';
    protected $moduleNameLower = 'jobs';

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

//        $this->mapWebRoutes();
    }

 
    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group(
            $this->getGroupPrefix(),
            function ($router) {
                require module_path('Jobs', '/Routes/api.php');
            }
        );
    }
}