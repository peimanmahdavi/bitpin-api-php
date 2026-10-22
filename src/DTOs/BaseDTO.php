<?php

namespace Bitpin\Api\DTOs;

use JsonSerializable;

abstract class BaseDTO implements JsonSerializable
{
    abstract public static function fromArray(array $data): self;

    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $data = [];
        foreach ($properties as $property) {
            $value = $property->getValue($this);
            if ($value instanceof JsonSerializable) {
                $data[$property->getName()] = $value->jsonSerialize();
            } elseif (is_array($value)) {
                $data[$property->getName()] = array_map(
                    fn($item) => $item instanceof JsonSerializable ? $item->jsonSerialize() : $item,
                    $value
                );
            } else {
                $data[$property->getName()] = $value;
            }
        }
        return $data;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}