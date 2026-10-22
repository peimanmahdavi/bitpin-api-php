<?php

namespace Bitpin\Api\DTOs;

class MarketData extends BaseDTO
{
    public string $symbol;
    public string $name;
    public string $base;
    public string $quote;
    public bool $tradable;
    public int $pricePrecision;
    public int $baseAmountPrecision;
    public int $quoteAmountPrecision;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->symbol = $data['symbol'] ?? '';
        $dto->name = $data['name'] ?? '';
        $dto->base = $data['base'] ?? '';
        $dto->quote = $data['quote'] ?? '';
        $dto->tradable = $data['tradable'] ?? false;
        $dto->pricePrecision = $data['price_precision'] ?? 0;
        $dto->baseAmountPrecision = $data['base_amount_precision'] ?? 0;
        $dto->quoteAmountPrecision = $data['quote_amount_precision'] ?? 0;
        return $dto;
    }
}

