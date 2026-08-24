<?php

namespace App\Exceptions;

class AppException extends \Exception
{
    protected int $statusCode;

    public function __construct(string $message = "", int $code = 0, int $statusCode = 500, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
