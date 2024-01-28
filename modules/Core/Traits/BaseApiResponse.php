<?php

namespace Digisource\Core\Traits;

use Exception;
use Digisource\Core\Exceptions\ApiException;

trait BaseApiResponse
{
    protected $data = [];

    protected $headers = [];

    protected $statusCode = 200;

    protected $errors;

    protected $message;

    protected $exception;

    public function addData(array $data)
    {
        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    public function setHeaders(array $headers)
    {
        foreach ($headers as $key => $value) {
            $this->headers[$key] = $value;
        }

        return $this;
    }

    public function setStatusCode(int $code)
    {
        $this->statusCode = $code;

        return $this;
    }

    public function addErrors(array $errors)
    {
        foreach ($errors as $key => $value) {
            $this->errors[$key] = $value;
        }

        return $this;
    }

    public function setMessage(string $message)
    {
        $this->message = $message;

        return $this;
    }

    public function setException($e)
    {
        $this->exception = $e;

        return $this;
    }

    public function getResponse()
    {
        $data = [];

        $data['success'] = true;
        $data['status'] = $this->statusCode;
        $data['data'] = $this->data;
        $data['message'] = $this->message;
        $data['error'] = 0;

        if ($this->errors || $this->exception || $this->data instanceof Exception) {
            if ($this->data) {
                $this->exception = $this->data;
            }
            $errorCode = $this->exception->getCode();
            $data['success'] = false;
            $data['status'] = $this->statusCode;
            $data['data'] = [];
            $data['message'] = $this->message ?? $this->exception->getMessage();
            $data['error'] = $errorCode ?: $this->statusCode;

            if ($this->errors) {
                $data['errors'] = $this->errors;
            }
        }

        if (env('APP_DEBUG') === true && $this->exception) {
            $data['debug'] = [
                'message' => $this->exception->getMessage(),
                'code' => $this->exception->getCode(),
                'file' => $this->exception->getFile(),
                'line' => $this->exception->getLine(),
                'tracers' => $this->exception->getTraceAsString(),
            ];
            if ($this->exception instanceof ApiException) {
                $data['debug']['message'] = $this->exception->getPrivateMessage();
            }
        }

        return response()->json($data, $this->statusCode, $this->headers);
    }
}
