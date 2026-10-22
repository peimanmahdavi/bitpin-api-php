<?php

namespace Bitpin\Api\DTOs;

class Ticker extends BaseDTO
{
    public string $symbol;
    public string $price;
    public float $dailyChangePrice;
    public string $low;
    public string $high;
    public float $timestamp;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->symbol = $data['symbol'] ?? '';
        $dto->price = $data['price'] ?? '0';
        $dto->dailyChangePrice = (float)($data['daily_change_price'] ?? 0);
        $dto->low = $data['low'] ?? '0';
        $dto->high = $data['high'] ?? '0';
        $dto->timestamp = (float)($data['timestamp'] ?? 0);
        return $dto;
    }

    public function getPriceAsFloat(): float
    {
        return (float)$this->price;
    }

    public function getLowAsFloat(): float
    {
        return (float)$this->low;
    }

    public function getHighAsFloat(): float
    {
        return (float)$this->high;
    }
}