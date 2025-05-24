<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['credit', 'debit']);
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->decimal('previous_balance', 15, 2)->nullable();
            $table->decimal('current_balance', 15, 2)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('transaction_id');
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
