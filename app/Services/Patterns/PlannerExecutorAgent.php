<?php

namespace App\Services\Patterns;

use App\Models\Task;
use App\Services\AgentPattern;
use Prism\Prism\Prism;

class PlannerExecutorAgent implements AgentPattern
{
    public function execute(Task $task): void
    {
        $userQuery = $task->input;

        // Step 1: Use LLM to create a plan (sequence of steps or outline)
        $planPrompt = "Break down the following task into a step-by-step plan:\nTask: {$userQuery}";
        $planResponse = Prism::text()
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
        $answer = $answerResponse->text ?? '';

        // Update task with the result
        $task->result = $answer;
        $task->save();
    }
}
