<?php

use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake(); // Fake the queue to test job dispatching
});

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
