<?php

use Homework\CommissionTask\Config\CommissionConfig;
use Homework\CommissionTask\Config\CurrencyConfig;
use Homework\CommissionTask\Model\OperationEntity;
use Homework\CommissionTask\Service\CommissionCalculatorService;
use Homework\CommissionTask\Config\JsonDataProvider;
use Homework\CommissionTask\Service\CurrencyService;
use Homework\CommissionTask\Repository\OperationRepository;
use Homework\CommissionTask\Service\OperationService;

require_once __DIR__ . '/../vendor/autoload.php';

if ($argc < 2) {
    echo "Usage: php script.php <input_file>\n";
    exit(1);
}


$csvFile = $argv[1];
if (!file_exists($csvFile)) {
    die("CSV file not found.\n");
}

$handle = fopen($csvFile, 'r');
if (!$handle) {
    die("Failed to open CSV file.\n");
}

$showInputValues = (isset($argv[2]) && $argv[2] === '--show-input-values') ? true : false;

$repositoryOperation = new OperationRepository();

while (($data = fgetcsv($handle)) !== false) {
    // Skip empty rows
    if (count($data) < 6) {
        continue;
    }


    $operationEntity = new OperationEntity($data);

    $currencyProvider = new JsonDataProvider(__DIR__ . '/../config/currencies.json');
    $currencyConfig = new CurrencyConfig($currencyProvider);
    $commissionProvider = new JsonDataProvider(__DIR__ . '/../config/commissions.json');
    $commissionConfig = new CommissionConfig($commissionProvider);

    $currencyService = new CurrencyService($currencyConfig);
    $operationService = new OperationService($repositoryOperation, $commissionConfig, $currencyService);

    // Validate input data
    if (!$operationService->validateOperation($operationEntity)) {
        echo "Invalid input data in row: " . implode(',', $data) . "\n";
        continue;
    }

    $commisionCalculatorService = new CommissionCalculatorService(
        $operationEntity,
        $commissionConfig,
        $currencyService,
        $operationService
    );

    $commisionValue = $commisionCalculatorService->getCommissionValue();

    $operationService->saveOperation($operationEntity);


    if ($showInputValues) {
        echo print_r($data) . "\n";
        echo "--------------------------\n";
        var_dump("Records stored:", count($repositoryOperation->getAll()));
        echo "--------------------------\nCommission Value: ";
    }


    echo "$commisionValue\n";

}


fclose($handle);

