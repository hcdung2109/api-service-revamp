<?php


namespace Digisource\Core\Providers;

use Digisource\Core\Listeners\RepositoryEventListener;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * The repository alias pattern.
     *
     * @var string
     */
    protected $repositoryAliasPattern = '{{class}}Contract';

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        // Register the event listener
        $this->app->bind('digisource.repository.listener', RepositoryEventListener::class);
    }


    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        // Subscribe the registered event listener
        $this->app['events']->subscribe('digisource.repository.listener');
    }
}
