<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Balance extends Model
{
    protected $fillable = ['user_id', 'amount'];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public static function getCurrentBalance($userId)
    {
        return static::lockForUpdate()->where('user_id', $userId)->first()->amount ?? 0;
    }

    public static function updateBalance($userId, $amount, $type)
    {
        return DB::transaction(function () use ($userId, $amount, $type) {
            $balance = static::lockForUpdate()->where('user_id', $userId)->first();

            if (!$balance) {
                $balance = static::create(['user_id' => $userId, 'amount' => 1000000.00]);
            }

            $previousBalance = $balance->amount;

            if ($type === 'credit') {
                $newBalance = $previousBalance + $amount;
            } else {
                $newBalance = $previousBalance - $amount;

                if ($newBalance < 0) {
                    throw new \Exception('Insufficient balance');
                }
            }

            $balance->update(['amount' => $newBalance]);

            return [
                'previous_balance' => $previousBalance,
                'current_balance' => $newBalance,
            ];
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
