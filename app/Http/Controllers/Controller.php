<?php

namespace App\Http\Controllers;

use Digisource\Core\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\ValidationException;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, ApiResponse;

    protected function validateFails(ValidationException $ex)
    {
        $this->setException($ex);
        $this->setStatusCode($ex->status);
        $this->setMessage($ex->getMessage());
        $this->addErrors($ex->errors());
    }
}
