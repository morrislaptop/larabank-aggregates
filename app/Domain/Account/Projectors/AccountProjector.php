<?php

namespace App\Domain\Account\Projectors;

use App\Models\Account;
use App\Domain\Account\Events\AccountCreated;
use App\Domain\Account\Events\AccountDeleted;
use App\Domain\Account\Events\MoneyAdded;
use App\Domain\Account\Events\MoneySubtracted;
use EventSauce\EventSourcing\Message;
use EventSauce\LaravelEventSauce\Consumer;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class AccountProjector extends Consumer
{
    public function handleAccountCreated(AccountCreated $event, Message $message)
    {
        Account::create([
            'uuid' => $message->aggregateRootId()->toString(),
            'name' => $event->name,
            'user_id' => $event->userId,
        ]);
    }

    public function handleMoneyAdded(MoneyAdded $event, Message $message)
    {
        $account = Account::uuid($message->aggregateRootId()->toString());

        $account->balance += $event->amount;

        $account->save();
    }

    public function handleMoneySubtracted(MoneySubtracted $event, Message $message)
    {
        $account = Account::uuid($message->aggregateRootId()->toString());

        $account->balance -= $event->amount;

        $account->save();
    }

    public function handleAccountDeleted(AccountDeleted $event, Message $message)
    {
        Account::uuid($message->aggregateRootId()->toString())->delete();
    }
}
