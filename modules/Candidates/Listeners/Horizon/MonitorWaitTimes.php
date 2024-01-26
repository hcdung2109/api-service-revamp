<?php


namespace Digisource\Candidates\Listeners\Horizon;

use Laravel\Horizon\Events\LongWaitDetected;
use Laravel\Horizon\Events\SupervisorLooped;
use Laravel\Horizon\Listeners\MonitorWaitTimes as BaseMonitorWaitTimes;
use Laravel\Horizon\WaitTimeCalculator;

class MonitorWaitTimes extends BaseMonitorWaitTimes
{
    /**
     * Handle the event.
     *
     * @param  \Laravel\Horizon\Events\SupervisorLooped  $event
     * @return void
     */
    public function handle()
    {
        if (! $this->dueToMonitor()) {
            return;
        }

        // Here we will calculate the wait time in seconds for each of the queues that
        // the application is working. Then, we will filter the results to find the
        // queues with the longest wait times and raise events for each of these.
        $results = app(WaitTimeCalculator::class)->calculate();

        $long = collect($results)->filter(function ($wait, $queue) {
            return $wait > (config("horizon.waits.{$queue}") ?? 60);
        });

        // Once we have determined which queues have long wait times we will raise the
        // events for each of the queues. We'll need to separate the connection and
        // queue names into their own strings before we will fire off the events.
        $long->each(function ($wait, $queue) {
            [$connection, $queue] = explode(':', $queue, 2);

            event(new LongWaitDetected($connection, $queue, $wait));
        });
    }
}
