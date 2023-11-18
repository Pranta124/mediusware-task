<?php
namespace App\Services;

use App\Transaction;
use App\User;
use Carbon\Carbon;

class TransactionService
{
    public function getUserBalanceAndTransactions($userId)
    {
        $user = User::findOrFail($userId);

        $transactions = $user->transactions;
        $currentBalance = $user->balance;

        $totalTransactionBalance = $transactions->sum('amount') - $transactions->sum('fee');

        return [
            'user' => [
                'name' => $user->name,
                'current_balance' => $currentBalance,
                'transaction_balance' => $totalTransactionBalance,
            ]
        ];
    }
    public function getDepositedTransactions()
    {
        return Transaction::where('transaction_type', Transaction::TRANSACTION_TYPE_DEPOSIT)
            ->select(['transaction_type', 'amount', 'fee', 'date'])
            ->get();
    }
    public function depositAmount($userId, $amount)
    {
        $user = User::findOrFail($userId);

        // Update the user's balance by adding the deposited amount
        $user->balance += $amount;
        $user->save();

        // Record the deposit transaction
        Transaction::create([
            'user_id' => $user->id,
            'account_type' => $user->account_type,
            'amount' => $amount,
            'fee' => 0,
            'date' => now()->toDateString(),
            'transaction_type' => 'deposit', // Include the transaction type
        ]);

        return $user;
    }
    public function getWithdrawalTransactions()
    {
        return Transaction::where('transaction_type', Transaction::TRANSACTION_TYPE_WITHDRAWN)
            ->select(['transaction_type', 'amount', 'fee', 'date'])
            ->get();
    }

    public function processWithdrawal($userId, $amount)
    {
        $user = User::findOrFail($userId);
        $accountType = $user->account_type;
         // Get the total withdrawal amount for the user
         $totalWithdrawal = Transaction::where('user_id', $user->id)
         ->where('transaction_type', Transaction::TRANSACTION_TYPE_WITHDRAWN)
         ->sum('amount');

     // Apply withdrawal rates based on the user's account type
     if ($accountType === User::ACCOUNT_TYPE_BUSINESS) {
         if ($totalWithdrawal >= 50000) {
             $withdrawalRate = 0.015; // Decrease withdrawal fee to 0.015% for Business accounts after 50K total withdrawal
         } else {
             $withdrawalRate = 0.025; // Default withdrawal rate for Business accounts
         }
     } else {
         $withdrawalRate = 0.015; // Default withdrawal rate for non-Business accounts
     }
     // Check for free withdrawal conditions for Individual accounts
     if ($accountType === User::ACCOUNT_TYPE_INDIVIDUAL) {
         $today = Carbon::today();

         // Each Friday withdrawal is free
         if ($today->isFriday()) {
             $withdrawalRate = 0;
         }
         // Check the first 1K withdrawal per transaction
         if ($amount > 1000) {
             $withdrawn = 1000;
             $remaining = $amount - $withdrawn;
             $withdrawalRate += $remaining * $withdrawalRate;
             $amount = $withdrawn;
         }

         // Check the first 5K withdrawal each month
         $monthWithdrawals = Transaction::where('user_id', $user->id)
            ->where('transaction_type', Transaction::TRANSACTION_TYPE_WITHDRAWN)
             ->whereYear('date', $today->year)
             ->whereMonth('date', $today->month)
             ->sum('amount');

         if ($monthWithdrawals < Transaction::FIVE_THOUSANDS_TAKA) {
             $remainingMonthlyLimit = Transaction::FIVE_THOUSANDS_TAKA - $monthWithdrawals;
             if ($amount > $remainingMonthlyLimit) {
                 $withdrawalRate += ($amount - $remainingMonthlyLimit) * $withdrawalRate;
                 $amount = $remainingMonthlyLimit;
             }
         }
     }

     // Check for fee adjustment for Business accounts after 50K total withdrawal
     if ($accountType === User::ACCOUNT_TYPE_BUSINESS && $totalWithdrawal >= Transaction::FIFTY_THOUSANDS_TAKA) {
         $withdrawalRate = 0.015;
     }

     // Calculate the withdrawal fee
     $withdrawalFee = $amount * $withdrawalRate;

     // Update the user's balance by deducting the withdrawn amount and fee
     $user->balance -= ($amount + $withdrawalFee);
     $user->save();

     // Record the withdrawal transaction
     Transaction::create([
         'user_id' => $user->id,
         'account_type' => $accountType,
         'amount' => $amount,
         'fee' => $withdrawalFee,
         'transaction_type' => 'withdrawn',
         'date' => now()->toDateString(),
     ]);
     return $user;
    }
}
