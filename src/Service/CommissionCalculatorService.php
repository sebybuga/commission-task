<?php

declare(strict_types=1);

namespace Homework\CommissionTask\Service;

use Homework\CommissionTask\Config\CommissionConfig;
use Homework\CommissionTask\Config\CurrencyConfig;
use Homework\CommissionTask\Model\OperationEntity;

use Homework\CommissionTask\Exception\InvalidUserTypeException;

class CommissionCalculatorService
{
    private $commissionValue;
    private $currencyService;
    private $commissionConfig;

    public function __construct(
        OperationEntity $operationEntity,
        CommissionConfig $commissionConfig,
        CurrencyConfig $currencyConfig
    ) {
        $this->commissionConfig = $commissionConfig;
        $this->currencyService = new CurrencyService($currencyConfig);
        $this->commissionValue = $this->calculateCommission($operationEntity);

    }


    public function getCommissionValue(): float
    {
        return $this->commissionValue;
    }

    private function calculateCommission(OperationEntity $operationEntity)
    {
        $amount = $operationEntity->getAmount();
        $operationType = $operationEntity->getOperationType();
        $userType = $operationEntity->getUserType();
        $currency = $operationEntity->getCurrency();

        if ($operationType === "deposit") {
            return $this->calculateDepositCommission($amount);
        }

        if ($operationType === "withdraw") {
            return $this->calculateWithdrawCommission($amount, $userType, $currency);
        }

        return null;
    }

    private function calculateDepositCommission(float $amount): float
    {
        return $amount * $this->commissionConfig->getCommissionRateDeposit();
    }

    private function calculateWithdrawCommission(float $amount, string $userType, string $currency): float
    {
        if ($userType === "business") {
            return $this->calculateBusinessWithdrawCommission($amount, $currency);
        }

        if ($userType === "private") {
            return $this->calculatePrivateWithdrawCommission($amount, $currency);
        }

        throw new InvalidUserTypeException($userType);
    }

    private function calculateBusinessWithdrawCommission(float $amount, string $currency): float
    {
        $commissionValue = $amount * $this->commissionConfig->getCommissionRateWithdrawBusiness();
        return $this->currencyService->roundUpToCurrency($commissionValue, $currency);
    }

    private function calculatePrivateWithdrawCommission(float $amount, string $currency): float
    {
        $taxableAmount = $this->getTaxableAmount($amount, $currency);
        $commissionValue = $taxableAmount * $this->commissionConfig->getCommissionRateWithdrawPrivate();
        return $this->currencyService->roundUpToCurrency($commissionValue, $currency);
    }

    private function getTaxableAmount(float $amount, string $currency): float
    {

        $withdrawalWeekCount = 0; //TODO get data from history

        if ($withdrawalWeekCount > $this->commissionConfig->getFreeWithdrawCount()) {
            return $amount;
        }

        $taxableAmount = 0 + $amount;  //TODO get history data and replace 0
        $taxableAmountEUR = $this->currencyService->convertCurrency($taxableAmount, 'EUR');

        if ($taxableAmountEUR > $this->commissionConfig->getFreeWithdrawLimit()) {
            $remainingSumEUR = $taxableAmountEUR - $this->commissionConfig->getFreeWithdrawLimit();
            return $this->currencyService->convertCurrency($remainingSumEUR, $currency);
        }

        return 0;
    }




}
