<?php

namespace App\Agents\Traits\Cognitive;

trait ImprovesItself
{
    public function reflect(string $input, string $output): array
    {
        $summary = "The input was: '{$input}'. The agent responded: '{$output}'.";

        return [
            'reflection' => $summary,
            'insight' => "Consider if this output meets the user's intent.",
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}
