<?php

namespace App\Agents\Traits\Capabilities;

trait HandlesTasks
{
    public function handleTask(string $task): string
    {
        // Example implementation
        return 'Handled task: '.$task;
    }
}
