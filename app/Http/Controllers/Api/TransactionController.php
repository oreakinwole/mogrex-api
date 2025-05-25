<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTransactionRequest;
use App\Jobs\ProcessTransactionJob;
use App\Models\Balance;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function store(CreateTransactionRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $currentBalance = Balance::getCurrentBalance($user->id);

            if ($request->type === 'debit' && $currentBalance < $request->amount) {
                return response()->json([
                    'error' => 'Insufficient balance',
                    'current_balance' => number_format($currentBalance, 2)
                ], 400);
            }

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'type' => $request->type,
                'status' => 'pending',
            ]);

            ProcessTransactionJob::dispatch($transaction);

            $projectedBalance = $request->type === 'credit'
                ? $currentBalance + $request->amount
                : $currentBalance - $request->amount;

            DB::commit();

            return response()->json([
                'transaction_id' => $transaction->transaction_id,
                'message' => 'Transaction queued for processing',
                'previous_balance' => number_format($currentBalance, 2),
                'current_balance' => number_format($projectedBalance, 2)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to create transaction',
                'message' => 'Please try again later'
            ], 500);
        }
    }

    public function show($transactionId, Request $request): JsonResponse
    {
        $transaction = Transaction::where('transaction_id', $transactionId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$transaction) {
            return response()->json([
                'error' => 'Transaction not found'
            ], 404);
        }

        return response()->json([
            'transaction_id' => $transaction->transaction_id,
            'amount' => number_format($transaction->amount, 2),
            'type' => $transaction->type,
            'status' => $transaction->status,
            'previous_balance' => $transaction->previous_balance ? number_format($transaction->previous_balance, 2) : null,
            'current_balance' => $transaction->current_balance ? number_format($transaction->current_balance, 2) : null,
            'created_at' => $transaction->created_at,
            'updated_at' => $transaction->updated_at,
        ]);
    }

    public function getBalance(Request $request): JsonResponse
    {
        $balance = Balance::getCurrentBalance($request->user()->id);

        return response()->json([
            'current_balance' => number_format($balance, 2)
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $transactions = Transaction::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'transactions' => $transactions->items(),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ]
        ]);
    }
}
