<?php

namespace App\Services\Patterns;

use App\Models\Task;
use App\Services\AgentPattern;
use Prism\Prism\Prism;

class PlannerExecutorAgent implements AgentPattern
{
    public function execute(Task $task)
    {
        $userQuery = $task->input;

        $planPrompt = "Break down the following task into a step-by-step plan:\nTask: {$userQuery}";

        $planResponse = Prism::text()
            ->using('ollama', 'llama3.2:latest')
            ->withPrompt($planPrompt)
            ->asText();

        $plan = $planResponse->text ?? '(no plan)';

        // Optional: store the plan in task meta for record
        $task->meta = array_merge($task->meta ?? [], ['plan' => $plan]);
        $task->save();

        // Step 2: Use LLM to execute/answer using the plan
        $execPrompt = "Given the plan:\n{$plan}\nNow provide the final answer or solution to the original task: {$userQuery}";

        $answerResponse = Prism::text()
            ->withPrompt($execPrompt)
            ->asText();

        return $answerResponse->text ?? '';
    }
}
