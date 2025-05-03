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
        'version' => env('A2A_AGENT_VERSION', '1.0.0'), // Added agent version
        'provider' => [
            'organization' => env('A2A_AGENT_PROVIDER_ORG', 'Default Organization'),
            'url' => env('A2A_AGENT_PROVIDER_URL', null), // Optional provider URL
        ],
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
            'tasks/send' => true,
            'tasks/sendSubscribe' => true,
            'streaming' => true,
        ],

        // Authentication methods supported.
        'authentication' => [
            'schemes' => ['bearer'],
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
        'awaiting_input' => 'input-required',
    ],

    'reverse_status_map' => [
        // A2A Status => Internal Status (Useful for filtering/updates)
        'submitted' => 'pending',
        'working' => 'running',
        'completed' => 'completed',
        'failed' => 'failed',
        'input-required' => 'awaiting_input',
    ],
];
