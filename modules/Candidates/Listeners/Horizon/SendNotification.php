<?php


namespace Digisource\Candidates\Listeners\Horizon;


use Laravel\Horizon\Lock;

class SendNotification
{

    /**
     * Handle the event.
     *
     * @param mixed $event
     * @return void
     */
    public function handle($event)
    {
        $notification = $event->toNotification();
        if (!app(Lock::class)->get('notification:' . $notification->signature(), 30)) {
            return;
        }
    }
}
