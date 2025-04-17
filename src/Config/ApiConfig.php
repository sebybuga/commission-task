<?php

declare(strict_types=1);

namespace Homework\CommissionTask\Config;

use Homework\CommissionTask\Exception\UndefinedExchangeRateException;
use PharIo\Manifest\InvalidUrlException;

class ApiConfig
{
        
    private $apiUrlMap;

    public function __construct(DataProviderInterface $provider)
    {
        $this->apiUrlMap = $provider->getData();
    }

    public function getExchangeApiUrl(): string
    {
        if (!isset($this->apiUrlMap['exchange_api_url'])) {
            throw new InvalidUrlException("Exchange rate URL not defined!");
        }

        return $this->apiUrlMap['exchange_api_url'];
    }
    public function getAccessKey(): string
    {
        if (!isset($this->apiUrlMap['exchange_api_key'])) {
            throw new InvalidUrlException("Exchange access key not defined!");
        }

        return $this->apiUrlMap['exchange_api_key'];
    }

}