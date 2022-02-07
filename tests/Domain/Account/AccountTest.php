<?php

namespace Tests\Domain\Account;

use App\Domain\Account\AccountAggregateRoot;
use App\Domain\Account\Events\AccountCreated;
use App\Domain\Account\Events\AccountDeleted;
use App\Domain\Account\Events\AccountLimitHit;
use App\Domain\Account\Events\MoneyAdded;
use App\Domain\Account\Events\MoneySubtracted;
use App\Domain\Account\Events\MoreMoneyNeeded;
use App\Domain\Account\Exceptions\CouldNotSubtractMoney;
use App\Models\Account;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class AccountTest extends AccountTestCase
{
    private const ACCOUNT_UUID = 'accounts-uuid';

    private const ACCOUNT_NAME = 'fake-account';

    /** @test */
    public function can_create(): void
    {
        $this
            ->when(function (AccountAggregateRoot $account) {
                $account->createAccount('Craig', '123');
            })
            ->then(new AccountCreated('Craig', '123'));
    }

    /** @test */
    public function can_add_money(): void
    {
        $this
            ->given(
                new AccountCreated(self::ACCOUNT_NAME, '123')
            )
            ->when(function (AccountAggregateRoot $accountAggregateRoot): void {
                $accountAggregateRoot->addMoney(10);
            })
            ->then(
                new MoneyAdded(10)
            );
    }

    /** @test */
    public function can_subtract_money(): void
    {
        $this
            ->given(
                new AccountCreated(self::ACCOUNT_NAME, '123'),
                new MoneyAdded(10)
            )
            ->when(function (AccountAggregateRoot $accountAggregateRoot): void {
                $accountAggregateRoot->subtractMoney(10);
            })
            ->then(
                new MoneySubtracted(10),
            );
            // @todo how to test this?!
            // ->assertNotRecorded(AccountLimitHit::class);
    }

    /** @test */
    public function cannot_subtract_money_when_money_below_account_limit(): void
    {
        $this
            ->given(
                new AccountCreated(self::ACCOUNT_NAME, '123'),
                new MoneySubtracted(5000)
            )
            ->when(function (AccountAggregateRoot $accountAggregateRoot): void {
                $accountAggregateRoot->subtractMoney(1);
            })
            ->expectToFail(
                CouldNotSubtractMoney::notEnoughFunds(1),
            )
            ->then(
                new AccountLimitHit()
            )
            ;
            // @todo how to test this?!
            // ->assertNotRecorded(MoneySubtracted::class);

    }

    /** @test */
    public function record_need_more_money_when_limit_hit_equal_three_times(): void
    {
        $this
            ->given(
                new AccountCreated(self::ACCOUNT_NAME, '123'),
                new MoneySubtracted(5000),
                new AccountLimitHit(),
                new AccountLimitHit(),
            )
            ->when(function (AccountAggregateRoot $accountAggregateRoot): void {
                $accountAggregateRoot->subtractMoney(1);
            })
            ->expectToFail(CouldNotSubtractMoney::notEnoughFunds(1))
            ->then(
                new AccountLimitHit(),
                new MoreMoneyNeeded(),
            );
    }

    /** @test */
    public function can_delete_account(): void
    {
        $this
            ->given(new AccountCreated(self::ACCOUNT_NAME, '123'))
            ->when(function (AccountAggregateRoot $accountAggregateRoot): void {
                $accountAggregateRoot->deleteAccount();
            })
            ->then(
                new AccountDeleted()
            );
    }

}
