<?php

namespace Tests\Domain\Account\Projectors;

use App\Domain\Account\AccountAggregateRoot;
use App\Domain\Account\AccountId;
use App\Models\TransactionCount;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class TransactionCountProjectorTest extends TestCase
{
    /** @test */
    public function test_transaction_count(): void
    {
        $this->assertDatabaseHas((new TransactionCount())->getTable(), [
            'uuid' => $this->account->uuid,
            'user_id' => $this->user->id,
        ]);

        $transactionCount = TransactionCount::uuid($this->account->uuid);

        $this->assertEquals(0, $transactionCount->count);

        $account = $this->repo->retrieve(AccountId::fromString($this->account->uuid));
        $account->addMoney(10);
        $this->repo->persist($account);

        $transactionCount->refresh();

        $this->assertEquals(1, $transactionCount->count);
    }
}
