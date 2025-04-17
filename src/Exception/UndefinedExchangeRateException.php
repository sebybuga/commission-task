<?php

declare(strict_types=1);

namespace Homework\CommissionTask\Exception;

    use Exception;

class UndefinedExchangeRateException extends Exception
{
    public function __construct(string $fromCurrency, string $toCurrency)
    {
        $message = "Exchange rate from {$fromCurrency} to {$toCurrency} is not defined.";
        parent::__construct($message);
    }
}

