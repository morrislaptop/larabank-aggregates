<?php

namespace Tests\Domain\Account;

use App\Domain\Account\AccountAggregateRoot;
use App\Domain\Account\AccountId;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\TestUtilities\AggregateRootTestCase;
use Illuminate\Support\Str;

abstract class AccountTestCase extends AggregateRootTestCase
{
    protected function newAggregateRootId(): AggregateRootId
    {
        return AccountId::fromString(Str::uuid());
    }

    protected function aggregateRootClassName(): string
    {
        return AccountAggregateRoot::class;
    }

    protected function handle(callable $callback)
    {
        /** @var AccountAggregateRoot */
        $account = $this->retrieveAggregateRoot($this->aggregateRootId());

        $callback($account);

        $this->persistAggregateRoot($account);
    }
}
