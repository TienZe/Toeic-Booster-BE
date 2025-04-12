<?php

namespace App\Entities;

class ErrorMessage extends Entity
{
    public function __construct(string $message = "", $code = 0, $validationErrors = null, $exception = null)
    {
        $this->message = $message;
        $this->code = $code;
        $this->validationErrors = $validationErrors;
        $this->exception = $exception;
    }
}

