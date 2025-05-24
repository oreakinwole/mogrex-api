<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function balance()
    {
        return $this->hasOne(Balance::class);
    }

    public function getCurrentBalance()
    {
        return $this->balance ? $this->balance->amount : 0;
    }

    public function createInitialBalance()
    {
        if (!$this->balance) {
            $this->balance()->create(['amount' => 1000000.00]);
        }
    }
}
