<?php
namespace Homework\CommissionTask\Config;

class CurrencyConfig
{
    const CONFIG_FILE = 'currencies.json';
    private $currencyMap;

    public function __construct()
    {
        $this->currencyMap = json_decode(
            file_get_contents(
                __DIR__ . DIRECTORY_SEPARATOR . self::CONFIG_FILE
            ),
            true
        );
    }

    public function getPrecision(string $currency): int
    {
        return $this->currencyMap[$currency] ?? 2; // 2 decimals by default
    }

    public function getCurrencies(): array
    {
        return array_keys($this->currencyMap);
    }

    public function isCurrencySupported(string $currency): bool
    {
        return isset($this->currencyMap[$currency]);
    }
}