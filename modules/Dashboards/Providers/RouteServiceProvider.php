<?php

namespace Digisource\Dashboards\Providers;

use Digisource\Core\Providers\BaseServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends BaseServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     */
    protected string $moduleNamespace = 'Digisource\Dashboards\Http\Controllers';
    protected $moduleNameLower = 'dashboards';

     /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     *
     * @return void
     */
    public function boot()
    {
        $this->map();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();
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
                require module_path('Dashboards', '/Routes/api.php');
            }
        );
    }
}
