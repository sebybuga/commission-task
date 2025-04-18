<?php

declare(strict_types=1);

namespace Homework\CommissionTask\Service;

use Homework\CommissionTask\Config\CommissionConfig;

use Homework\CommissionTask\Model\OperationEntity;

use Homework\CommissionTask\Exception\InvalidUserTypeException;


class CommissionCalculatorService
{
    private $operationEntity;
    private $currencyService;
    private $commissionConfig;
    private $operationService;

    public function __construct(
        OperationEntity $operationEntity,
        CommissionConfig $commissionConfig,
        CurrencyService $currencyService,
        OperationService $operationService


    ) {
        $this->operationEntity = $operationEntity;
        $this->commissionConfig = $commissionConfig;
        $this->currencyService = $currencyService;
        $this->operationService = $operationService;
    }


    public function getCommissionValue(): string
    {
        return $this->calculateCommission($this->operationEntity);
    }

    private function calculateCommission(OperationEntity $operationEntity)
    {

        $amount = $operationEntity->getAmount();
        $operationType = $operationEntity->getOperationType();
        $userType = $operationEntity->getUserType();
        $currency = $operationEntity->getCurrency();

        if ($operationType === "deposit") {
            return $this->calculateDepositCommission($amount, $currency);
        }

        if ($operationType === "withdraw") {
            return $this->calculateWithdrawCommission($amount, $userType, $currency);
        }

        return null;
    }

    private function calculateDepositCommission(string $amount, string $currency): string
    {
        $commissionValue = bcmul($amount, $this->commissionConfig->getCommissionRateDeposit(), 4);
        return $this->currencyService->roundUpToCurrency($commissionValue, $currency);
    }

    private function calculateWithdrawCommission(string $amount, string $userType, string $currency): string
    {
        if ($userType === "business") {
            return $this->calculateBusinessWithdrawCommission($amount, $currency);
        }

        if ($userType === "private") {
            return $this->calculatePrivateWithdrawCommission($amount, $currency);
        }

        throw new InvalidUserTypeException($userType);
    }

    private function calculateBusinessWithdrawCommission($amount, string $currency): string
    {
        $commissionValue = bcmul($amount, $this->commissionConfig->getCommissionRateWithdrawBusiness(), 4);
        return $this->currencyService->roundUpToCurrency($commissionValue, $currency);
    }

    private function calculatePrivateWithdrawCommission(string $amount, string $currency): string
    {
        $taxableAmount = $this->getTaxableAmount($amount, $currency);
        $commissionValue = bcmul($taxableAmount, $this->commissionConfig->getCommissionRateWithdrawPrivate(), 4);
        
        return $this->currencyService->roundUpToCurrency($commissionValue, $currency);
    }

    private function getTaxableAmount(string $amount, string $currency): string
    {

        $startDate = clone $this->operationEntity->getDate();
        $endDate = clone $startDate;

        $weekHistoryData = $this->operationService->getOperationsByDate(
            $this->operationEntity->getUserId(),
            $startDate->modify('monday this week'),
            $endDate->modify('sunday this week'),
            $this->operationEntity->getOperationType()
        );
        
        $withdrawWeekCount = $weekHistoryData['operationCount'];
        $amountCurrencyDefault = ($currency === $this->commissionConfig->getCurrencyDefault()) ?
            $amount :
            $this->currencyService->convertCurrency($amount, $currency, $this->commissionConfig->getCurrencyDefault());
            
        if ($withdrawWeekCount > $this->commissionConfig->getFreeWithdrawCount()) {
            return $amount;
        }

        $currencyDefault = $this->commissionConfig->getCurrencyDefault();

        $weekHistoryTotalAmountCurrencyDefault = $this->currencyService->convertCurrency(
            $weekHistoryData['totalAmount'],
            $currency,
            $currencyDefault
        );

        $totalAmountCurrencyDefault = bcadd($weekHistoryTotalAmountCurrencyDefault, $amountCurrencyDefault, 4);
        //check if the total amount in default currency is greater than the free withdraw limit
        if ($totalAmountCurrencyDefault > $this->commissionConfig->getFreeWithdrawLimit()) {
            return $weekHistoryTotalAmountCurrencyDefault > $this->commissionConfig->getFreeWithdrawLimit() ?
                $amount :
                $this->currencyService->convertCurrency(
                    bcsub($totalAmountCurrencyDefault, $this->commissionConfig->getFreeWithdrawLimit(), 4),
                    $currencyDefault,
                    $currency
                );
        }

        return "0";
    }
}
