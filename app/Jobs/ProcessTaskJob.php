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
use Illuminate\Support\Facades\Config;

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
        if (! $task) {
            return;
        }

        $task->update([
            'status' => 'running',
            'a2a_status' => Config::get('a2a.status_map.running'),
        ]);

        try {
            $agent = $this->resolvePattern($this->pattern);
            $output = $agent->execute($task);

            $nextSequenceId = ($task->a2a_last_message_sequence ?? 0) + 1;
            $finalResultText = is_string($output) ? $output : $task->result;

            if (!empty($finalResultText)) {
                $task->a2aMessages()->create([
                    'role' => 'agent',
                    'sequence_id' => $nextSequenceId,
                    'parts' => [['type' => 'text', 'text' => $finalResultText]],
                ]);
                $task->a2a_last_message_sequence = $nextSequenceId; // Update sequence on task
            }

            $task->update([
                'status' => 'completed',
                'a2a_status' => Config::get('a2a.status_map.completed'),
                'result' => $task->result,
                'a2a_last_message_sequence' => $task->a2a_last_message_sequence, // Save the updated sequence
            ]);

        } catch (\Throwable $e) {
            $nextSequenceId = ($task->a2a_last_message_sequence ?? 0) + 1;
            $task->a2aMessages()->create([
                'role' => 'agent',
                'sequence_id' => $nextSequenceId,
                'parts' => [['type' => 'text', 'text' => 'Task failed: ' . $e->getMessage()]],
            ]);
            $task->a2a_last_message_sequence = $nextSequenceId; // Update sequence on task

            $task->update([
                'status' => 'failed',
                'a2a_status' => Config::get('a2a.status_map.failed'),
                'result' => null,
                'meta' => array_merge($task->meta ?? [], ['error' => $e->getMessage()]),
                'a2a_last_message_sequence' => $task->a2a_last_message_sequence, // Save the updated sequence
            ]);

            throw $e;
        }
    }

    protected function resolvePattern(string $patternName): AgentPattern
    {
        return app(match ($patternName) {
            'planner' => PlannerExecutorAgent::class,
            'parallel' => ParallelAgent::class,
            'debate' => DebateAgent::class,
            'reflect' => SelfReflectorAgent::class,
            'memory' => MemoryAgent::class,
            'decompose' => GoalDecomposerAgent::class,
            default => PlannerExecutorAgent::class,
        });
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
