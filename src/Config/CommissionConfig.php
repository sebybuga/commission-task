<?php
namespace Homework\CommissionTask\Config;

class CommissionConfig
{
    const CONFIG_FILE = 'commission.json';
    private $commissionMap;


    public function __construct()
    {
        $this->commissionMap = json_decode(
            file_get_contents(
                __DIR__ . DIRECTORY_SEPARATOR . self::CONFIG_FILE
            ),
            true
        );
    }


    public function getCommissionRateDeposit(): float
    {
        return $this->configData['commission_rate_deposit'] ?? 0.0; // Default value if not set
    }
    public function getCommissionRateWithdrawPrivate(): float
    {
        return (float) $this->commissionMap['commission_rate_withdraw_private'] ?? 0.0;
    }
    public function getCommissionRateWithdrawBusiness(): float
    {
        return (float) $this->commissionMap['commission_rate_withdraw_business'] ?? 0.0;
    }

    public function getFreeWithdrawLimit(): float
    {
        return (float) $this->commissionMap['free_withdraw_limit'] ?? 0.0;
    }

    public function getFreeWithdrawCount(): int
    {
        return (int) $this->commissionMap['free_withdraw_count'] ?? 0;
    }
    public function getCurrencyDefault(): string
    {
        return (float) ($this->commissionMap['currency_default'] ?? 'EUR');
    }






}