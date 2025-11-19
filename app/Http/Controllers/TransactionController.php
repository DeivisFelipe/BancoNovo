<?php

namespace App\Http\Controllers;

use App\Events\TransactionReceived;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Fazer um depósito na própria conta
     */
    public function deposit(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        try {
            $transaction = Transaction::create([
                'from_user_id' => null, // Depósito não tem origem
                'to_user_id' => auth()->id(),
                'amount' => $validated['amount'],
                'type' => 'deposit',
                'description' => 'Depósito na conta',
            ]);

            // Carregar relacionamentos
            $transaction->load(['fromUser', 'toUser']);

            // Disparar evento de transação recebida
            event(new TransactionReceived($transaction, auth()->id()));

            return back()->with('success', [
                'title' => 'Depósito realizado!',
                'message' => 'Valor de ' . number_format($validated['amount'], 2, ',', '.') . ' depositado com sucesso.',
            ]);
        } catch (\Exception $e) {
            return back()->with('error', [
                'title' => 'Erro no depósito',
                'message' => 'Não foi possível realizar o depósito. Tente novamente.',
            ]);
        }
    }

    /**
     * Buscar usuários para autocomplete
     */
    public function searchUsers(Request $request)
    {
        $search = $request->input('search', '');
        $currentUserId = auth()->id();

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $users = User::where('id', '!=', $currentUserId)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('account_number', 'like', $search . '%');
            })
            ->select('id', 'name', 'account_number')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'account_number' => $user->account_number,
                    'label' => $user->name . ' - Conta: ' . $user->account_number,
                ];
            });

        return response()->json($users);
    }

    /**
     * Fazer uma transferência para outro usuário
     */
    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $user = auth()->user();

        // Buscar destinatário por ID
        $recipient = User::find($validated['recipient_id']);

        if (!$recipient) {
            return back()->with('error', [
                'title' => 'Destinatário não encontrado',
                'message' => 'Nenhum usuário encontrado.',
            ]);
        }

        if ($recipient->id === $user->id) {
            return back()->with('error', [
                'title' => 'Transferência inválida',
                'message' => 'Você não pode transferir para si mesmo.',
            ]);
        }

        // Verificar saldo
        if ($user->balance < $validated['amount']) {
            $saldoFuturo = $user->getBalanceAfter($validated['amount']);
            return back()->with('error', [
                'title' => 'Saldo insuficiente',
                'message' => sprintf(
                    'Você não tem saldo suficiente. Saldo atual: R$ %s. Após a transferência ficaria: R$ %s',
                    number_format($user->balance, 2, ',', '.'),
                    number_format($saldoFuturo, 2, ',', '.')
                ),
            ]);
        }

        try {
            DB::beginTransaction();

            $transaction = Transaction::create([
                'from_user_id' => $user->id,
                'to_user_id' => $recipient->id,
                'amount' => $validated['amount'],
                'type' => 'transfer',
                'description' => sprintf('Transferência para %s (Conta: %s)', $recipient->name, $recipient->account_number),
            ]);

            // Carregar relacionamentos
            $transaction->load(['fromUser', 'toUser']);

            DB::commit();

            // Disparar evento de transação recebida para o destinatário
            event(new TransactionReceived($transaction, $recipient->id));

            $saldoFuturo = $user->getBalanceAfter($validated['amount']);

            return back()->with('success', [
                'title' => 'Transferência realizada!',
                'message' => sprintf(
                    'R$ %s transferido para %s. Seu novo saldo: R$ %s',
                    number_format($validated['amount'], 2, ',', '.'),
                    $recipient->name,
                    number_format($saldoFuturo, 2, ',', '.')
                ),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', [
                'title' => 'Erro na transferência',
                'message' => 'Não foi possível realizar a transferência. Tente novamente.',
            ]);
        }
    }

    /**
     * Buscar transações do usuário
     */
    public function index()
    {
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
                    'name' => $transaction->type === 'deposit' ? 'Depósito' : $otherUser?->name ?? 'Usuário desconhecido',
                    'account' => $transaction->type === 'deposit' ? '-' : $otherUser?->account_number ?? '-',
                    'amount' => (float) $transaction->amount,
                    'date' => $transaction->created_at->format('d/m/Y H:i'),
                ];
            });

        return response()->json($transactions);
    }
}
