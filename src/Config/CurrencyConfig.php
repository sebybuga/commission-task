<?php
namespace Homework\CommissionTask\Config;

class CurrencyConfig
{
    private $currencyMap;

    public function __construct(DataProviderInterface $provider)
    {
        $this->currencyMap = $provider->getData();

    }

    public function getPrecision(string $currency): int
    {
        return $this->currencyMap[$currency] ?? 2; // 2 decimals by default
    }

    public function getCurrencies(): array
    {
        return array_keys($this->currencyMap);
    }
}