<?php

use App\Enums\TransferStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('source_account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('destination_account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('initiated_by_user_id')->constrained('users')->cascadeOnDelete();

            $table->decimal('amount', 15, 2);

            $table->enum('status', [
                TransferStatus::PENDING->value,
                TransferStatus::COMPLETED->value,
                TransferStatus::FAILED->value,
            ])->default(TransferStatus::PENDING->value);

            $table->text('failure_reason')->nullable();
            $table->timestamp('executed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};