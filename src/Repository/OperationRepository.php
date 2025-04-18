<?php

declare(strict_types=1);

namespace Homework\CommissionTask\Repository;
use Homework\CommissionTask\Model\OperationEntity;
use DateTime;
use DateTimeZone;

class OperationRepository
{
    /** @var OperationEntity[] */
    private $storage = [];

    public function save(OperationEntity $entity)
    {
        $this->storage[$entity->id] = $entity;
    }

    public function getById(int $id)
    {
        return isset($this->storage[$id]) ? $this->storage[$id] : null;
    }

    /**
     *  Get all operations.
     * @return OperationEntity[]
     */
    public function getAll(): array
    {
        return array_values($this->storage);
    }


    /**
     * Get operations filtered by date range and operation type,
     * and calculate the total amount.
     *
     * @param DateTime $start
     * @param DateTime $end
     * @param string|null $operationType
     * @return array{operations: OperationEntity[], total: string}
     */
    public function getByDateRangeAndOperationTypeWithTotal(int $userId, DateTime $startDate, DateTime $endDate, string $operationType): array
    {
        return array_filter(
            $this->storage,
            function (OperationEntity $entity) use ($userId, $startDate, $endDate, $operationType): bool {
                $matchesUserId = $entity->userId === $userId;
                $inDateRange = $entity->date >= $startDate && $entity->date <= $endDate;
                $matchesType = $entity->operationType === $operationType;
        
                return $matchesUserId && $inDateRange && $matchesType;
            }
        );
    }
    
}