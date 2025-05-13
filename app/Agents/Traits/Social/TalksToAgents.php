<?php

namespace App\Agents\Traits\Social;

trait TalksToAgents
{
    public function talkToAgent(string $message, string $agent): string
    {
        // Example implementation
        return 'Message to agent: '.$agent.' - '.$message;
    }
}
