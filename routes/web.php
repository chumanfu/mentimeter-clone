<?php

use App\Http\Controllers\PresentationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('landing');

Route::get('/join', [SessionController::class, 'joinForm'])->name('join.form');
Route::post('/join', [SessionController::class, 'join'])->name('join.submit');
Route::get('/session/{joinCode}', [SessionController::class, 'show'])->name('session.show');
Route::post('/session/{joinCode}/vote', [VoteController::class, 'submit'])->name('session.vote');

Route::get('/dashboard', [PresentationController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/presentations/create', [PresentationController::class, 'create'])->name('presentations.create');
    Route::post('/presentations', [PresentationController::class, 'store'])->name('presentations.store');

    Route::get('/host/{token}', [PresentationController::class, 'host'])->name('host.show');
    Route::get('/host/{token}/present', [PresentationController::class, 'present'])->name('host.present');
    Route::post('/host/{token}/start', [PresentationController::class, 'start'])->name('host.start');
    Route::post('/host/{token}/stop', [PresentationController::class, 'stop'])->name('host.stop');
    Route::post('/host/{token}/questions/{question}/activate', [PresentationController::class, 'activateQuestion'])
        ->name('host.questions.activate');

    Route::get('/presentations/{presentation}/summary', [PresentationController::class, 'summary'])
        ->name('presentations.summary');
    Route::post('/presentations/{presentation}/reset-results', [PresentationController::class, 'resetResults'])
        ->name('presentations.reset-results');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
