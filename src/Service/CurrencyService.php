<?php

declare(strict_types=1);

namespace Homework\CommissionTask\Service;

use Homework\CommissionTask\Config\CurrencyConfig;


class CurrencyService
{
    private $currencyConfig;
    public function __construct(CurrencyConfig $currencyConfig)
    {
        $this->currencyConfig = $currencyConfig;
    }

    public function roundUpToCurrency(float $amount, string $currency): float
    {

        $decimalPlaces = $this->currencyConfig->getPrecision($currency);
        $multiplier = 10 ** $decimalPlaces;
        $scaled = $amount * $multiplier;
        $rounded = ceil($scaled);

        return $rounded / $multiplier;
    }

    public function convertCurrency(float $amount, string $currency): float
    {
        // TODO Example conversion rates (you should replace this with actual logic or API calls)
        $conversionRates = [
            'EUR' => 1.0,
            'USD' => 1.1,
            'JPY' => 130.0,
        ];

        if (!isset($conversionRates[$currency])) {
            throw new \InvalidArgumentException("Unsupported currency: $currency");
        }

        return $amount / $conversionRates[$currency];

    }

}
