<?php

namespace Bitpin\Api\DTOs;

class NetworkWithMaintenance extends BaseDTO
{
    public Network $network;
    public bool $depositUnderMaintenance;
    public bool $withdrawUnderMaintenance;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->network = Network::fromArray($data['network'] ?? []);
        $dto->depositUnderMaintenance = $data['deposit_under_maintenance'] ?? false;
        $dto->withdrawUnderMaintenance = $data['withdraw_under_maintenance'] ?? false;
        return $dto;
    }
}