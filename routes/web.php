<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\CourtController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MinigameController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\RememberCurrentMinigame;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
});

Route::get('/auth/{provider}', [AuthController::class, 'redirect'])
    ->whereIn('provider', ['google', 'facebook'])
    ->middleware('throttle:social-login')
    ->name('auth.redirect');
Route::get('/auth/{provider}/callback', [AuthController::class, 'callback'])
    ->whereIn('provider', ['google', 'facebook'])
    ->middleware('throttle:social-login')
    ->name('auth.callback');

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'club.active'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/guide', [DashboardController::class, 'guide'])->name('guide');
    Route::delete('/current-minigame', [DashboardController::class, 'clear'])->name('minigames.clear');

    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::get('/members/create', [MemberController::class, 'create'])->name('members.create');
    Route::post('/members', [MemberController::class, 'store'])->name('members.store');
    Route::get('/members/{member}', [MemberController::class, 'show'])->name('members.show');
    Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');

    Route::get('/courts', [CourtController::class, 'index'])->name('courts.index');
    Route::post('/courts', [CourtController::class, 'store'])->name('courts.store');
    Route::patch('/courts/{court}', [CourtController::class, 'update'])->name('courts.update');

    Route::get('/settings', [ClubController::class, 'edit'])->name('settings');
    Route::put('/settings', [ClubController::class, 'update'])->name('settings.update');
    Route::patch('/settings/users/{user}', [UserController::class, 'update'])->name('users.update');

    Route::get('/minigames', [MinigameController::class, 'index'])->name('minigames.index');
    Route::get('/minigames/create', [MinigameController::class, 'create'])->name('minigames.create');
    Route::post('/minigames', [MinigameController::class, 'store'])->name('minigames.store');

    Route::middleware(RememberCurrentMinigame::class)->group(function () {
        Route::post('/minigames/{minigame}/select', [MinigameController::class, 'select'])->name('minigames.select');
        Route::get('/minigames/{minigame}', [MinigameController::class, 'show'])->name('minigames.show');
        Route::put('/minigames/{minigame}', [MinigameController::class, 'update'])->name('minigames.update');
        Route::get('/minigames/{minigame}/roster', [MinigameController::class, 'editRoster'])->name('minigames.roster');
        Route::put('/minigames/{minigame}/roster', [MinigameController::class, 'roster'])->name('minigames.roster.update');
        Route::get('/minigames/{minigame}/rules', [MinigameController::class, 'editRules'])->name('minigames.rules');
        Route::patch('/minigames/{minigame}/rules', [MinigameController::class, 'rules'])->name('minigames.rules.update');
        Route::post('/minigames/{minigame}/activate', [MinigameController::class, 'activate'])->name('minigames.activate');
        Route::post('/minigames/{minigame}/close', [MinigameController::class, 'close'])->name('minigames.close');

        Route::get('/minigames/{minigame}/matches', [MatchController::class, 'index'])->name('matches.index');
        Route::get('/minigames/{minigame}/matches/create', [MatchController::class, 'create'])->name('matches.create');
        Route::post('/minigames/{minigame}/matches', [MatchController::class, 'store'])->name('matches.store');
        Route::get('/minigames/{minigame}/matches/{match}', [MatchController::class, 'show'])->name('matches.show');
        Route::put('/minigames/{minigame}/matches/{match}', [MatchController::class, 'update'])->name('matches.update');
        Route::post('/minigames/{minigame}/matches/{match}/start', [MatchController::class, 'start'])->name('matches.start');
        Route::post('/minigames/{minigame}/matches/{match}/cancel', [MatchController::class, 'cancel'])->name('matches.cancel');
        Route::get('/minigames/{minigame}/matches/{match}/result', [MatchController::class, 'editResult'])->name('matches.result');
        Route::post('/minigames/{minigame}/matches/{match}/result', [MatchController::class, 'result'])->name('matches.result.store');

        Route::get('/minigames/{minigame}/rankings', [RankingController::class, 'index'])->name('rankings.index');
    });
});
