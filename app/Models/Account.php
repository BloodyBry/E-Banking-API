<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_number',
        'type',
        'status',
        'balance',
        'overdraft_limit',
        'annual_interest_rate',
        'monthly_fee',
        'block_reason',
        'closure_requested_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => AccountType::class,
            'status' => AccountStatus::class,
            'balance' => 'decimal:2',
            'overdraft_limit' => 'decimal:2',
            'annual_interest_rate' => 'decimal:2',
            'monthly_fee' => 'decimal:2',
            'closure_requested_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('is_primary_holder', 'accepted_closure')
            ->withTimestamps();
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function outgoingTransfers()
    {
        return $this->hasMany(Transfer::class, 'source_account_id');
    }

    public function incomingTransfers()
    {
        return $this->hasMany(Transfer::class, 'destination_account_id');
    }
}