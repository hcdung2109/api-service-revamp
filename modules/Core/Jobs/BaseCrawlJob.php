<?php


namespace Digisource\Core\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class BaseJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    public function __construct()
    {
        $this->connection = env('QUEUE_CONNECTION_NAME', 'queue_connection');
    }

    /**
     * @return bool
     */
    public function shouldQueue($event=null, $entity = null): bool
    {
        return true;
    }

    /**
     * dispatch job into queue
     * @param int $delay
     */
    public function dispatch($delay = 0){
        if ($delay){
            $this->delay($delay);
        }
        if($this->shouldQueue()){
            dispatch($this)->onConnection($this->connection);
        }
    }
}
