<?php

namespace Digisource\Calendars\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Digisource\Calendars\Contracts\CalendarsServiceFactory;
use Digisource\Calendars\Services\V1\CalendarsService;
use Illuminate\Http\Request;

class CalendarsController extends Controller
{

    protected CalendarsService $calendarsService;

    public function __construct(CalendarsServiceFactory $calendarsServiceFactory
    ) {
        $this->calendarsService = $calendarsServiceFactory;
    }

    public function getCalendar(Request $request)
    {
        $data = $this->calendarsService->getCalendar();
        $this->data = $data;
        return $this->getResponse();
    }
}
