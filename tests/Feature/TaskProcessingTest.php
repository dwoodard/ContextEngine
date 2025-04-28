<?php

use App\Jobs\ProcessTaskJob;
use App\Models\Task;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake(); // Fake the queue to test job dispatching
});

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('a task can be created and dispatched for processing', function () {
    $payload = [
        'input' => 'Explain quantum computing.',
        'pattern' => 'planner',
    ];

    $response = $this->postJson('/api/tasks', $payload);

    $response->assertStatus(202)
        ->assertJsonStructure(['task_id', 'pattern', 'status']);

    $task = Task::find($response->json('task_id'));

    expect($task)->not->toBeNull();
    expect($task->status)->toBe('pending');

    Queue::assertPushed(ProcessTaskJob::class, function ($job) use ($task) {
        return $job->getTaskId() === $task->id && $job->getPattern() === 'planner';
    });
});

test('a task result can be retrieved after processing', function () {
    $task = Task::create([
        'input' => 'Explain quantum computing.',
        'pattern' => 'planner',
        'status' => 'completed',
        'result' => 'Quantum computing uses quantum bits to perform computations.',
    ]);

    $response = $this->getJson("/api/tasks/{$task->id}");

    $response->assertStatus(200)
        ->assertJson([
            'task_id' => $task->id,
            'pattern' => 'planner',
            'status' => 'completed',
            'result' => 'Quantum computing uses quantum bits to perform computations.',
        ]);
});

test('errors are returned in JSON format for API requests', function () {
    $response = $this->postJson('/api/tasks', []); // Sending an empty payload to trigger validation error

    $response->assertStatus(422) // Unprocessable Entity for validation errors
        ->assertJsonStructure([
            'message',
            'errors' => [
                'input', // Validation error for the missing 'input' field
            ],
        ]);
});

test('ProcessTaskJob processes a task and updates its status and result', function () {
    $task = Task::create([
        'input' => 'Explain quantum computing.',
        'pattern' => 'planner',
        'status' => 'pending',
    ]);

    // this will really run because the queue is not faked here
    ProcessTaskJob::dispatchSync($task->id, 'planner');

    $task->refresh();
    expect($task->status)->toBe('completed')
        ->and($task->result)->not()->toBeNull()
        ->and($task->result)->toContain('quantum');
});
