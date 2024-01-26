<?php


namespace Digisource\Core\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class BaseServiceProvider extends ServiceProvider
{

    protected $path_config;
    protected $path_view;
    protected $path_lang;
    protected $moduleName;

    /**
     *
     */
    protected function getVersion()
    {
        if(app()->runningInConsole()){
            $version = env('API_VER');
        }else{
            $version = (Request::segment(1) ?? env('API_VER')) . "";
//            $prefix = env('ROUTE_BASE_PATH', '');
//            if (!empty($prefix)) {
//                $slashes = explode('/', $prefix);
//                $version = Request::segment(count($slashes));
//            }
        }
        $version = $version . '';
        return Str::upper($version);
    }

    protected function getNamespace()
    {
        $ns = $this->moduleNamespace;
        $version = $this->getVersion();
        $version = Str::replace('.', '_', $version);
        $ns_checker = $ns . '\\' . $version.'\\Controller';

        if(!class_exists($ns_checker)){
            $bindingsVersion = config($this->moduleNameLower . '.repository.contractBindings', false);
            $version = $bindingsVersion['default'] ?? '';
        }
        Log::info($ns . '\\' . $version);
        return $ns . '\\' . $version;
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
     * get group prefix for router
     * @return string[]
     */

    protected function getGroupPrefix($middleware = '')
    {
        $group = [
            // 'middleware'=>'api',
            'namespace' => $this->getNamespace()
        ];
//        $prefix = env('ROUTE_BASE_PATH', '');
//        if (!empty($prefix)) {
//            $group['prefix'] = $prefix;
//        }
        return $group;
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, $this->path_config) => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, $this->path_config),
            $this->moduleNameLower
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, $this->path_view);

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, $this->path_lang), $this->moduleNameLower);
        }
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        $views = config('view.paths') ?? [];
        foreach ($views as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
