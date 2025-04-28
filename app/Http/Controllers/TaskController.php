<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTaskRequest;
use App\Jobs\ProcessTaskJob;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // list all tasks, that are active
        $tasks = Task::where('status', '!=', 'completed')->get();

        return response()->json($tasks);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate input - ensure at least a prompt or input is provided
        $data = $request->validate([
            'input' => 'required|string',
            'pattern' => 'nullable|string',  // optionally user can specify a pattern
        ]);

        // Determine which agent pattern to use
        $pattern = $data['pattern'] ?? $this->matchPattern($data['input']);

        // Create a new task record in DB with status 'pending'
        $task = Task::create([
            'input' => $data['input'],
            'pattern' => $pattern,
            'status' => 'pending',
        ]);

        // Dispatch a job to process this task using the chosen pattern
        ProcessTaskJob::dispatch($task->id, $pattern);

        // Return a response with task ID and initial status
        return response()->json([
            'task_id' => $task->id,
            'pattern' => $pattern,
            'status' => $task->status,
        ], 202);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        // Using route model binding to get Task by ID
        return response()->json([
            'task_id' => $task->id,
            'pattern' => $task->pattern,
            'status' => $task->status,
            'result' => $task->result,
            'meta' => $task->meta,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        //
    }

    /**
     * Simple Task Interpreter/Pattern Matcher.
     */
    protected function matchPattern(string $userInput): string
    {
        // **Basic heuristic pattern selection**
        // (This can be as complex as needed, even using an LLM to classify the task)
        $input = strtolower($userInput);
        if (str_contains($input, ' versus') || str_contains($input, ' vs ')) {
            return 'debate';  // user is asking for comparison -> debate pattern
        }
        if (preg_match('/\b(and|&|,)\b/', $input)) {
            return 'parallel';  // multiple parts in query -> parallelize
        }
        if (str_contains($input, 'step') || str_contains($input, 'plan')) {
            return 'planner';  // explicit steps or planning needed
        }

        // Default fallback:
        return 'planner';  // default to planner-executor if unsure
    }
}
