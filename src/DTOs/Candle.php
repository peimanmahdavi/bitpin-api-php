<?php

namespace Bitpin\Api\DTOs;

class Candle extends BaseDTO
{
    public float $open;
    public float $close;
    public string $low;
    public string $high;
    public string $volume;
    public float $ts;
    public string $resolution;
    public int $time;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->open = (float)($data['open'] ?? 0);
        $dto->close = (float)($data['close'] ?? 0);
        $dto->low = $data['low'] ?? '0';
        $dto->high = $data['high'] ?? '0';
        $dto->volume = $data['volume'] ?? '0';
        $dto->ts = (float)($data['ts'] ?? 0);
        $dto->resolution = $data['resolution'] ?? '30m';
        $dto->time = (int)($data['time'] ?? 0);
        return $dto;
    }

    public function getLowAsFloat(): float
    {
        return (float)$this->low;
    }

    public function getHighAsFloat(): float
    {
        return (float)$this->high;
    }

    public function getVolumeAsFloat(): float
    {
        return (float)$this->volume;
    }

    public function getDateTime(): \DateTime
    {
        return (new \DateTime())->setTimestamp((int)$this->ts);
    }

    public function isBullish(): bool
    {
        return $this->close > $this->open;
    }

    public function isBearish(): bool
    {
        return $this->close < $this->open;
    }
}