<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'amount',
        'type',
        'status',
        'previous_balance',
        'current_balance',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'previous_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_id)) {
                $transaction->transaction_id = 'TXN-' . Str::upper(Str::random(12));
            }
        });
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function markAsProcessed($previousBalance, $currentBalance)
    {
        $this->update([
            'status' => 'processed',
            'previous_balance' => $previousBalance,
            'current_balance' => $currentBalance,
        ]);
    }

    public function markAsFailed()
    {
        $this->update(['status' => 'failed']);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
