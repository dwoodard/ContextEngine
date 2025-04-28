<?php

namespace App\Services\Patterns;

use App\Models\Task;
use App\Services\AgentPattern;
use Prism\Prism\Prism;

class PlannerExecutorAgent implements AgentPattern
{
    public function execute(Task $task): string
    {
        $userQuery = $task->input;

        $planPrompt = "Break down the following task into a step-by-step plan:\nTask: {$userQuery}";

        try {
            $planResponse = Prism::text()
                ->using('ollama', 'llama3.2:latest')
                ->withPrompt($planPrompt)
                ->asText();

            $plan = $planResponse->text ?? '(no plan)';
        } catch (\Throwable $e) {
            $plan = '(error generating plan)';
            $task->meta = array_merge($task->meta ?? [], ['error' => $e->getMessage()]);
        }

        $task->meta = array_merge($task->meta ?? [], ['plan' => $plan]);
        $task->save();

        $execPrompt = "Given the plan:\n{$plan}\nNow provide the final answer or solution to the original task: {$userQuery}";

        try {
            $answerResponse = Prism::text()
                ->using('ollama', 'llama3.2:latest')
                ->withPrompt($execPrompt)
                ->asText();

            return $answerResponse->text ?? '';
        } catch (\Throwable $e) {
            $task->meta = array_merge($task->meta ?? [], ['error' => $e->getMessage()]);

            return '(error generating answer)';
        }
    }
}
