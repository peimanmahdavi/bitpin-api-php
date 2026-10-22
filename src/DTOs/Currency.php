<?php

namespace Bitpin\Api\DTOs;

class Currency extends BaseDTO
{
    public string $currency;
    public string $name;
    public bool $tradable;
    public string $precision;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->currency = $data['currency'] ?? '';
        $dto->name = $data['name'] ?? '';
        $dto->tradable = $data['tradable'] ?? false;
        $dto->precision = $data['precision'] ?? '0';
        return $dto;
    }
}