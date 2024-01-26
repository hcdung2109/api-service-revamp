<?php

class ServiceCandidate
{
    public $appSession;
    public $msg;

    public function __construct()
    {
        global $appSession;
        $this->appSession = $appSession;
        $this->msg = $this->appSession->getTier()->createMessage();
    }

}
