<?php

namespace App\Domain\Account\Events;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class MoreMoneyNeeded implements SerializablePayload
{
    public function toPayload(): array
    {
        return [];
    }

    public static function fromPayload(array $payload): self
    {
        return new self();
    }
}
