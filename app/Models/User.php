<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'account_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Boot method to generate unique account number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->account_number)) {
                $user->account_number = self::generateUniqueAccountNumber();
            }
        });
    }

    /**
     * Generate a unique 6-digit account number
     */
    private static function generateUniqueAccountNumber(): string
    {
        do {
            $accountNumber = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }

    /**
     * Transações enviadas
     */
    public function sentTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'from_user_id');
    }

    /**
     * Transações recebidas
     */
    public function receivedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'to_user_id');
    }

    /**
     * Todas as transações (enviadas e recebidas)
     */
    public function transactions()
    {
        return Transaction::where('from_user_id', $this->id)
            ->orWhere('to_user_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Calcula o saldo baseado nas transações
     */
    public function getBalanceAttribute(): float
    {
        $received = Transaction::where('to_user_id', $this->id)->sum('amount');
        $sent = Transaction::where('from_user_id', $this->id)->sum('amount');

        return (float) ($received - $sent);
    }

    /**
     * Calcula o saldo futuro após uma transação
     */
    public function getBalanceAfter(float $amount): float
    {
        return $this->balance - $amount;
    }
}
