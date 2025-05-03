<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config; // Import Config facade

class A2aController extends Controller
{
    /**
     * Serve the Agent Card JSON.
     */
    public function agentCard(): JsonResponse
    {
        $config = Config::get('a2a'); // Use Config facade

        $agentCard = [
            'a2aVersion' => '1.0.0', // Specify the A2A protocol version you support
            'agent' => $config['agent'],
            'capabilities' => $config['protocol']['capabilities'],
            'endpointUrl' => $config['protocol']['endpointBaseUrl'],
            'authentication' => $config['protocol']['authentication'],
            // Add other required/optional fields from the spec as needed
            'skills' => [
                // You might dynamically generate this based on your 'patterns'
                ['name' => 'General Task Processing', 'description' => 'Processes general user queries.'],
                ['name' => 'Debate Simulation', 'description' => 'Simulates a debate between perspectives.'],
                // ... add more based on your patterns (planner, parallel, etc.)
            ],
        ];

        return response()->json($agentCard);
    }

    // We will add methods for tasks/send etc. later
}
