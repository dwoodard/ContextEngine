<?php

use App\Http\Controllers\A2aController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia; // Make sure to create this controller

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/.well-known/agent.json', [A2aController::class, 'agentCard'])->name('a2a.agent-card');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
