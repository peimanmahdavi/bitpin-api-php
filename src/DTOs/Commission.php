<?php

namespace Bitpin\Api\DTOs;

class Commission extends BaseDTO
{
    public int $market;
    public string $symbol;
    public float $maker;
    public float $taker;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->market = $data['market'] ?? 0;
        $dto->symbol = $data['symbol'] ?? '';
        $dto->maker = (float)($data['maker'] ?? 0);
        $dto->taker = (float)($data['taker'] ?? 0);
        return $dto;
    }
}