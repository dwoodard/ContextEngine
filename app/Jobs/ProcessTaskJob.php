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
use Illuminate\Support\Str;

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
        if (! $task || ! $task->a2a_task_id) {
            return;
        }

        $task->update([
            'status' => 'running',
            'a2a_status' => Config::get('a2a.status_map.running'),
        ]);

        try {
            $agent = $this->resolvePattern($this->pattern);
            $output = $agent->execute($task);

            $a2aMeta = $task->a2a_meta ?? ['messages' => [], 'artifacts' => []];
            $finalAgentMessageParts = [];
            $finalArtifacts = [];

            $finalResultText = is_string($output) ? $output : $task->result;
            if (! empty($finalResultText)) {
                $finalAgentMessageParts[] = [
                    'type' => 'text',
                    'text' => $finalResultText,
                ];
                if (empty($task->result)) {
                    $task->result = $finalResultText;
                }
            }

            if (isset($task->meta['sub_results']) && is_array($task->meta['sub_results'])) {
                $artifactParts = [];
                foreach ($task->meta['sub_results'] as $key => $subResult) {
                    $artifactParts[] = ['type' => 'text', 'text' => "Sub-result {$key}: ".$subResult];
                }
                if (! empty($artifactParts)) {
                    $finalArtifacts[] = [
                        'artifactId' => Str::uuid()->toString(),
                        'mimeType' => 'text/plain',
                        'parts' => $artifactParts,
                        'name' => 'sub_task_results.txt',
                    ];
                }
            }

            if (! empty($finalAgentMessageParts)) {
                $a2aMeta['messages'][] = [
                    'role' => 'agent',
                    'parts' => $finalAgentMessageParts,
                    'sequenceId' => ($task->a2a_last_message_sequence ?? 0) + 1,
                ];
            }

            $a2aMeta['artifacts'] = array_merge($a2aMeta['artifacts'] ?? [], $finalArtifacts);

            $task->update([
                'status' => 'completed',
                'a2a_status' => Config::get('a2a.status_map.completed'),
                'result' => $task->result,
                'a2a_meta' => $a2aMeta,
                'a2a_last_message_sequence' => $a2aMeta['messages'][count($a2aMeta['messages']) - 1]['sequenceId'] ?? $task->a2a_last_message_sequence,
            ]);

        } catch (\Throwable $e) {
            $a2aMeta = $task->a2a_meta ?? ['messages' => [], 'artifacts' => []];
            $a2aMeta['messages'][] = [
                'role' => 'agent',
                'parts' => [['type' => 'text', 'text' => 'Task failed: '.$e->getMessage()]],
                'sequenceId' => ($task->a2a_last_message_sequence ?? 0) + 1,
            ];

            $task->update([
                'status' => 'failed',
                'a2a_status' => Config::get('a2a.status_map.failed'),
                'result' => null,
                'meta' => array_merge($task->meta ?? [], ['error' => $e->getMessage()]),
                'a2a_meta' => $a2aMeta,
                'a2a_last_message_sequence' => $a2aMeta['messages'][count($a2aMeta['messages']) - 1]['sequenceId'] ?? $task->a2a_last_message_sequence,
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
