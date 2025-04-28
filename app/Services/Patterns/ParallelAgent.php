<?php

namespace App\Services\Patterns;

use App\Models\Task;
use App\Services\AgentPattern;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

class ParallelAgent implements AgentPattern
{
    public function execute(Task $task): void
    {
        $userQuery = $task->input;
        // Simple split logic: split by " and " (for demonstration)
        $parts = preg_split('/\band\b|&|,/i', $userQuery);
        $parts = array_filter(array_map('trim', $parts));  // clean empty entries

        if (count($parts) <= 1) {
            // If we couldn't detect multiple parts, just use PlannerExecutor as fallback
            (new PlannerExecutorAgent)->execute($task);

            return;
        }

        // Dispatch a sub-task job for each part (in parallel)
        $batch = Bus::batch([])->allowFailures()->then(function (Batch $batch) use ($task) {
            // This callback runs after all sub-tasks complete
            // Combine results from meta
            $subResults = $task->meta['sub_results'] ?? [];
            $combined = implode("\n", $subResults);
            $task->result = $combined;
            $task->save();
        })->dispatch();

        // Launch each part as a separate job
        foreach ($parts as $idx => $part) {
            $batch->add(new \App\Jobs\SubTaskJob($task->id, trim($part), $idx));
        }
    }
}
