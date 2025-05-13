<?php

namespace Database\Seeders;

use App\Models\Agent;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder
{
    public function run(): void
    {
        Agent::create([
            'name' => 'RouterAgent',
            'description' => 'Coordinates actions among multiple agents.',
            'class' => \App\Agents\RouterAgent::class,
            'url' => route('agent.show', ['id' => 1], false),
            'skills' => json_encode(['routing', 'delegation']),
            'is_public' => true,
        ]);

        Agent::create([
            'name' => 'DeveloperAgent',
            'description' => 'Handles technical development-related queries.',
            'class' => \App\Agents\DeveloperAgent::class,
            'url' => route('agent.show', ['id' => 2], false),
            'skills' => json_encode(['code_generation', 'debugging']),
            'is_public' => true,
        ]);

        Agent::create([
            'name' => 'FinancialAgent',
            'description' => 'Analyzes financial metrics and forecasts.',
            'class' => \App\Agents\FinancialAgent::class,
            'url' => route('agent.show', ['id' => 3], false),
            'skills' => json_encode(['finance', 'reporting']),
            'is_public' => true,
        ]);
    }
}
