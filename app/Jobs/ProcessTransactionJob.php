<?php
// app/Jobs/ProcessTransactionJob.php

namespace App\Jobs;

use App\Models\Balance;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $transaction;
    public $tries = 3;
    public $timeout = 60;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function handle()
    {
        try {
            $transaction = Transaction::lockForUpdate()
                ->where('id', $this->transaction->id)
                ->where('status', 'pending')
                ->first();

            if (!$transaction) {
                Log::warning("Transaction {$this->transaction->transaction_id} not found or already processed");
                return;
            }

            $balanceData = Balance::updateBalance(
                $transaction->user_id,
                $transaction->amount,
                $transaction->type
            );

            $transaction->markAsProcessed(
                $balanceData['previous_balance'],
                $balanceData['current_balance']
            );

            Log::info("Transaction {$transaction->transaction_id} processed successfully");

        } catch (\Exception $e) {
            Log::error("Failed to process transaction {$this->transaction->transaction_id}: " . $e->getMessage());

            $this->transaction->markAsFailed();

            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("Transaction job failed permanently: {$this->transaction->transaction_id}", [
            'error' => $exception->getMessage()
        ]);

        $this->transaction->markAsFailed();
    }
}
