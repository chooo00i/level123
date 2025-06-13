<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LevelLogController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\UserAccountController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\HabitController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // 로그인
    Route::get('login', [AuthController::class, 'create'])->name('login');
    Route::post('login', [AuthController::class, 'store'])->name('login.store');

    // 계정 생성
    Route::resource('user-account', UserAccountController::class)->only(['create', 'store']);
});

// 로그아웃
Route::delete('logout', [AuthController::class, 'destroy'])->name('logout');


// 로그인 필요
Route::middleware('auth')->group(function () {
    // 홈(습관 기록)
    Route::resource('log', LogController::class)->only(['store', 'destroy']);
    Route::get('home/{log?}', [LogController::class, 'index'])->name('home');
    Route::get('/', [LogController::class, 'index'])->name('root');
    Route::get('log/history', [LogController::class, 'history'])->name('log.history');
    
    // Habit
    Route::resource('habit', HabitController::class)->only(['index', 'create', 'store']);
    Route::get('habit/edit/{habit}/{log_id}', [HabitController::class, 'edit'])->name('habit.edit');
    Route::get('habit/copy/{habit}', [HabitController::class, 'copy'])->name('habit.copy');
    Route::post('habit', [HabitController::class, 'store'])->name('habit.store');
    Route::put('habit/{habit}', [HabitController::class, 'update'])->name('habit.update');
    Route::post('/habit/{habit}/round', [HabitController::class, 'startNewRound'])->name('habit.startNewRound');
    
    // LevelLog
    Route::patch('level-log/batch-check', [LevelLogController::class, 'batchCheck'])->name('level_log.batch-check');
    Route::get('level-log/{log_id}/{date}', [LevelLogController::class, 'byDate'])->name('level_log.by_date');

    // 마이페이지
    Route::get('user-account/{user}', [UserAccountController::class, 'edit'])->name('user-account.edit');
    Route::put('user-account', [UserAccountController::class, 'update'])->name('user-account.update');
});
