<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Agent Information
    |--------------------------------------------------------------------------
    | Used for the Agent Card.
    */
    'agent' => [
        'displayName' => env('A2A_AGENT_DISPLAY_NAME', config('app.name').' Agent'),
        'description' => env('A2A_AGENT_DESCRIPTION', 'An agent powered by '.config('app.name')),
        // Add other relevant metadata like icons, publisher, etc.
        // 'publisher': 'Your Company Name',
        // 'iconUri': 'URL to an icon',
    ],

    /*
    |--------------------------------------------------------------------------
    | A2A Protocol Settings
    |--------------------------------------------------------------------------
    */
    'protocol' => [
        // The base URL where your A2A API endpoints will live.
        // Ensure this is correctly set in your .env file (APP_URL)
        // and accessible externally.
        'endpointBaseUrl' => env('APP_URL').'/api/a2a/v1',

        // List of capabilities supported by this agent.
        'capabilities' => [
            'tasks/send',
            // Add 'tasks/sendSubscribe' and 'streaming' if you implement SSE
            // Add 'pushNotifications' if you implement webhooks
        ],

        // Authentication methods supported.
        'authentication' => [
            [
                'type' => 'bearer', // For Laravel Sanctum API Tokens
                // Optionally add details about how to obtain a token if public
                // 'instructionsUri': 'https://your-docs.com/a2a-auth'
            ],
            // Add other methods like 'apiKey', 'oauth2' if needed
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | A2A Status Mapping
    |--------------------------------------------------------------------------
    | Map internal statuses to A2A standard statuses.
    */
    'status_map' => [
        // Internal Status => A2A Status
        'pending' => 'submitted',
        'running' => 'working',
        'completed' => 'completed',
        'failed' => 'failed',
        'awaiting_input' => 'input-required', // Example if you add this state
        // Add 'canceled' if applicable
    ],

    'reverse_status_map' => [
        // A2A Status => Internal Status (Useful for filtering/updates)
        'submitted' => 'pending',
        'working' => 'running',
        'completed' => 'completed',
        'failed' => 'failed',
        'input-required' => 'awaiting_input',
        // 'canceled' => 'canceled',
    ],
];
