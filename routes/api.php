<?php

use App\Http\Controllers\TaskController;

Route::get('/tasks', [TaskController::class, 'index']); // List all active tasks
Route::post('/tasks', [TaskController::class, 'store']);
Route::get('/tasks/{task}', [TaskController::class, 'show']);
