<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transaction;
    public $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(Transaction $transaction, int $userId)
    {
        $this->transaction = $transaction;
        $this->userId = $userId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'transaction.received';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $fromUser = $this->transaction->fromUser;

        return [
            'id' => $this->transaction->id,
            'type' => $this->transaction->type,
            'amount' => (float) $this->transaction->amount,
            'from_name' => $fromUser ? $fromUser->name : 'Sistema',
            'from_account' => $fromUser ? $fromUser->account_number : '-',
            'date' => $this->transaction->created_at->format('d/m/Y H:i'),
        ];
    }
}
