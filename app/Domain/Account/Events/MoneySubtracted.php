<?php

namespace App\Domain\Account\Events;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class MoneySubtracted implements SerializablePayload
{
    public function __construct(
        public int $amount,
    ) {}

    public function toPayload(): array
    {
        return [
            'amount' => $this->amount,
        ];
    }

    public static function fromPayload(array $payload): self
    {
        return new self(
            $payload['amount'],
        );
    }
}
