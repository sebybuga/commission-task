<?php

declare(strict_types=1);

namespace Homework\CommissionTask\Service;

use Homework\CommissionTask\Config\CurrencyConfig;
use Homework\CommissionTask\Exception\UndefinedExchangeRateException;
use Homework\CommissionTask\Exception\InvalidExchangeRatesFormatException;
use Homework\CommissionTask\Exception\ExchangeRateLoadException;
use Homework\CommissionTask\Config\ApiConfig;

use Homework\CommissionTask\Exception\InvalidCurrencySettings;

class CurrencyService
{
    private $currencyConfig;
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
    /* = [
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
    ];*/

    public function __construct(CurrencyConfig $currencyConfig, ApiConfig $apiConfig)
    {
        $this->currencyConfig = $currencyConfig;
        $this->apiUrl = $this->buildExchangeRateUrl(
            $apiConfig->getExchangeApiUrl(), 
            $apiConfig->getAccessKey(), 
            $currencyConfig->getCurrencies() 
        );
        $this->loadExchangeRates();
    }
    
    private function loadExchangeRates() 
    {
        
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
        $decimals = $this->currencyConfig->getPrecision($currency);
        
        if (!is_int($decimals) || $decimals < 0) {
            throw new InvalidCurrencySettings('Invalid precision for currency: ' . $currency);
        }
        
        $factor = pow(10, $decimals);

        // Round up to the nearest decimal place
        return ceil($value * $factor) / $factor;
    }

}
