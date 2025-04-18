<?php

declare(strict_types=1);

namespace Homework\CommissionTask\Service;

use Homework\CommissionTask\Repository\OperationRepository;
use Homework\CommissionTask\Model\OperationEntity;
use Homework\CommissionTask\Config\CommissionConfig;
use DateTime;
use InvalidArgumentException;

class OperationService
{
    /**
     * @var OperationRepository
     */
    private $repository;
    private $commissionConfig;
    private $currencyService;

    public function __construct(OperationRepository $repository, CommissionConfig $commissionConfig, CurrencyService $currencyService)
    {
        $this->repository = $repository;
        $this->commissionConfig = $commissionConfig;
        $this->currencyService = $currencyService;
    }

    public function saveOperation(OperationEntity $operation)
    {
        $this->repository->save($operation);
    }

    public function getOperationsByDate(int $userId, DateTime $startDate, DateTime $endDate, $operationType): array
    {

        $filtered = $this->repository->getByDateRangeAndOperationTypeWithTotal($userId, $startDate, $endDate, $operationType);
        $totalAmount = array_reduce(
            $filtered,
            function (string $sum, OperationEntity $entity): string {
                if ($entity->getCurrency() == $this->commissionConfig->getCurrencyDefault()) {
                    return bcadd( $sum,  $entity->amount,4);
                }
                return bcadd($sum, $this->currencyService->convertCurrency(
                    $entity->getAmount(),
                    $entity->getCurrency(),
                    $this->commissionConfig->getCurrencyDefault()
                ), 2);
            },
            "0"
        );

        return [
            'operationCount' => count($filtered),
            'totalAmount' => (string) $totalAmount,
        ];


    }
    public function validateOperation(OperationEntity $operation): bool
    {

        if (!$operation->date instanceof DateTime) {
            throw new InvalidArgumentException("Invalid date.");
        }

        if (!is_int($operation->userId)) {
            throw new InvalidArgumentException("User ID must be an integer.");
        }

        if (!in_array($operation->userType, ['private', 'business'])) {
            throw new InvalidArgumentException("Invalid user type.");
        }

        if (!in_array($operation->operationType, ['deposit', 'withdraw'])) {
            throw new InvalidArgumentException("Invalid operation type.");
        }

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $operation->amount)) {
            throw new InvalidArgumentException("Amount must be a numeric value.");
        }

        if (!is_string($operation->currency) || strlen($operation->currency) !== 3) {
            throw new InvalidArgumentException("Currency must be a 3-letter string.");
        }
        if ($operation->amount <= 0) {
            throw new InvalidArgumentException("Amount must be greater than zero.");
        }
        return true;
    }
}
