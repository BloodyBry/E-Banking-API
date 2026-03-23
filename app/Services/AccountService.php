<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AccountService
{
    public function createAccount(User $user, array $data): Account
    {
        return DB::transaction(function () use ($user, $data) {
            $type = AccountType::from($data['type']);

            if ($type === AccountType::MINEUR) {
                throw new \Exception('La création d’un compte mineur sera gérée dans une étape dédiée.');
            }

            $account = Account::create([
                'account_number' => $this->generateAccountNumber(),
                'type' => $type,
                'status' => AccountStatus::ACTIVE,
                'balance' => 0,
                'overdraft_limit' => $type === AccountType::COURANT
                    ? ($data['overdraft_limit'] ?? 0)
                    : 0,
                'annual_interest_rate' => $type === AccountType::EPARGNE
                    ? ($data['annual_interest_rate'] ?? 0)
                    : 0,
                'monthly_fee' => $type === AccountType::COURANT
                    ? ($data['monthly_fee'] ?? 0)
                    : 0,
            ]);

            $account->users()->attach($user->id, [
                'is_primary_holder' => true,
                'accepted_closure' => false,
            ]);

            return $account->load('users');
        });
    }

    private function generateAccountNumber(): string
    {
        do {
            $number = 'ALM-' . now()->format('Ymd') . '-' . random_int(100000, 999999);
        } while (Account::where('account_number', $number)->exists());

        return $number;
    }
}