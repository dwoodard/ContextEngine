<?php

namespace App\Agents\Traits\Social;

trait Delegates
{
    public function delegate(string $task, string $agent): string
    {
        // Example implementation
        return 'Delegated task: '.$task.' to agent: '.$agent;
    }
}
