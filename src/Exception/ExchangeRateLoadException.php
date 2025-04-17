<?php

declare(strict_types=1);

namespace Homework\CommissionTask\Exception;

use Exception;

class ExchangeRateLoadException extends Exception
{
    protected $message = 'Failed to load exchange rates from provider.';
}