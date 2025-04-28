<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Prism\Prism\Prism;

class SubTaskJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected int $taskId;

    protected string $subQuery;

    protected int $index;

    public function __construct(int $taskId, string $subQuery, int $index = 0)
    {
        $this->taskId = $taskId;
        $this->subQuery = $subQuery;
        $this->index = $index;
    }

    public function handle()
    {
        $task = Task::find($this->taskId);
        if (! $task) {
            return;
        }

        // Call LLM for this sub-query
        $response = Prism::text()->withPrompt($this->subQuery)->asText();
        $answer = $response->text ?? '';

        // Store sub-result in task.meta.sub_results[index]
        $meta = $task->meta ?? [];
        $meta['sub_results'][$this->index] = $answer;
        $task->meta = $meta;
        $task->save();
    }
}
