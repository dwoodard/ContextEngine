<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('creates a task via the endpoint', function () {
    $user = User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;

    $payload = [
        'input' => 'Explain quantum computing.',
        'pattern' => 'planner',
    ];

    $response = postJson('/api/tasks', $payload, [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertStatus(202)
        ->assertJsonStructure([
            'task_id', 'pattern', 'status',
        ]);

    expect(Task::where([
        'input' => 'Explain quantum computing.',
        'pattern' => 'planner',
        'status' => 'pending',
    ])->exists())->toBeTrue();
});

it('retrieves a task via the endpoint', function () {
    $task = Task::factory()->create([
        'input' => 'Explain quantum computing.',
        'pattern' => 'planner',
        'status' => 'completed',
        'result' => 'Quantum computing uses quantum bits to perform computations.',
    ]);

    $response = getJson("/api/tasks/{$task->id}");

    $response->assertStatus(200)
        ->assertJson([
            'task_id' => $task->id,
            'pattern' => 'planner',
            'status' => 'completed',
            'result' => 'Quantum computing uses quantum bits to perform computations.',
        ]);
});

it('validates task creation payload', function () {
    $user = User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;

    $response = postJson('/api/tasks', [], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['input', 'pattern']);
});

it('returns 404 for a non-existent task', function () {
    $response = getJson('/api/tasks/99999');

    $response->assertStatus(404);
});
