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


    public function getCommissionValue(): float
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

        $startDate = clone $this->operationEntity->getDate();
        $endDate = clone $startDate;

        $weekHistoryData = $this->operationService->getOperationsByDate(
            $this->operationEntity->getUserId(),
            $startDate->modify('monday this week'),
            $endDate->modify('sunday this week'),
            $this->operationEntity->getOperationType()
        );

        $withdrawWeekCount = $weekHistoryData['operationCount'];
        $amountCurrencyDefault = $currency === $this->commissionConfig->getCurrencyDefault() ?
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

        $totalAmountCurrencyDefault = $weekHistoryTotalAmountCurrencyDefault + $amountCurrencyDefault;
        
        //check if the total amount in default currency is greater than the free withdraw limit
        if ($totalAmountCurrencyDefault > $this->commissionConfig->getFreeWithdrawLimit()) {
            return $weekHistoryTotalAmountCurrencyDefault > $this->commissionConfig->getFreeWithdrawLimit() ?
                $amount :
                $this->currencyService->convertCurrency(
                    $totalAmountCurrencyDefault - $this->commissionConfig->getFreeWithdrawLimit(),
                    $currencyDefault,
                    $currency
                );
        }

        return 0;
    }




}
