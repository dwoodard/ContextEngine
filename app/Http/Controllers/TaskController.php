<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Jobs\ProcessTaskJob;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $data = $request->validate([
            'input' => 'required|string',
            'pattern' => 'nullable|string',
        ]);

        $pattern = $data['pattern'] ?? 'planner';

        $task = Task::create([
            'input' => $data['input'],
            'pattern' => $pattern,
            'status' => 'pending',
        ]);

        ProcessTaskJob::dispatch($task->id, $pattern);

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
}
