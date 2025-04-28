<?php

namespace App\Services\Patterns;

use App\Models\Task;
use App\Services\AgentPattern;
use Prism\Prism\Prism;

class SelfReflectorAgent implements AgentPattern
{
    public function execute(Task $task): void
    {
        $query = $task->input;

        // First attempt answer
        $initialResp = Prism::text()->withPrompt($query)->asText();
        $initialAnswer = $initialResp->text ?? '';

        // Self-reflection: have the LLM critique its answer
        $reflectPrompt = "You answered the question as follows:\n\"{$initialAnswer}\"\nNow reflect on this answer. Identify any incorrect assumptions, missing details, or improvements. Provide a critique.";
        $critiqueResp = Prism::text()->withPrompt($reflectPrompt)->asText();
        $critique = $critiqueResp->text ?? '';

        // Use the critique to improve the answer
        $improvePrompt = "Critique of the answer: {$critique}\n\nNow improve the original answer based on this critique. Provide a final, refined answer to the question: {$query}";
        $finalResp = Prism::text()->withPrompt($improvePrompt)->asText();
        $finalAnswer = $finalResp->text ?? $initialAnswer;

        // Store critique and set final result
        $task->meta = array_merge($task->meta ?? [], ['critique' => $critique]);
        $task->result = $finalAnswer;
        $task->save();
    }
}
