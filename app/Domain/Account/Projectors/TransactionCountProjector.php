<?php

namespace App\Domain\Account\Projectors;

use App\Domain\Account\Events\AccountCreated;
use App\Domain\Account\Events\AccountDeleted;
use App\Domain\Account\Events\MoneyAdded;
use App\Domain\Account\Events\MoneySubtracted;
use App\Models\TransactionCount;
use EventSauce\EventSourcing\Message;
use EventSauce\LaravelEventSauce\Consumer;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class TransactionCountProjector extends Consumer
{
    public function handleAccountCreated(AccountCreated $event, Message $message)
    {
        TransactionCount::create([
            'uuid' => $message->aggregateRootId()->toString(),
            'user_id' => $event->userId,
        ]);
    }

    public function handleMoneyAdded(MoneyAdded $event, Message $message)
    {
        TransactionCount::uuid($message->aggregateRootId()->toString())->incrementCount();
    }

    public function handleMoneySubtracted(MoneySubtracted $event, Message $message)
    {
        TransactionCount::uuid($message->aggregateRootId()->toString())->incrementCount();
    }

    public function handleAccountDeleted(AccountDeleted $event, Message $message)
    {
        TransactionCount::uuid($message->aggregateRootId()->toString())->delete();
    }
}
