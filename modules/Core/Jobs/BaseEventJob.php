<?php


namespace Digisource\Core\Jobs;


abstract class BaseEventJob extends BaseJob
{
    public function __construct()
    {
        parent::__construct();
        $this->connection = env('EVENT_QUEUE_CONNECTION_NAME', 'queue_event');
    }
}
