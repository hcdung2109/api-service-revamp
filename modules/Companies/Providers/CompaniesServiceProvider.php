<?php

namespace Digisource\Companies\Providers;

use Digisource\Core\Providers\BaseServiceProvider;
use Illuminate\Support\Arr;

class CompaniesServiceProvider extends BaseServiceProvider
{
    protected $path_config = 'Config/config.php';

    protected $moduleName = 'Companies';

    protected string $moduleNameLower = 'companies';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->register(RouteServiceProvider::class);
//        $this->app->register(RepositoryServiceProvider::class);
        // $this->app->register(EventServiceProvider::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->registerRepositories();
        $this->registerCommands();
        $this->registerCron();
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes(
            [
                module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
            ],
            'config'
        );

        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );
    }

    protected function registerRepositories()
    {
        $bindingsVersion = config($this->moduleNameLower . '.repository.contractBindings', false);
        if ($bindingsVersion) {
            $default = $bindingsVersion['default'] ?? '';
            $version = $this->getVersion();
            $bindingsDefault = $bindingsVersion[$default];
            if (!Arr::has($bindingsVersion, $version)) {
                $version = $default;
            }
            $bindings = $bindingsVersion[$version];
            $bindings = array_merge($bindingsDefault, $bindings);
            // bind contact to real class
            foreach ($bindings as $contract => $class) {
                $this->app->bind($contract, $class);
            }
        }
    }

    /**
     * PRIVATE Method
     */
    private function registerCommands()
    {
        $this->commands(
            [
                //batch commands
//                PruneBatchesCommand::class
            ]
        );
    }

    private function registerCron()
    {
        if ($this->app->runningInConsole()) {
//            $schedule = $this->app->make(Schedule::class);
//            $schedule->call(function (){
//                if (env("RUN_UPDATE_HOTEL_OBO", false)){
//                    $stat_key = static::class.':update-hotel-content';
//                    $lock = Cache::lock($stat_key, 70);
//                    if ($lock->get()) {
//                        Artisan::call('packages:update-all-hotel --one 1');
//                        $lock->forceRelease();
//                    }
//                }
//            })->everyMinute();
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
