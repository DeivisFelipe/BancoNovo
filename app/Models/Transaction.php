<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    // Usar $guarded ao invés de $fillable para maior segurança
    protected $guarded = ['id'];

    // Campos que nunca devem ser alterados após criação
    protected $immutable = [
        'from_user_id',
        'to_user_id',
        'amount',
        'type',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    /**
     * Usuário que enviou (pode ser null para depósitos)
     */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * Usuário que recebeu
     */
    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
