<?php

namespace Digisource\Core\Exceptions;

use Exception;

class ApiException extends Exception
{
    const ERROR_NOT_FOUND = 1;
    protected $privateMessage;
    protected $statusCode;

    public function __construct($code, $arrValue = array())
    {
        $arrErrors = $this->_getListErrors();
        $dataMsg = isset($arrErrors[$code]) ? $arrErrors[$code] : [
            'INTERNAL_SERVER_ERROR',
            'Error code is not found',
            500
        ];
        list($message, $privateMessage, $statusCode) = $dataMsg;

        if (count($arrValue) > 0) {
            $message = vsprintf($message, $arrValue);
            $privateMessage = vsprintf($privateMessage, $arrValue);
        }
        $this->setStatusCode($statusCode);
        $this->setPrivateMessage($privateMessage);
        parent::__construct($message, $code);
    }

    /**
     * @return array error with [message,private message, status code]
     */
    function _getListErrors()
    {
        return [
            self::ERROR_NOT_FOUND => ['ERROR_NOT_FOUND', 'Something is not found', 404]
        ];
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $code)
    {
        $this->statusCode = $code;
    }

    public function getPrivateMessage()
    {
        return $this->privateMessage;
    }

    public function setPrivateMessage(string $message)
    {
        $this->privateMessage = $message;
    }
}
