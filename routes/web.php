<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Models\Transaction;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', fn() => inertia('Guest/Login'))->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', fn() => inertia('Guest/Register'))->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();

        $transactions = Transaction::where('from_user_id', $user->id)
            ->orWhere('to_user_id', $user->id)
            ->with(['fromUser', 'toUser'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($transaction) use ($user) {
                $isReceived = $transaction->to_user_id === $user->id;
                $otherUser = $isReceived ? $transaction->fromUser : $transaction->toUser;

                return [
                    'id' => $transaction->id,
                    'type' => $transaction->type === 'deposit' ? 'received' : ($isReceived ? 'received' : 'sent'),
                    'name' => $transaction->type === 'deposit' ? 'Depósito' : ($otherUser?->name ?? 'Usuário desconhecido'),
                    'account' => $transaction->type === 'deposit' ? '-' : ($otherUser?->account_number ?? '-'),
                    'amount' => (float) $transaction->amount,
                    'date' => $transaction->created_at->format('d/m/Y H:i'),
                ];
            });

        return inertia('Dashboard', [
            'user' => $user,
            'balance' => $user->balance,
            'transactions' => $transactions,
        ]);
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::post('/transfer', [TransactionController::class, 'transfer'])->name('transfer');
    Route::post('/deposit', [TransactionController::class, 'deposit'])->name('deposit');
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
    Route::get('/search-users', [TransactionController::class, 'searchUsers'])->name('search.users');
});

// Fallback para rotas inexistentes
Route::fallback(function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return redirect('/login');
});
