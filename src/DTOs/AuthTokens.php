<?php

namespace Bitpin\Api\DTOs;

class AuthTokens extends BaseDTO
{
    public string $access;
    public string $refresh;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->access = $data['access'] ?? '';
        $dto->refresh = $data['refresh'] ?? '';
        return $dto;
    }

    public function isValid(): bool
    {
        return !empty($this->access) && !empty($this->refresh);
    }
}

class AccessToken extends BaseDTO
{
    public string $access;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->access = $data['access'] ?? '';
        return $dto;
    }
}