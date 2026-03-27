<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Enums\TransactionType;
use App\Enums\TransferStatus;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class TransferService
{
    public function createTransfer(User $user, array $data): Transfer
    {
        return DB::transaction(function () use ($user, $data) {
            $source = Account::lockForUpdate()->findOrFail($data['source_account_id']);
            $destination = Account::lockForUpdate()->findOrFail($data['destination_account_id']);

            if ($source->id === $destination->id) {
                throw new Exception('Le virement vers le même compte est interdit.');
            }

            if ($source->status !== AccountStatus::ACTIVE || $destination->status !== AccountStatus::ACTIVE) {
                return Transfer::create([
                    'source_account_id' => $source->id,
                    'destination_account_id' => $destination->id,
                    'initiated_by_user_id' => $user->id,
                    'amount' => $data['amount'],
                    'status' => TransferStatus::FAILED,
                    'failure_reason' => 'Le compte source ou destination n’est pas actif.',
                ]);
            }

            $isOwner = $source->users()->where('users.id', $user->id)->exists();

            if (! $isOwner) {
                throw new Exception('Vous n’êtes pas autorisé à effectuer un virement depuis ce compte.');
            }

            if ($source->balance < $data['amount']) {
                return Transfer::create([
                    'source_account_id' => $source->id,
                    'destination_account_id' => $destination->id,
                    'initiated_by_user_id' => $user->id,
                    'amount' => $data['amount'],
                    'status' => TransferStatus::FAILED,
                    'failure_reason' => 'Solde insuffisant.',
                ]);
            }

            $transfer = Transfer::create([
                'source_account_id' => $source->id,
                'destination_account_id' => $destination->id,
                'initiated_by_user_id' => $user->id,
                'amount' => $data['amount'],
                'status' => TransferStatus::COMPLETED,
                'executed_at' => now(),
            ]);

            $sourceBalanceBefore = $source->balance;
            $destinationBalanceBefore = $destination->balance;

            $source->balance = $source->balance - $data['amount'];
            $destination->balance = $destination->balance + $data['amount'];

            $source->save();
            $destination->save();

            Transaction::create([
                'account_id' => $source->id,
                'transfer_id' => $transfer->id,
                'type' => TransactionType::TRANSFER_OUT,
                'amount' => $data['amount'],
                'balance_before' => $sourceBalanceBefore,
                'balance_after' => $source->balance,
                'description' => $data['description'] ?? 'Virement sortant',
                'transaction_date' => now(),
                'created_by_user_id' => $user->id,
            ]);

            Transaction::create([
                'account_id' => $destination->id,
                'transfer_id' => $transfer->id,
                'type' => TransactionType::TRANSFER_IN,
                'amount' => $data['amount'],
                'balance_before' => $destinationBalanceBefore,
                'balance_after' => $destination->balance,
                'description' => $data['description'] ?? 'Virement entrant',
                'transaction_date' => now(),
                'created_by_user_id' => $user->id,
            ]);

            return $transfer->load(['sourceAccount', 'destinationAccount', 'initiatedBy']);
        });
    }
}