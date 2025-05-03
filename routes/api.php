<?php

use App\Http\Controllers\A2aController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;  // The new A2A controller

Route::get('/tasks', [TaskController::class, 'index']); // List all active tasks
Route::post('/tasks', [TaskController::class, 'store']);
Route::get('/tasks/{task}', [TaskController::class, 'show']);

// --- A2A Protocol Routes ---
// Apply Sanctum authentication middleware to all A2A routes
Route::prefix('a2a/v1')
    ->middleware('auth:sanctum') // Require valid Sanctum token
    ->name('a2a.api.') // Optional: Name prefix for routes
    ->group(function () {

        // POST /api/a2a/v1/tasks/send
        Route::post('tasks/send', [A2aController::class, 'tasksSend'])
            ->name('tasks.send');

        // POST /api/a2a/v1/tasks/sendSubscribe (For Streaming - Implement Later)
        // Route::post('tasks/sendSubscribe', [A2aController::class, 'tasksSendSubscribe'])
        //      ->name('tasks.sendSubscribe');

        // GET /api/a2a/v1/tasks/{taskId} (Optional: If needed to fetch status later)
        // Route::get('tasks/{taskId}', [A2aController::class, 'getTask'])
        //      ->name('tasks.get');

        // Add other A2A endpoints as you implement them (e.g., push notification setup)
    });

// User route for API authentication (e.g., creating tokens - needs implementation)
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
