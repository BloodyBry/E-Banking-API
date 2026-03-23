<?php

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();

            $table->string('account_number')->unique();

            $table->enum('type', [
                AccountType::COURANT->value,
                AccountType::EPARGNE->value,
                AccountType::MINEUR->value,
            ]);

            $table->enum('status', [
                AccountStatus::ACTIVE->value,
                AccountStatus::BLOCKED->value,
                AccountStatus::CLOSED->value,
            ])->default(AccountStatus::ACTIVE->value);

            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('overdraft_limit', 15, 2)->default(0);
            $table->decimal('annual_interest_rate', 5, 2)->default(0);
            $table->decimal('monthly_fee', 15, 2)->default(0);

            $table->text('block_reason')->nullable();
            $table->timestamp('closure_requested_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};