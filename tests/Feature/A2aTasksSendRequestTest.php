<?php

test('it validates task id is required', function () {
    $user = \App\Models\User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;

    $response = $this->postJson('/api/a2a/v1/tasks/send', [
        'message' => [
            'role' => 'user',
            'parts' => [
                ['type' => 'text', 'text' => 'Sample text'],
            ],
        ],
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('taskId');
});

test('it validates message parts file structure', function () {
    $user = \App\Models\User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;

    $response = $this->postJson('/api/a2a/v1/tasks/send', [
        'taskId' => 'unique-task-id',
        'message' => [
            'role' => 'user',
            'parts' => [
                [
                    'type' => 'text',
                    'text' => 'This is a sample text input.',
                ],
                [
                    'type' => 'file',
                    'file' => [
                        'mimeType' => 'application/pdf',
                        'uri' => 'http://example.com/file.pdf',
                        'bytes' => null,
                    ],
                ],
            ],
        ],
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertStatus(202);
});

test('it validates message parts data structure', function () {
    $user = \App\Models\User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;

    $response = $this->postJson('/api/a2a/v1/tasks/send', [
        'taskId' => 'unique-task-id',
        'message' => [
            'role' => 'user',
            'parts' => [
                [
                    'type' => 'text',
                    'text' => 'This is a valid text part.',
                ],
                [
                    'type' => 'data',
                    'data' => [
                        'key' => 'value',
                    ],
                ],
            ],
        ],
    ], [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertStatus(202);

});
