<?php

namespace App\Domain\Account\Events;

use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class AccountCreated implements SerializablePayload
{
    public function __construct(
        public string $name,
        public int $userId,
    ) {}

    public function toPayload(): array
    {
        return [
            'name' => $this->name,
            'userId' => $this->userId,
        ];
    }

    public static function fromPayload(array $payload): self
    {
        return new self(
            $payload['name'],
            $payload['userId'],
        );
    }
}
