<?php

namespace App\Domain\Account;

use EventSauce\EventSourcing\AggregateRootId;

class AccountId implements AggregateRootId
{
    private $id;

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    public function toString(): string
    {
        return $this->id;
    }

    public static function fromString(string $id): self
    {
        return new static($id);
    }
}
