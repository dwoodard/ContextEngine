<?php

namespace App\Jobs;

use App\Models\Task;
use App\Services\AgentPattern;
use App\Services\Patterns\DebateAgent;
use App\Services\Patterns\GoalDecomposerAgent;
use App\Services\Patterns\MemoryAgent;
use App\Services\Patterns\ParallelAgent;
use App\Services\Patterns\PlannerExecutorAgent;
use App\Services\Patterns\SelfReflectorAgent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $taskId;

    protected string $pattern;

    public function __construct(int $taskId, string $pattern)
    {
        $this->taskId = $taskId;
        $this->pattern = $pattern;
    }

    public function handle()
    {
        $task = Task::find($this->taskId);

        $task->update(['status' => 'running']);

        try {
            $agent = $this->resolvePattern($this->pattern);

            $output = $agent->execute($task);   // capture return value

            // Persist result if the agent didn’t update the model itself
            if (is_string($output) && empty($task->result)) {
                $task->result = $output;
            }

            $task->update(['status' => 'completed']);
        } catch (\Throwable $e) {
            $task->update([
                'status' => 'failed',
                'result' => null,
                'meta' => array_merge($task->meta ?? [], ['error' => $e->getMessage()]),
            ]);
            throw $e;
        }
    }

    protected function resolvePattern(string $patternName): AgentPattern
    {
        return match ($patternName) {
            'planner' => new PlannerExecutorAgent,
            'parallel' => new ParallelAgent,
            'debate' => new DebateAgent,
            'reflect' => new SelfReflectorAgent,
            'memory' => new MemoryAgent,
            'decompose' => new GoalDecomposerAgent,
            default => new PlannerExecutorAgent,
        };
    }

    public function getTaskId(): int
    {
        return $this->taskId;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }
}
