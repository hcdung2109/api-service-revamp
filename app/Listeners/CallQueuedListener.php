<?php


namespace App\Listeners;
use Illuminate\Container\Container;
use Illuminate\Events\CallQueuedListener as BaseCallQueuedListener;

class CallQueuedListener extends BaseCallQueuedListener
{
    public $user =null;
    public $ip='';

    public function __construct($class, $method, $data)
    {
        parent::__construct($class, $method, $data);
    }
    /**
     * Handle the queued job.
     *
     * @param  \Illuminate\Container\Container  $container
     * @return void
     */
    public function handle(Container $container)
    {
        $this->prepareData();

        $handler = $this->setJobInstanceIfNecessary(
            $this->job, $container->make($this->class)
        );

        $handler->{$this->method}(...array_values($this->data));
    }
    /**
     * Unserialize the data if needed.
     *
     * @return void
     */
    protected function prepareData()
    {
        parent::prepareData();
    }
}
