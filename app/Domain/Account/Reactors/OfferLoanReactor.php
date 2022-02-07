<?php

namespace App\Domain\Account\Reactors;

use App\Domain\Account\Events\MoreMoneyNeeded;
use App\Mail\LoanProposalMail;
use App\Models\Account;
use EventSauce\EventSourcing\Message;
use EventSauce\LaravelEventSauce\Consumer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;

class OfferLoanReactor extends Consumer
{
    public function handleMoreMoneyNeeded(MoreMoneyNeeded $event, Message $message)
    {
        $account = Account::where('uuid', $message->aggregateRootId()->toString())->first();

        Mail::to($account->user)->send(new LoanProposalMail());
    }
}
