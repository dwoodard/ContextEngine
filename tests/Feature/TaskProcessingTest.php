<?php

use App\Jobs\ProcessTaskJob;
use App\Models\Task;
use Illuminate\Support\Facades\Queue;

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('a task can be created and dispatched for processing', function () {
    // Fake the queue to intercept job dispatches
    Queue::fake();

    $payload = [
        'input' => 'Explain quantum computing.',
        'pattern' => 'planner',
    ];

    // Send a POST request to create the task
    $response = $this->postJson('/api/tasks', $payload);

    // Assert that the response has the expected status and structure
    $response->assertStatus(202)
        ->assertJsonStructure(['task_id', 'pattern', 'status']);

    // Retrieve the task from the database
    $task = Task::find($response->json('task_id'));

    // Assert that the task exists and has the correct status
    expect($task)->not->toBeNull();
    expect($task->status)->toBe('pending');

    // Assert that the ProcessTaskJob was dispatched with the correct task
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
    $task = Task::factory()->create([
        'input' => 'Explain quantum computing.',
        'pattern' => 'planner',
        'status' => 'pending',
        'a2a_task_id' => 'test-a2a-id', // Ensure this field is set
    ]);

    $job = new ProcessTaskJob($task->id, 'planner');
    $job->handle();

    $task->refresh();

    expect($task->status)->toBe('completed');
    expect($task->result)->not->toBeNull();
});

test('ProcessTaskJob sets final a2a_status correctly on success', function () {
    $task = Task::factory()->create([
        'input' => 'Explain quantum computing.',
        'pattern' => 'planner',
        'status' => 'pending',
        'a2a_task_id' => 'test-a2a-id',
    ]);

    $job = new ProcessTaskJob($task->id, 'planner');
    $job->handle();

    $task->refresh();

    expect($task->status)->toBe('completed');
    expect($task->a2a_status)->toBe('completed');
});

test('ProcessTaskJob sets a2a_status to failed on exception', function () {
    $task = Task::factory()->create([
        'input' => 'Explain quantum computing.',
        'pattern' => 'planner',
        'status' => 'pending',
        'a2a_task_id' => 'test-a2a-id',
    ]);

    $mockJob = $this->mock(ProcessTaskJob::class, function ($mock) {
        $mock->shouldReceive('handle')->andThrow(new \Exception('Simulated failure'));
    });

    try {
        $mockJob->handle();
    } catch (\Exception $e) {
        // Exception is expected
    }

    $task->refresh();

    expect($task->status)->toBe('failed');
    expect($task->a2a_status)->toBe('failed');
});
