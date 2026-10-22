<?php

namespace Bitpin\Api\DTOs;

class TradeMatch extends BaseDTO
{
    public string $id;
    public string $price;
    public string $baseAmount;
    public string $quoteAmount;
    public string $side;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->id = $data['id'] ?? '';
        $dto->price = $data['price'] ?? '0';
        $dto->baseAmount = $data['base_amount'] ?? '0';
        $dto->quoteAmount = $data['quote_amount'] ?? '0';
        $dto->side = $data['side'] ?? '';
        return $dto;
    }

    public function getPriceAsFloat(): float
    {
        return (float)$this->price;
    }

    public function getBaseAmountAsFloat(): float
    {
        return (float)$this->baseAmount;
    }

    public function getQuoteAmountAsFloat(): float
    {
        return (float)$this->quoteAmount;
    }

    public function isBuy(): bool
    {
        return $this->side === 'buy';
    }

    public function isSell(): bool
    {
        return $this->side === 'sell';
    }
}