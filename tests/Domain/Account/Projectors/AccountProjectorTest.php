<?php

namespace Tests\Domain\Account\Projectors;

use App\Domain\Account\AccountAggregateRoot;
use App\Domain\Account\AccountId;
use App\Domain\Account\Events\MoneyAdded;
use App\Domain\Account\Projectors\AccountProjector;
use App\Models\Account;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use Tests\TestCase;

class AccountProjectorTest extends TestCase
{
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

        $event = new MoneyAdded(10);
        $message = new Message($event, [
            Header::AGGREGATE_ROOT_ID => AccountId::fromString($this->account->uuid),
        ]);
        $projector = new AccountProjector();
        $projector->handleMoneyAdded($event, $message);

        $this->account->refresh();

        $this->assertEquals(10, $this->account->balance);
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
