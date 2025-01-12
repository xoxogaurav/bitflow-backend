<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DepositController;
use App\Http\Controllers\Api\DepositMethodController;
use App\Http\Controllers\Api\WithdrawalController;
use App\Http\Controllers\Api\CryptocurrencyController;
use App\Http\Controllers\Api\FlexibleInvestmentController;
use App\Http\Controllers\Api\CryptoInvestmentController;
use App\Http\Controllers\Api\PortfolioController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\ReferralController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/deposit-methods', [DepositMethodController::class, 'index']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // User routes
    Route::get('/profile', [UserController::class, 'show']);
    Route::put('/profile', [UserController::class, 'update']);
    
    // Deposit routes
    Route::post('/deposits', [DepositController::class, 'store']);
    Route::get('/deposits/my', [DepositController::class, 'userDeposits']);
    
    // Withdrawal routes
    Route::post('/withdrawals', [WithdrawalController::class, 'store']);
    Route::get('/withdrawals/my', [WithdrawalController::class, 'userWithdrawals']);
    
    // Outside admin middleware (public route)
    Route::get('cryptocurrencies', [CryptocurrencyController::class, 'index']);
    
    // Crypto Investment routes
    Route::post('/crypto/invest', [CryptoInvestmentController::class, 'invest']);
    Route::post('/crypto/withdraw/{investment}', [CryptoInvestmentController::class, 'withdraw']);
    Route::get('/crypto/investments', [CryptoInvestmentController::class, 'userInvestments']);

    
    
     // Flexible Investment routes
    Route::post('/flexible/deposit', [FlexibleInvestmentController::class, 'deposit']);
    Route::post('/flexible/withdraw', [FlexibleInvestmentController::class, 'withdraw']);
    Route::get('/flexible/balance', [FlexibleInvestmentController::class, 'getBalance']);
    Route::get('/flexible/transactions', [FlexibleInvestmentController::class, 'getTransactions']);
    
    
    Route::get('/portfolio', [PortfolioController::class, 'index']);

 // Transactions route
    Route::get('/transactions', [TransactionController::class, 'index']);
    
      // Referral routes
    Route::get('/referrals', [ReferralController::class, 'getReferralInfo']);

    // Admin routes
    Route::middleware('admin')->group(function () {
        // User management
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        
        // Admin management
        Route::post('/admins', [AdminController::class, 'createAdmin']);
        Route::get('/admins', [AdminController::class, 'listAdmins']);
        
        // Deposit management
        Route::get('/deposits', [DepositController::class, 'index']);
        Route::put('/deposits/{depositRequest}', [DepositController::class, 'processRequest']);
        Route::resource('deposit-methods', DepositMethodController::class)->except('index');
        
        // Withdrawal management
        Route::get('/withdrawals', [WithdrawalController::class, 'index']);
        Route::put('/withdrawals/{withdrawalRequest}', [WithdrawalController::class, 'processRequest']);
        
        // Inside the admin middleware group
        Route::apiResource('cryptocurrencies', CryptocurrencyController::class)->except(['index']);
        Route::get('admin/cryptocurrencies', [CryptocurrencyController::class, 'listAll']);
    });
});