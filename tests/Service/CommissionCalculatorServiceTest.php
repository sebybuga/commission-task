<?php

use PHPUnit\Framework\TestCase;
use Homework\CommissionTask\Service\CommissionCalculatorService;
use Homework\CommissionTask\Model\OperationEntity;
use Homework\CommissionTask\Config\CommissionConfig;
use Homework\CommissionTask\Service\CurrencyService;
use Homework\CommissionTask\Service\OperationService;

class CommissionCalculatorServiceTest extends TestCase
{
    public function testDepositCommission()
    {
        $data = ['2025-04-14', '1', 'private', 'deposit', '1000.00', 'EUR'];
        $operation = new OperationEntity($data);

        $commissionConfig = $this->createMock(CommissionConfig::class);
        $commissionConfig->method('getCommissionRateDeposit')->willReturn('0.0003');

        $currencyService = $this->createMock(CurrencyService::class);
        $currencyService->method('roundUpToCurrency')->willReturn('0.30');

        $operationService = $this->createMock(OperationService::class);

        $calculator = new CommissionCalculatorService($operation, $commissionConfig, $currencyService, $operationService);
        $result = $calculator->getCommissionValue();

        $this->assertEquals('0.30', $result);
    }

    public function testBusinessWithdrawCommission()
    {
        $data = ['2025-04-14', '2', 'business', 'withdraw', '500.00', 'EUR'];
        $operation = new OperationEntity($data);

        $commissionConfig = $this->createMock(CommissionConfig::class);
        $commissionConfig->method('getCommissionRateWithdrawBusiness')->willReturn('0.005');

        $currencyService = $this->createMock(CurrencyService::class);
        $currencyService->method('roundUpToCurrency')->willReturn('2.50');

        $operationService = $this->createMock(OperationService::class);

        $calculator = new CommissionCalculatorService($operation, $commissionConfig, $currencyService, $operationService);
        $result = $calculator->getCommissionValue();

        $this->assertEquals('2.50', $result);
    }

    public function testPrivateWithdrawFreeCommission()
    {
        $data = ['2025-04-14', '3', 'private', 'withdraw', '200.00', 'EUR'];
        $operation = new OperationEntity($data);

        $commissionConfig = $this->createMock(CommissionConfig::class);
        $commissionConfig->method('getCommissionRateWithdrawPrivate')->willReturn('0.003');
        $commissionConfig->method('getCurrencyDefault')->willReturn('EUR');
        $commissionConfig->method('getFreeWithdrawCount')->willReturn(3);
        $commissionConfig->method('getFreeWithdrawLimit')->willReturn('1000.00');

        $currencyService = $this->createMock(CurrencyService::class);
        $currencyService->method('convertCurrency')->willReturn('200.00');
        $currencyService->method('roundUpToCurrency')->willReturn('0.00');

        $operationService = $this->createMock(OperationService::class);
        $operationService->method('getOperationsByDate')->willReturn([
            'operationCount' => 1,
            'totalAmount' => '0.00'
        ]);

        $calculator = new CommissionCalculatorService($operation, $commissionConfig, $currencyService, $operationService);
        $result = $calculator->getCommissionValue();

        $this->assertEquals('0.00', $result);
    }
}