<?php

namespace Homework\CommissionTask\Config;

use Homework\CommissionTask\Config\DataProviderInterface;

class JsonDataProvider implements DataProviderInterface
{
    private $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function getData(): array
    {
        
        $json = file_get_contents($this->filePath);        
        return json_decode($json, true);
    }
}