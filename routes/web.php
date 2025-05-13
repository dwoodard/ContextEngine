<?php

use App\Http\Controllers\AgentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/agent/{handle}', [AgentController::class, 'show'])->name('agent.show');

Route::get('/.well-known/agents.json', function () {
    /*
        The /.well-known/agent.json file is a standard location
        for AI agents to publish information about themselves,
        enabling other agents to discover and interact with
        them. It's part of the Agent2Agent (A2A) protocol
        which aims to standardize communication between
        AI agents. This file essentially acts as a "business card"
        for the agent, containing details like its name, description,
        supported skills, and authentication requirements.
    */
    $agents = \App\Models\Agent::where('is_public', true)->get()->map(function ($agent) {
        $url = route('agent.show', ['handle' => $agent->handle]);

        return [
            'name' => $agent->name,
            'url' => $url,
        ];
    });

    return response()->json([
        'agents' => $agents,
    ]);
})->name('agents');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::resource('agents', AgentController::class);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
