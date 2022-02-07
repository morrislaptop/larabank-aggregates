<?php

namespace Tests;

use App\Domain\Account\AccountAggregateRoot;
use App\Domain\Account\AccountId;
use App\Domain\Account\AccountRepository;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;
use Throwable;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected User $user;

    protected Account $account;

    protected AccountRepository $repo;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->repo = app(AccountRepository::class);

        $this->account = $this->createAccount();
    }

    protected function assertExceptionThrown(callable $callable, string $expectedExceptionClass): void
    {
        try {
            $callable();

            $this->assertTrue(false, "Expected exception `{$expectedExceptionClass}` was not thrown.");
        } catch (Throwable $exception) {
            if (! $exception instanceof $expectedExceptionClass) {
                throw $exception;
            }
            $this->assertInstanceOf($expectedExceptionClass, $exception);
        }
    }

    protected function createAccount(): Account
    {
        $uuid = Str::uuid();

        $aggregate = $this->repo->retrieve(AccountId::fromString($uuid));
        $aggregate->createAccount('account', $this->user->id);
        $this->repo->persist($aggregate);

        return Account::uuid($aggregate->aggregateRootId()->toString());
    }
}
