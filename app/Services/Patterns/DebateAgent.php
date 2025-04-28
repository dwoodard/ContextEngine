<?php

namespace App\Services\Patterns;

use App\Models\Task;
use App\Services\AgentPattern;
use Prism\Prism\Prism;

class DebateAgent implements AgentPattern
{
    public function execute(Task $task): void
    {
        $userQuery = $task->input;

        // Agent A: proponent perspective
        $promptA = "You are Agent A. You strongly support or agree with the following statement/question and provide an answer arguing for it:\nQ: {$userQuery}";
        $respA = Prism::text()->withPrompt($promptA)->asText();
        $answerA = $respA->text ?? '';

        // Agent B: opponent/skeptical perspective
        $promptB = "You are Agent B. You are skeptical or take an opposing view on the statement/question and provide an answer arguing against it or highlighting issues:\nQ: {$userQuery}";
        $respB = Prism::text()->withPrompt($promptB)->asText();
        $answerB = $respB->text ?? '';

        // Store both answers in meta for reference
        $task->meta = [
            'agent_A' => $answerA,
            'agent_B' => $answerB,
        ];
        $task->save();

        // Moderator: form a consensus or choose the best answer
        $moderatorPrompt = "Agent A answered:\n{$answerA}\n\nAgent B answered:\n{$answerB}\n\nYou are a moderator. Combine the insights from both agents and provide the best final answer to the question: {$userQuery}";
        $respM = Prism::text()->withPrompt($moderatorPrompt)->asText();
        $finalAnswer = $respM->text ?? '';

        // Update task result with the consensus answer
        $task->result = $finalAnswer;
        $task->save();
    }
}
