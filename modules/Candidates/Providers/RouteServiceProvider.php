<?php

namespace Digisource\Candidates\Providers;

use Digisource\Core\Providers\BaseServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends BaseServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected $moduleNamespace = 'Digisource\Candidates\Http\Controllers';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'candidates';

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
                require module_path('Candidates', '/Routes/api.php');
            }
        );
    }
}
