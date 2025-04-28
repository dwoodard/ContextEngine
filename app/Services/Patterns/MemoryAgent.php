<?php

namespace App\Services\Patterns;

use App\Models\Task;
use App\Services\AgentPattern;
use Illuminate\Support\Str;
use Prism\Prism\Prism;

class MemoryAgent implements AgentPattern
{
    public function execute(Task $task): void
    {
        $query = $task->input;
        $context = '';

        // Retrieve some relevant past knowledge (simple strategy: find tasks with similar keywords)
        $keywords = array_slice(explode(' ', Str::lower($query)), 0, 3); // take first 3 keywords
        if (! empty($keywords)) {
            $relatedTasks = Task::where('id', '!=', $task->id)
                ->where(function ($q) use ($keywords) {
                    foreach ($keywords as $word) {
                        $q->orWhere('input', 'LIKE', "%{$word}%");
                    }
                })
                ->orderBy('id', 'desc')
                ->limit(3)
                ->get();
            foreach ($relatedTasks as $rel) {
                if ($rel->result) {
                    $context .= "Q: {$rel->input}\nA: {$rel->result}\n\n";
                }
            }
        }

        if ($context) {
            // If we found related info, prepend it to the prompt
            $prompt = "Use the following information to help answer the question.\n{$context}\nQuestion: {$query}\nAnswer:";
        } else {
            $prompt = $query;
        }

        // Call LLM with the context-enhanced prompt
        $response = Prism::text()->withPrompt($prompt)->asText();
        $answer = $response->text ?? '';

        // Save context used (for transparency) and the result
        $task->meta = array_merge($task->meta ?? [], ['used_context' => $context]);
        $task->result = $answer;
        $task->save();
    }
}
