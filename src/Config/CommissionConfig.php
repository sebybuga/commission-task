<?php
namespace Homework\CommissionTask\Config;

class CommissionConfig
{
    
    private $commissionMap;


    public function __construct(DataProviderInterface $provider)
    {
        $this->commissionMap = $provider->getData();
    }


    public function getCommissionRateDeposit(): string
    {
        return $this->commissionMap['commission_rate_deposit'] ?? "0.0"; // Default value if not set
    }
    public function getCommissionRateWithdrawPrivate(): string
    {
        return (string) $this->commissionMap['commission_rate_withdraw_private'] ?? "0.0";
    }
    public function getCommissionRateWithdrawBusiness(): string
    {
        return (string) $this->commissionMap['commission_rate_withdraw_business'] ?? "0.0";
    }

    public function getFreeWithdrawLimit(): string
    {
        return (string) $this->commissionMap['free_withdraw_limit'] ?? "0.0";
    }

    public function getFreeWithdrawCount(): int
    {
        return (int) $this->commissionMap['free_withdraw_count'] ?? 0;
    }
    
    public function getCurrencyDefault(): string
    {
        return $this->currencyMap['currency_default'] ?? 'EUR';
    }




}