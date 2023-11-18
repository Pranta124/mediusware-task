<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepositeRequest;
use App\Http\Requests\WithdrawalRequest;
use App\Transaction;
use Illuminate\Http\Request;
use App\User;
use Carbon\Carbon;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function showUserBalanceAndTransactions($userId)
    {
        $userData = $this->transactionService->getUserBalanceAndTransactions($userId);

        return response()->json($userData, 200);
    }

    public function showDepositedTransactions()
    {

        $depositedTransactions = $this->transactionService->getDepositedTransactions();

        return response()->json([
            'deposited_transactions' => $depositedTransactions,
        ], 200);
    }
    public function deposit(DepositeRequest $request)
    {
        $userId = $request->user_id;
        $amount = $request->amount;

        $user = $this->transactionService->depositAmount($userId, $amount);

        return response()->json([
            'message' => 'Deposit successful',
            'user' => $user, // Optional: Return updated user details
        ], 200);
    }
    public function showWithdrawalTransactions()
    {
        $withdrawalTransactions = $this->transactionService->getWithdrawalTransactions();

        return response()->json([
            'withdrawal_transactions' => $withdrawalTransactions,
        ], 200);
    }

    public function withdrawal(WithdrawalRequest $request)
    {
        $userId = $request->user_id;
        $amount = $request->amount;

        $user = $this->transactionService->processWithdrawal($userId, $amount);

        return response()->json([
            'message' => 'Withdrawal successful',
            'user' => $user,
        ], 200);
    }
}
