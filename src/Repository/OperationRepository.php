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
     * @return array{operations: OperationEntity[], total: float}
     */
    public function getByDateRangeAndOperationTypeWithTotal(int $userId, DateTime $startDate, DateTime $endDate, string $operationType): array
    {
        return array_filter(
            $this->storage,
            function (OperationEntity $entity) use ($userId, $startDate, $endDate, $operationType): bool {
                $matchesUserId = $entity->userId === $userId;
                $inDateRange = $entity->date >= $startDate && $entity->date <= $endDate;
                $matchesType = $entity->operationType === $operationType;
//                 echo "User match: " . var_export($matchesUserId, true) . "\n";
//                 echo "Date match: " . var_export($inDateRange, true) . "\n";
//                 echo "Type match: " . var_export($matchesType, true) . "\n\n";
//                 echo "Entity: " . $entity->date->format('Y-m-d H:i:s') . "\n";
// echo "Start : " . $startDate->format('Y-m-d H:i:s') . "\n";
// echo "End   : " . $endDate->format('Y-m-d H:i:s') . "\n";
        
                return $matchesUserId && $inDateRange && $matchesType;
            }
        );

        // print_r($this->storage);
        // if (!empty($this->storage)) {
        //     echo "Storage is not empty\n";
        //     // print_r(array_values($this->storage)[0]->date->format('Y-m-d'));
        //     // echo (array_values($this->storage)[0]->date >= $startDate) . "\n";
        //     // echo (array_values($this->storage)[0]->date <= $endDate) . "\n";
        //     // echo (array_values($this->storage)[0]->operationType === $operationType) . "\n";
        //     // echo (array_values($this->storage)[0]->userId === $userId) . "\n";

        // } else {
        //     echo "Storage is empty\n";
        // }

        // echo "Filtered operations:\n";
        // print_r($filtered);



   
    }

    public function delete(int $id): void
    {
        unset($this->storage[$id]);
    }

    public function clear(): void
    {
        $this->storage = [];
    }
}