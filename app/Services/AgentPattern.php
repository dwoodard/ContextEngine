<?php

namespace App\Services;

use App\Models\Task;

interface AgentPattern
{
    public function execute(Task $task): void;
}
