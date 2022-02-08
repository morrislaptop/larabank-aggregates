<?php

namespace Tests\Domain\Account\Projectors;

use App\Domain\Account\AccountAggregateRoot;
use App\Domain\Account\AccountId;
use App\Domain\Account\Events\MoneyAdded;
use App\Domain\Account\Projectors\AccountProjector;
use App\Models\Account;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\TestUtilities\MessageConsumerTestCase;
use Tests\Domain\Account\AccountMessageTestCase;
use Tests\Support\LaravelMessageConsumerTestCase;
use Tests\TestCase;

class AccountProjectorTest extends LaravelMessageConsumerTestCase
{
    public function messageConsumer(): AccountProjector {
        return new AccountProjector();
    }

    /** @test */
    public function test_create(): void
    {
        $this->assertDatabaseHas((new Account())->getTable(), [
            'user_id' => $this->user->id,
            'uuid' => $this->account->uuid,
        ]);

        $this->assertTrue($this->account->user->is($this->user));
    }

    /** @test */
    public function test_add_money(): void
    {
        $this->assertEquals(0, $this->account->balance);

        $account = $this->repo->retrieve(AccountId::fromString($this->account->uuid));
        $account->addMoney(10);
        $this->repo->persist($account);

        $this->account->refresh();

        $this->assertEquals(10, $this->account->balance);
    }

    /** @test */
    public function test_add_money_direct(): void
    {
        $this->assertEquals(0, $this->account->balance);

        $this
            ->when(
                new Message(new MoneyAdded(10), [
                    Header::AGGREGATE_ROOT_ID => AccountId::fromString($this->account->uuid)
                ])
            )
            ->then(function () {
                $this->account->refresh();

                $this->assertEquals(10, $this->account->balance);
            });

    }

    /** @test */
    public function test_subtract_money(): void
    {
        $this->assertEquals(0, $this->account->balance);

        $account = $this->repo->retrieve(AccountId::fromString($this->account->uuid));
        $account->subtractMoney(10);
        $this->repo->persist($account);

        $this->account->refresh();

        $this->assertEquals(-10, $this->account->balance);
    }

    /** @test */
    public function test_delete_account(): void
    {
        $account = $this->repo->retrieve(AccountId::fromString($this->account->uuid));
        $account->deleteAccount();
        $this->repo->persist($account);

        $this->assertDatabaseMissing((new Account())->getTable(), [
            'user_id' => $this->user->id,
            'uuid' => $this->account->uuid,
        ]);
    }
}
