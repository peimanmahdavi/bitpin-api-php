<?php

namespace Bitpin\Api\DTOs;

class OrderRequest extends BaseDTO
{
    public string $symbol;
    public string $type; // 'limit' or 'market'
    public string $side; // 'buy' or 'sell'
    public float|string $price;
    public float|string $baseAmount;
    public ?float $stopPrice = null;
    public ?float $ocoTargetPrice = null;
    public ?string $identifier = null;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->symbol = $data['symbol'] ?? '';
        $dto->type = $data['type'] ?? 'limit';
        $dto->side = $data['side'] ?? 'buy';
        $dto->price = $data['price'] ?? 0;
        $dto->baseAmount = $data['base_amount'] ?? 0;
        $dto->stopPrice = isset($data['stop_price']) ? (float) $data['stop_price'] : null;
        $dto->ocoTargetPrice = isset($data['oco_target_price']) ? (float) $data['oco_target_price'] : null;
        $dto->identifier = $data['identifier'] ?? null;
        return $dto;
    }

    public function toArray(): array
    {
        $data = [
            'symbol' => $this->symbol,
            'type' => $this->type,
            'side' => $this->side,
            'price' => $this->price,
            'base_amount' => $this->baseAmount,
        ];

        if ($this->stopPrice !== null) {
            $data['stop_price'] = $this->stopPrice;
        }
        if ($this->ocoTargetPrice !== null) {
            $data['oco_target_price'] = $this->ocoTargetPrice;
        }
        if ($this->identifier !== null) {
            $data['identifier'] = $this->identifier;
        }

        return $data;
    }

    public static function limit(string $symbol, string $side, float $price, float $baseAmount): self
    {
        $dto = new self();
        $dto->symbol = $symbol;
        $dto->type = 'limit';
        $dto->side = $side;
        $dto->price = $price;
        $dto->baseAmount = $baseAmount;
        return $dto;
    }

    public static function market(string $symbol, string $side, float $baseAmount): self
    {
        $dto = new self();
        $dto->symbol = $symbol;
        $dto->type = 'market';
        $dto->side = $side;
        $dto->price = 0; // Market orders don't need price
        $dto->baseAmount = $baseAmount;
        return $dto;
    }

    public static function stopLimit(string $symbol, string $side, float $price, float $baseAmount, float $stopPrice): self
    {
        $dto = self::limit($symbol, $side, $price, $baseAmount);
        $dto->type = 'stop_limit';
        $dto->stopPrice = $stopPrice;
        return $dto;
    }

    public static function oco(string $symbol, string $side, float $price, float $baseAmount, float $stopPrice, float $ocoTargetPrice): self
    {
        $dto = self::stopLimit($symbol, $side, $price, $baseAmount, $stopPrice);
        $dto->type = 'oco';
        $dto->ocoTargetPrice = $ocoTargetPrice;
        return $dto;
    }
}

class Order extends BaseDTO
{
    public int $id;
    public string $symbol;
    public string $type;
    public string $side;
    public string $price;
    public ?string $stopPrice;
    public ?string $ocoTargetPrice;
    public string $baseAmount;
    public string $quoteAmount;
    public ?string $identifier;
    public string $state;
    public ?string $closedAt;
    public string $createdAt;
    public string $dealedBaseAmount;
    public string $dealedQuoteAmount;
    public bool $reqToCancel;
    public string $commission;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->id = $data['id'] ?? 0;
        $dto->symbol = $data['symbol'] ?? '';
        $dto->type = $data['type'] ?? '';
        $dto->side = $data['side'] ?? '';
        $dto->price = $data['price'] ?? '0';
        $dto->stopPrice = $data['stop_price'] ?? null;
        $dto->ocoTargetPrice = $data['oco_target_price'] ?? null;
        $dto->baseAmount = $data['base_amount'] ?? '0';
        $dto->quoteAmount = $data['quote_amount'] ?? '0';
        $dto->identifier = $data['identifier'] ?? null;
        $dto->state = $data['state'] ?? '';
        $dto->closedAt = $data['closed_at'] ?? null;
        $dto->createdAt = $data['created_at'] ?? '';
        $dto->dealedBaseAmount = $data['dealed_base_amount'] ?? '0';
        $dto->dealedQuoteAmount = $data['dealed_quote_amount'] ?? '0';
        $dto->reqToCancel = $data['req_to_cancel'] ?? false;
        $dto->commission = $data['commission'] ?? '0';
        return $dto;
    }

    public function getPriceAsFloat(): float
    {
        return (float) $this->price;
    }

    public function getBaseAmountAsFloat(): float
    {
        return (float) $this->baseAmount;
    }

    public function getQuoteAmountAsFloat(): float
    {
        return (float) $this->quoteAmount;
    }

    public function getDealedBaseAmountAsFloat(): float
    {
        return (float) $this->dealedBaseAmount;
    }

    public function getDealedQuoteAmountAsFloat(): float
    {
        return (float) $this->dealedQuoteAmount;
    }

    public function getCommissionAsFloat(): float
    {
        return (float) $this->commission;
    }

    public function isOpen(): bool
    {
        return in_array($this->state, ['open', 'partial', 'pending']);
    }

    public function isClosed(): bool
    {
        return $this->state === 'closed';
    }

    public function isCancelled(): bool
    {
        return $this->state === 'cancelled';
    }

    public function getFillRatio(): float
    {
        $baseAmount = $this->getBaseAmountAsFloat();
        if ($baseAmount === 0) {
            return 0;
        }
        return $this->getDealedBaseAmountAsFloat() / $baseAmount;
    }
}

class UserOrderUpdate extends BaseDTO
{
    public int $id;
    public int $market;
    public string $symbol;
    public string $type;
    public string $side;
    public string $price;
    public ?string $stopPrice;
    public ?string $ocoTargetPrice;
    public string $baseAmount;
    public string $quoteAmount;
    public ?string $identifier;
    public string $state;
    public string $closedAt;
    public string $createdAt;
    public string $dealedBaseAmount;
    public string $dealedQuoteAmount;
    public bool $reqToCancel;
    public string $commission;
    public string $gainCurrencyCode;
    public string $resourceCurrencyCode;
    public string $fulfilled;
    public string $event;
    public string $eventTime;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->id = $data['id'] ?? 0;
        $dto->market = $data['market'] ?? 0;
        $dto->symbol = $data['symbol'] ?? '';
        $dto->type = $data['type'] ?? '';
        $dto->side = $data['side'] ?? '';
        $dto->price = $data['price'] ?? '0';
        $dto->stopPrice = $data['stop_price'] ?? null;
        $dto->ocoTargetPrice = $data['oco_target_price'] ?? null;
        $dto->baseAmount = $data['base_amount'] ?? '0';
        $dto->quoteAmount = $data['quote_amount'] ?? '0';
        $dto->identifier = $data['identifier'] ?? null;
        $dto->state = $data['state'] ?? '';
        $dto->closedAt = $data['closed_at'] ?? '';
        $dto->createdAt = $data['created_at'] ?? '';
        $dto->dealedBaseAmount = $data['dealed_base_amount'] ?? '0';
        $dto->dealedQuoteAmount = $data['dealed_quote_amount'] ?? '0';
        $dto->reqToCancel = $data['req_to_cancel'] ?? false;
        $dto->commission = $data['commission'] ?? '0';
        $dto->gainCurrencyCode = $data['gain_currency_code'] ?? '';
        $dto->resourceCurrencyCode = $data['resource_currency_code'] ?? '';
        $dto->fulfilled = $data['fulfilled'] ?? '0';
        $dto->event = $data['event'] ?? '';
        $dto->eventTime = $data['event_time'] ?? '';
        return $dto;
    }
}

class UserMatchUpdate extends BaseDTO
{
    public int $id;
    public string $symbol;
    public string $baseAmount;
    public string $quoteAmount;
    public string $price;
    public string $createdAt;
    public string $commission;
    public string $side;
    public string $commissionCurrency;
    public int $orderId;
    public ?string $identifier;
    public string $event;
    public string $eventTime;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->id = $data['id'] ?? 0;
        $dto->symbol = $data['symbol'] ?? '';
        $dto->baseAmount = $data['base_amount'] ?? '0';
        $dto->quoteAmount = $data['quote_amount'] ?? '0';
        $dto->price = $data['price'] ?? '0';
        $dto->createdAt = $data['created_at'] ?? '';
        $dto->commission = $data['commission'] ?? '0';
        $dto->side = $data['side'] ?? '';
        $dto->commissionCurrency = $data['commission_currency'] ?? '';
        $dto->orderId = $data['order_id'] ?? 0;
        $dto->identifier = $data['identifier'] ?? null;
        $dto->event = $data['event'] ?? '';
        $dto->eventTime = $data['event_time'] ?? '';
        return $dto;
    }
}