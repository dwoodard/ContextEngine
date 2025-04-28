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
        return $job->taskId === $task->id && $job->pattern === 'planner';
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
