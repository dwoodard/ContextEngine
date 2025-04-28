<?php

use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

it('can generate a response using the Ollama provider', function () {
    // Arrange: Set up a sample prompt
    $prompt = 'What is the capital of France?';

    // Act: Use the Prism library with the Ollama provider
    $response = Prism::text()
        ->using('ollama', 'llama3.2:latest')
        ->withPrompt($prompt)
        ->asText();

    // Assert: Verify the response contains the expected keyword
    expect($response->text)->toContain('Paris');
});
