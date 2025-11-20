<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Models\Transaction;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', fn() => inertia('Guest/Login'))->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1'); // 5 tentativas por minuto

    Route::get('/register', fn() => inertia('Guest/Register'))->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,10'); // 3 registros a cada 10 minutos
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

    Route::post('/transfer', [TransactionController::class, 'transfer'])->middleware('throttle:10,1')->name('transfer'); // 10 transferências por minuto
    Route::post('/deposit', [TransactionController::class, 'deposit'])->middleware('throttle:5,1')->name('deposit'); // 5 depósitos por minuto
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
