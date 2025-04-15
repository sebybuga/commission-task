<?php

namespace Homework\CommissionTask\Exception;

use Exception;

class InvalidUserTypeException extends Exception
{
    public function __construct(string $userType, int $code = 0, Exception $previous = null)
    {
        $message = "Invalid user type: {$userType}";
        parent::__construct($message, $code, $previous);
    }
}