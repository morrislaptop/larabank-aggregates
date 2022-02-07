<?php

declare(strict_types=1);

namespace App\Domain\Account;

use App\Domain\Account\Projectors\AccountProjector;
use App\Domain\Account\Projectors\TransactionCountProjector;
use App\Domain\Account\Reactors\OfferLoanReactor;
use EventSauce\LaravelEventSauce\AggregateRootRepository;

/** @method AccountAggregateRoot retrieve(AccountId $aggregateRootId) */
final class AccountRepository extends AggregateRootRepository
{
    protected string $aggregateRoot = AccountAggregateRoot::class;

    protected string $table = 'accounts_messages';

    protected array $consumers = [
        AccountProjector::class,
        TransactionCountProjector::class,
        OfferLoanReactor::class,
    ];
}
