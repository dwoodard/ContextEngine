<?php

namespace App\Services\Patterns;

use App\Models\Task;
use App\Services\AgentPattern;
use Prism\Prism\Prism;

class GoalDecomposerAgent implements AgentPattern
{
    public function execute(Task $task): void
    {
        $query = $task->input;

        // Step 1: Ask LLM to decompose the goal into sub-goals
        $decomposePrompt = "Break down the following task into a list of smaller goals or questions:\nTask: {$query}";
        $decompResp = Prism::text()->withPrompt($decomposePrompt)->asText();
        $subGoalsText = $decompResp->text ?? '';
        $subGoals = preg_split('/\n+/', trim($subGoalsText));  // split by lines as subgoals
        $subGoals = array_filter(array_map('trim', $subGoals));

        if (empty($subGoals)) {
            // If no decomposition, just answer directly
            $directResp = Prism::text()->withPrompt($query)->asText();
            $task->result = $directResp->text ?? '';
            $task->save();

            return;
        }

        // Step 2: Solve each sub-goal
        $subResults = [];
        foreach ($subGoals as $subGoal) {
            $subAnswerResp = Prism::text()->withPrompt($subGoal)->asText();
            $subResults[] = ($subAnswerResp->text ?? '');
        }
        // Store sub-goals and sub-results in meta for traceability
        $task->meta = [
            'sub_goals' => $subGoals,
            'sub_results' => $subResults,
        ];
        $task->save();

        // Step 3: Ask LLM to synthesize a final answer from all sub-results
        $synthesisPrompt = "We had broken the task into sub-goals and answered each:\n";
        foreach ($subGoals as $index => $sg) {
            $ans = $subResults[$index];
            $synthesisPrompt .= "- Sub-goal: {$sg}\n  Answer: {$ans}\n";
        }
        $synthesisPrompt .= "\nNow combine these findings into a comprehensive final answer for the original task: {$query}";

        $finalResp = Prism::text()->withPrompt($synthesisPrompt)->asText();
        $finalAnswer = $finalResp->text ?? implode("\n", $subResults);

        $task->result = $finalAnswer;
        $task->save();
    }
}
