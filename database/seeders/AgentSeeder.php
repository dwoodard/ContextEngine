<?php

namespace Database\Seeders;

use App\Models\Agent;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure the 'handle' field is populated by the factory
        Agent::factory()->count(10)->create();
    }
}
