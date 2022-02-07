<?php

namespace App\Domain\Account;

use App\Domain\Account\Events\AccountCreated;
use App\Domain\Account\Events\AccountDeleted;
use App\Domain\Account\Events\AccountLimitHit;
use App\Domain\Account\Events\MoneyAdded;
use App\Domain\Account\Events\MoneySubtracted;
use App\Domain\Account\Events\MoreMoneyNeeded;
use App\Domain\Account\Exceptions\CouldNotSubtractMoney;
use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootBehaviour;
use Illuminate\Support\Str;

class AccountAggregateRoot implements AggregateRoot
{
    use AggregateRootBehaviour;

    protected int $balance = 0;

    protected int $accountLimit = -5000;

    protected int $accountLimitHitInARow = 0;

    public static function initiate(AccountId $id): self
    {
        $account = new static($id);

        return $account;
    }

    public function createAccount(string $name, string $userId): self
    {
        $this->recordThat(new AccountCreated($name, $userId));

        return $this;
    }

    private function applyAccountCreated(AccountCreated $event) {}
    private function applyMoreMoneyNeeded(MoreMoneyNeeded $event) {}
    private function applyAccountDeleted(AccountDeleted $event) {}

    public function addMoney(int $amount)
    {
        $this->recordThat(new MoneyAdded($amount));

        return $this;
    }

    public function applyMoneyAdded(MoneyAdded $event)
    {
        $this->accountLimitHitInARow = 0;

        $this->balance += $event->amount;
    }

    public function subtractMoney(int $amount)
    {
        if (!$this->hasSufficientFundsToSubtractAmount($amount)) {
            $this->recordThat(new AccountLimitHit());

            if ($this->needsMoreMoney()) {
                $this->recordThat(new MoreMoneyNeeded());
            }

            throw CouldNotSubtractMoney::notEnoughFunds($amount);
        }

        $this->recordThat(new MoneySubtracted($amount));
    }

    public function applyMoneySubtracted(MoneySubtracted $event)
    {
        $this->balance -= $event->amount;

        $this->accountLimitHitInARow = 0;
    }

    public function deleteAccount()
    {
        $this->recordThat(new AccountDeleted());

        return $this;
    }

    public function applyAccountLimitHit(AccountLimitHit $accountLimitHit)
    {
        $this->accountLimitHitInARow++;
    }

    private function hasSufficientFundsToSubtractAmount(int $amount): bool
    {
        return $this->balance - $amount >= $this->accountLimit;
    }

    private function needsMoreMoney()
    {
        return $this->accountLimitHitInARow >= 3;
    }
}
