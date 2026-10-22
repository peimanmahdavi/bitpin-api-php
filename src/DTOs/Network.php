<?php

namespace Bitpin\Api\DTOs;

class Network extends BaseDTO
{
    public int $id;
    public string $code;
    public string $title;
    public string $titleFa;
    public bool $canDeposit;
    public bool $canWithdraw;
    public string $addressPattern;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->id = $data['id'] ?? 0;
        $dto->code = $data['code'] ?? '';
        $dto->title = $data['title'] ?? '';
        $dto->titleFa = $data['title_fa'] ?? '';
        $dto->canDeposit = $data['can_deposit'] ?? false;
        $dto->canWithdraw = $data['can_withdraw'] ?? false;
        $dto->addressPattern = $data['address_pattern'] ?? '';
        return $dto;
    }
}