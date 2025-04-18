<?php

declare(strict_types=1);

namespace Homework\CommissionTask\Config;

use Homework\CommissionTask\Exception\UndefinedExchangeRateException;
use PharIo\Manifest\InvalidUrlException;

class TestExchangeRatesConfig
{
       
    private $exchangeRatesMap;

    public function __construct(DataProviderInterface $provider)
    {
        $this->exchangeRatesMap = $provider->getData();
    }

    
    public function getExchangeRates(): array
    {
        
        return $this->exchangeRatesMap;
    }

}