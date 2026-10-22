<?php

namespace Bitpin\Api\DTOs;

class OrderBook extends BaseDTO
{
    /** @var array<int, array{0: string, 1: string}> */
    public array $asks = [];

    /** @var array<int, array{0: string, 1: string}> */
    public array $bids = [];

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->asks = $data['asks'] ?? [];
        $dto->bids = $data['bids'] ?? [];
        return $dto;
    }

    /** @return array<int, array{price: float, amount: float}> */
    public function getAsksParsed(): array
    {
        return array_map(
            fn($item) => ['price' => (float)$item[0], 'amount' => (float)$item[1]],
            $this->asks
        );
    }

    /** @return array<int, array{price: float, amount: float}> */
    public function getBidsParsed(): array
    {
        return array_map(
            fn($item) => ['price' => (float)$item[0], 'amount' => (float)$item[1]],
            $this->bids
        );
    }

    public function getBestAsk(): ?float
    {
        return $this->asks ? (float)$this->asks[0][0] : null;
    }

    public function getBestBid(): ?float
    {
        return $this->bids ? (float)$this->bids[0][0] : null;
    }

    public function getSpread(): ?float
    {
        $ask = $this->getBestAsk();
        $bid = $this->getBestBid();
        if ($ask !== null && $bid !== null) {
            return $ask - $bid;
        }
        return null;
    }
}