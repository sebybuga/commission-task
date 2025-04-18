<?php

namespace Homework\CommissionTask\Model;

use DateTime;


class OperationEntity {
    public $id;
    public $date;
    public $userId;
    public $userType;
    public $operationType;
    public $amount;
    public $currency;

    public function __construct(array $data) {
        [$date, $userId, $userType, $opType, $amount, $currency] = $data;
        $this->id = $this->generateBigRandomId();
        $this->date = new DateTime($date);
        $this->userId = (int)$userId;
        $this->userType = $userType;
        $this->operationType = $opType;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    private function generateBigRandomId(): string
    {
        return bin2hex(random_bytes(8)); // 16-char hex = 64-bit
        // For 128-bit: bin2hex(random_bytes(16));
    }

    public function getDate(): DateTime {
        return $this->date;
    }

    public function setDate(DateTime $date) {
        $this->date = $date;
    }

    public function getUserId(): int {
        return $this->userId;
    }

    public function setUserId(int $userId) {
        $this->userId = $userId;
    }

    public function getUserType(): string {
        return $this->userType;
    }

    public function setUserType(string $userType) {
        $this->userType = $userType;
    }

    public function getOperationType(): string {
        return $this->operationType;
    }

    public function setOperationType(string $operationType){
        $this->operationType = $operationType;
    }

    public function getAmount(): string {
        return $this->amount;
    }

    public function setAmount(string $amount) {
        $this->amount = $amount;
    }

    public function getCurrency(): string {
        return $this->currency;
    }

    public function setCurrency(string $currency) {
        $this->currency = $currency;
    }
}