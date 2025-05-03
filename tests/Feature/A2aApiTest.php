<?php

use App\Jobs\ProcessTaskJob;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\postJson;

it('creates a task and dispatches a job', function () {
    $user = User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;

    Queue::fake();

    $a2aTaskId = 'unique-task-id-'.uniqid(); // Store the ID
    $payload = [
        'taskId' => $a2aTaskId, // Use the stored ID
        'message' => [
            'role' => 'user',
            'parts' => [
                [
                    'type' => 'text',
                    'text' => 'User message content',
                    'file' => [
                        'mimeType' => 'text/plain',
                        'uri' => 'http://example.com/file.txt',
                    ],
                ],
            ],
        ],
    ];

    $response = postJson('/api/a2a/v1/tasks/send', $payload, [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertStatus(202)
        ->assertJsonStructure([
            'taskId', 'status', // 'meta' is likely not part of the standard A2A response
            'lastAgentMessage', 'artifacts', 'createdAt', 'updatedAt',
        ])
        ->assertJson([
            'taskId' => $a2aTaskId,
            'status' => 'submitted', // Initial status
        ]);

    // Find the task that was created
    $createdTask = Task::where('a2a_task_id', $a2aTaskId)->first();
    expect($createdTask)->not->toBeNull(); // Assert task exists

    // *** CORRECTED ASSERTION ***
    // Assert the job was pushed with the INTERNAL database ID of the created task
    Queue::assertPushed(ProcessTaskJob::class, function ($job) use ($createdTask) {
        return $job->getTaskId() === $createdTask->id; // Compare internal IDs
    });
});

it('validates the task send payload', function () {
    $user = User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;

    $invalidPayload = [
        'message' => 'Missing taskId',
    ];

    $response = postJson('/api/a2a/v1/tasks/send', $invalidPayload, [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['taskId']);
});

it('fails authentication for task send', function () {
    $payload = [
        'taskId' => 'unique-task-id',
        'message' => 'User message content',
    ];

    $response = postJson('/api/a2a/v1/tasks/send', $payload);

    $response->assertStatus(401);
});
