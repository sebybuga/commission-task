<?php

declare(strict_types=1);

namespace Homework\CommissionTask\Service;

use Homework\CommissionTask\Config\CurrencyConfig;
use Homework\CommissionTask\Exception\UndefinedExchangeRateException;


class CurrencyService
{
    private $currencyConfig;

    private $exchangeRates = [
        'EUR' => [
            'USD' => 1.1497,
            'JPY' => 129.53,
        ],
        'USD' => [
            'EUR' => 1 / 1.1497,
        ],
        'JPY' => [
            'EUR' => 1 / 129.53,
        ],
    ];

    public function __construct(CurrencyConfig $currencyConfig)
    {
        $this->currencyConfig = $currencyConfig;
    }
    

    public function convertCurrency(float $amount, string $currencyOrigin, string $currencyToConvert): float
    {
        $currencyOrigin = strtoupper($currencyOrigin);
        $currencyToConvert = strtoupper($currencyToConvert);

        if ($currencyOrigin === $currencyToConvert) {
            return $this->roundUpToCurrency($amount, $currencyToConvert);
        }

        if (!isset($this->exchangeRates[$currencyOrigin][$currencyToConvert])) {
            // Try via EUR
            if (isset($this->exchangeRates[$currencyOrigin]['EUR']) && isset($this->exchangeRates['EUR'][$currencyToConvert])) {
                $eurAmount = $amount * $this->exchangeRates[$currencyOrigin]['EUR'];
                $converted = $eurAmount * $this->exchangeRates['EUR'][$currencyToConvert];
                return $this->roundUpToCurrency($converted, $currencyToConvert);
            }

            throw new UndefinedExchangeRateException($currencyOrigin, $currencyToConvert);
        }

        $converted = $amount * $this->exchangeRates[$currencyOrigin][$currencyToConvert];
        return $this->roundUpToCurrency($converted, $currencyToConvert);
    }

    public function roundUpToCurrency(float $value, string $currency): float
    {
        // Define decimal places per currency
        switch (strtoupper($currency)) {
            case 'JPY':                
                $decimals = 0;
                break;
            case 'USD':
            case 'EUR':
                $decimals = 2;
                break;
            default:
                $decimals = 2; // fallback default
        }
        
        $factor = pow(10, $decimals);

        // Round up to the nearest decimal place
        return ceil($value * $factor) / $factor;
    }

}
