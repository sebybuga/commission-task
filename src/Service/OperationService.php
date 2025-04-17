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
            function (float $sum, OperationEntity $entity): float {
                if ($entity->getCurrency() == $this->commissionConfig->getCurrencyDefault()) {
                    return $sum + $entity->amount;
                }
                return $sum + $this->currencyService->convertCurrency(
                    $entity->getAmount(),
                    $entity->getCurrency(),
                    $this->commissionConfig->getCurrencyDefault()
                );
            },
            0.0
        );

        return [
            'operationCount' => count($filtered),
            'totalAmount' => (float) $totalAmount,
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

        if (!is_float($operation->amount)) {
            throw new InvalidArgumentException("Amount must be a float.");
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
