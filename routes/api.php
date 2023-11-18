<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    // return $request->user();
});
Route::group(['middleware' =>'auth:sanctum'],function(){
    Route::get('/users/{userId}/transactions', [TransactionController::class, 'showUserBalanceAndTransactions']);
    Route::get('/user-deposit-data', [TransactionController::class, 'showDepositedTransactions']);
    Route::post('/deposit', [TransactionController::class, 'deposit']);
    Route::get('/user-withdrawals-data', [TransactionController::class, 'showWithdrawalTransactions']);
    Route::post('/withdrawals', [TransactionController::class, 'withdrawal']);
});

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
// Route::get('/users/{userId}/transactions', [TransactionController::class, 'showUserBalanceAndTransactions']);
