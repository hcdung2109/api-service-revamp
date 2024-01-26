<?php


namespace App\Events;

use App\Listeners\CallQueuedListener;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionClass;
use Illuminate\Events\Dispatcher as EventDispatcher;

class Dispatcher extends EventDispatcher
{
    /**
     * Determine if the event handler wants to be queued.
     *
     * @param string $class
     * @param array $arguments
     * @return bool
     */
    protected function handlerWantsToBeQueued($class, $arguments)
    {
        $instance = $this->container->make($class);

        if (method_exists($instance, 'shouldQueue')) {
            $function = [$instance, 'shouldQueue'];

            $result = call_user_func_array($function, $arguments);
            return $result;
        }

        return true;
    }

    /**
     * Create the listener and job for a queued listener.
     *
     * @param string $class
     * @param string $method
     * @param array $arguments
     * @return array
     */
    protected function createListenerAndJob($class, $method, $arguments)
    {
        $listener = (new ReflectionClass($class))->newInstanceWithoutConstructor();
        $delay = $arguments[2] ?? '';
        if (is_string($delay) && Str::startsWith($delay, 'delay:')) {
            $delay = Str::replace('delay:', '', $delay);
            Arr::forget($arguments, 2);
            $listener->delay = (int)$delay;
        }
        $arguments = collect($arguments)->values()->all();
        return [$listener, $this->propagateListenerOptions(
            $listener, new CallQueuedListener($class, $method, $arguments)
        )];
    }
}
