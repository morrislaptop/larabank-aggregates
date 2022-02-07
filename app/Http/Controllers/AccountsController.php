<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Domain\Account\AccountAggregateRoot;
use App\Domain\Account\AccountId;
use App\Domain\Account\AccountRepository;
use App\Http\Requests\UpdateAccountRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AccountsController extends Controller
{
    public function __construct(private AccountRepository $repo)
    {

    }

    public function index()
    {
        $accounts = Account::where('user_id', Auth::user()->id)->get();

        return view('accounts.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $aggregateRoot = $this->repo->retrieve(AccountId::fromString(Str::uuid()));
        $aggregateRoot->createAccount($request->name, auth()->user()->id);

        $this->repo->persist($aggregateRoot);

        return back();
    }

    public function update(Account $account, UpdateAccountRequest $request)
    {
        $aggregateRoot = $this->repo->retrieve(AccountId::fromString($account->uuid));

        $request->adding()
            ? $aggregateRoot->addMoney($request->amount)
            : $aggregateRoot->subtractMoney($request->amount);

        $this->repo->persist($aggregateRoot);

        return back();
    }

    public function destroy(Account $account)
    {
        $aggregateRoot = $this->repo->retrieve(AccountId::fromString($account->uuid));
        $aggregateRoot->deleteAccount();
        $this->repo->persist($aggregateRoot);

        return back();
    }
}
