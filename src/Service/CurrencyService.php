<?php

declare(strict_types=1);

namespace Homework\CommissionTask\Service;

use Homework\CommissionTask\Config\CurrencyConfig;
use Homework\CommissionTask\Config\TestExchangeRatesConfig;
use Homework\CommissionTask\Exception\UndefinedExchangeRateException;
use Homework\CommissionTask\Exception\InvalidExchangeRatesFormatException;
use Homework\CommissionTask\Exception\ExchangeRateLoadException;
use Homework\CommissionTask\Config\ApiConfig;

use Homework\CommissionTask\Exception\InvalidCurrencySettings;
use Homework\CommissionTask\Exception\CurrencyConversionException;

class CurrencyService
{
    private $currencyConfig;
    private $testExchangeRatesConfig;
    private $apiUrl;

    /**
     * Builds the exchange rate API URL.
     *
     * @param string $baseUrl
     * @param string $accessKey
     * @param array $currencies
     * @return string
     */
    private function buildExchangeRateUrl(string $baseUrl, string $accessKey, array $currencies): string
    {
        $baseUrl .= date('Y-m-d');
        $currencyList = implode(',', $currencies);
        return sprintf('%s?access_key=%s&symbols=%s', $baseUrl, $accessKey, $currencyList);
    }

    private $exchangeRates;


    public function __construct(
            CurrencyConfig $currencyConfig, 
            ApiConfig $apiConfig, 
            TestExchangeRatesConfig $testExchangeRatesConfig, 
            bool $useApi = true
        )
    {

        $this->currencyConfig = $currencyConfig;
        $this->testExchangeRatesConfig = $testExchangeRatesConfig;
        $this->apiUrl = $this->buildExchangeRateUrl(
            $apiConfig->getExchangeApiUrl(),
            $apiConfig->getAccessKey(),
            $currencyConfig->getCurrencies()
        );

        $this->loadExchangeRates($useApi);

    }

    private function loadExchangeRates(bool $useApi)
    {
        if (!$useApi) {
            // Mock exchange rates for testing purposes
            $this->exchangeRates = $this->testExchangeRatesConfig->getExchangeRates();
            if (!$this->exchangeRates) {
                throw new UndefinedExchangeRateException('Test exchange rates not defined', "");
            }
            return;
        }
        // Load exchange rates from API
        $response = @file_get_contents($this->apiUrl);
        if (!$response) {
            throw new ExchangeRateLoadException();
        }

        $data = json_decode($response, true);

        if (!isset($data['base']) || !isset($data['rates'])) {
            throw new InvalidExchangeRatesFormatException();
        }

        $base = $data['base']; // 'EUR' as base currency
        $this->exchangeRates[$base] = $data['rates'];


        foreach ($data['rates'] as $currency => $rate) {
            $this->exchangeRates[$currency][$base] = 1 / $rate;
        }
    }

    public function convertCurrency(string $amount, string $currencyOrigin, string $currencyToConvert): string
    {
        try {

            $currencyOrigin = strtoupper($currencyOrigin);
            $currencyToConvert = strtoupper($currencyToConvert);

            if ($currencyOrigin === $currencyToConvert) {
                return $this->roundUpToCurrency($amount, $currencyToConvert);
            }

            if (!isset($this->exchangeRates[$currencyOrigin][$currencyToConvert])) {
                throw new UndefinedExchangeRateException($currencyOrigin, $currencyToConvert);
            }

            $converted = bcmul($amount, (string) $this->exchangeRates[$currencyOrigin][$currencyToConvert], 4);
            return $this->roundUpToCurrency($converted, $currencyToConvert);


        } catch (UndefinedExchangeRateException $e) {
            throw new CurrencyConversionException("Error converting currency: " . $e->getMessage());
        }

    }

    public function roundUpToCurrency(string $value, string $currency): string
    {
        $decimals = $this->currencyConfig->getPrecision($currency);

        if (!is_int($decimals) || $decimals < 0) {
            throw new InvalidCurrencySettings('Invalid precision for currency: ' . $currency);
        }
        $factor = bcpow('10', (string) $decimals);
        $scaled = bcmul($value, $factor, $decimals + 1);

        // Use float to ceil, then convert back to string
        $ceiled = (string) ceil((float) $scaled);

        return bcdiv($ceiled, $factor, $decimals);
    }

}
