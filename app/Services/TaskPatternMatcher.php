<?php

namespace App\Services;

class TaskPatternMatcher
{
    public function match(string $userInput): string
    {
        $input = strtolower($userInput);
        if (str_contains($input, ' versus') || str_contains($input, ' vs ')) {
            return 'debate';
        }
        if (preg_match('/\b(and|&|,)\b/', $input)) {
            return 'parallel';
        }
        if (str_contains($input, 'step') || str_contains($input, 'plan')) {
            return 'planner';
        }

        return 'planner'; // Default
    }
}
