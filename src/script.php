<?php

use Homework\CommissionTask\Config\CommissionConfig;
use Homework\CommissionTask\Config\CurrencyConfig;
use Homework\CommissionTask\Model\OperationEntity;
use Homework\CommissionTask\Service\CommissionCalculatorService;

require_once __DIR__ . '/../vendor/autoload.php';

if ($argc < 2) {
    echo "Usage: php script.php <input_file>\n";
    exit(1);
}

$inputFile = $argv[1];
$csvFile = __DIR__ . '/../data/' . $inputFile;


if (!file_exists($csvFile)) {
    die("CSV file not found.\n");
}

$handle = fopen($csvFile, 'r');
if (!$handle) {
    die("Failed to open CSV file.\n");
}


while (($data = fgetcsv($handle)) !== false) {
    // Skip empty rows
    if (count($data) < 6)
        continue;

    list($date, $userId, $userType, $operationType, $amount, $currency) = $data;
    
    $operationEntity = new OperationEntity($data);
    
    $commissionConfig = new CommissionConfig();
    $currencyConfig = new CurrencyConfig();
    $commisionCalculatorService = new CommissionCalculatorService(
        $operationEntity,
        $commissionConfig,
        $currencyConfig
    );
    $commisionValue = $commisionCalculatorService->getCommissionValue();

    echo "Date: $date\n";
    echo "User ID: $userId\n";
    echo "User Type: $userType\n";
    echo "Operation Type: $operationType\n";
    echo "Amount: $amount\n";
    echo "Currency: $currency\n";
    echo "Commission: $commisionValue\n";
    echo "--------------------------\n";


}

fclose($handle);