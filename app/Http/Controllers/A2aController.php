<?php

namespace App\Http\Controllers;

use App\Http\Requests\A2aTasksSendRequest;
use App\Jobs\ProcessTaskJob;
use App\Models\Task;
use App\Services\TaskPatternMatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class A2aController extends Controller
{
    /**
     * Serve the Agent Card JSON.
     * CLEANED UP VERSION: Uses config keys directly.
     */
    public function agentCard(): JsonResponse
    {
        $config = Config::get('a2a');

        $agentCard = [
            'a2aVersion' => '1.0.0',
            'agent' => $config['agent'], // Directly use the agent array from config
            'capabilities' => $config['protocol']['capabilities'],
            'endpointUrl' => $config['protocol']['endpointBaseUrl'],
            'authentication' => $config['protocol']['authentication'],
            'skills' => [ // Keep this hardcoded or make it dynamic
                ['name' => 'General Task Processing', 'description' => 'Processes general user queries.'],
                ['name' => 'Debate Simulation', 'description' => 'Simulates a debate between perspectives.'],
            ],
        ];

        return response()->json($agentCard);
    }

    /**
     * Handle A2A tasks/send requests.
     * IMPLEMENTED VERSION
     */
    public function tasksSend(A2aTasksSendRequest $request, TaskPatternMatcher $matcher): JsonResponse
    {
        $validated = $request->validated();
        $a2aTaskId = $validated['taskId'];
        $a2aMessagePayload = $validated['message']; // A2A Message structure

        $task = Task::where('a2a_task_id', $a2aTaskId)->first();
        $isNewTask = false;

        if (! $task) {
            // --- Create New Task ---
            $isNewTask = true;
            $userInput = '';
            foreach ($a2aMessagePayload['parts'] as $part) {
                if ($part['type'] === 'text' && isset($part['text'])) {
                    $userInput .= $part['text']."\n";
                }
                // TODO: Handle incoming file/data parts if needed
            }
            $userInput = trim($userInput);

            if (empty($userInput)) {
                return response()->json(['error' => 'Could not extract valid input text.'], 400);
            }

            $pattern = $request->input('pattern') ?? $matcher->match($userInput);

            // Create the main Task record
            $task = Task::create([
                'input' => $userInput,
                'pattern' => $pattern,
                'status' => 'pending', // Internal status
                'a2a_task_id' => $a2aTaskId,
                'a2a_status' => Config::get('a2a.status_map.pending'),
                // Remove a2a_meta for messages if using separate table
                'a2a_meta' => ['artifacts' => []], // Keep for artifacts for now
                'a2a_last_message_sequence' => 0, // Start sequence
                'meta' => [/* keep other internal meta if needed */],
            ]);

            // Create the first A2A Message record
            $task->a2aMessages()->create([
                'role' => $a2aMessagePayload['role'],
                'sequence_id' => 1, // First message
                'parts' => $a2aMessagePayload['parts'],
            ]);

            $task->a2a_last_message_sequence = 1; // Update sequence on task
            $task->save();

            // *** THIS IS THE CRUCIAL LINE FOR THE TEST ***
            ProcessTaskJob::dispatch($task->id, $pattern);

        } else {
            // --- Handle subsequent message for existing task ---
            if ($task->a2a_status !== Config::get('a2a.status_map.awaiting_input') && $task->a2a_status !== 'input-required') {
                return response()->json([
                    'error' => 'Task is not currently awaiting input.',
                    'taskId' => $task->a2a_task_id,
                    'status' => $task->a2a_status,
                ], 409); // Conflict
            }

            $nextSequenceId = ($task->a2a_last_message_sequence ?? 0) + 1;

            // Create the subsequent A2A Message record
            $task->a2aMessages()->create([
                'role' => $a2aMessagePayload['role'],
                'sequence_id' => $nextSequenceId,
                'parts' => $a2aMessagePayload['parts'],
            ]);

            // Update task status back to 'working' / 'running'
            $task->status = 'running'; // Or appropriate internal state
            $task->a2a_status = Config::get('a2a.status_map.running');
            $task->a2a_last_message_sequence = $nextSequenceId;

            // TODO: Add logic to notify the *running* job with the new input.
            // This is the tricky part with background jobs. Options:
            // 1. Event Broadcasting: Fire an event `NewInputReceived($taskId, $newInput)`
            //    The running job needs to listen for this (complex).
            // 2. Database Polling: The job occasionally checks a `new_input` field on the task model.
            // 3. Cache/Redis: Store new input in Redis keyed by task ID, job checks Redis.
            // Let's assume for now the *next* step in the agent pattern handles this (if using planner etc.)
            // Or, if the job finished while waiting, we might need to re-dispatch.
            // $task->meta['last_provided_input'] = $newInput; // Store for job to potentially pick up
            $task->save();

            // Decide if re-dispatch is needed based on your agent patterns' design
            // ProcessTaskJob::dispatch($task->id, $task->pattern);

        }

        // Return current task state as per A2A format
        return response()->json($this->formatA2aTask($task), $isNewTask ? 202 : 200);
    }

    /**
     * Formats an internal Task model into the A2A Task JSON structure.
     */
    protected function formatA2aTask(Task $task): array
    {
        // Load the latest agent message from the relationship
        $lastAgentA2AMessage = $task->a2aMessages()
            ->where('role', 'agent')
            ->orderByDesc('sequence_id')
            ->first();

        $lastAgentMessageFormatted = null;
        if ($lastAgentA2AMessage) {
            $lastAgentMessageFormatted = [
                'role' => $lastAgentA2AMessage->role,
                'parts' => $lastAgentA2AMessage->parts,
                'sequenceId' => $lastAgentA2AMessage->sequence_id,
            ];
        }

        // Prepare artifacts list (still reading from a2a_meta for now)
        $a2aMeta = $task->a2a_meta ?? ['artifacts' => []];
        $artifacts = [];
        foreach ($a2aMeta['artifacts'] ?? [] as $artifactData) {
            $artifacts[] = [
                'artifactId' => $artifactData['artifactId'] ?? Str::uuid()->toString(),
                'mimeType' => $artifactData['mimeType'] ?? 'application/octet-stream',
                'parts' => $artifactData['parts'] ?? [],
                'name' => $artifactData['name'] ?? null,
            ];
        }

        return [
            'taskId' => $task->a2a_task_id,
            'status' => $task->a2a_status ?? Config::get('a2a.status_map.'.$task->status),
            'lastAgentMessage' => $lastAgentMessageFormatted, // Use formatted message from DB
            'artifacts' => $artifacts,
            // 'meta' => $task->meta ?? [], // Generally A2A spec doesn't expose internal meta
            'createdAt' => $task->created_at?->toIso8601String(),
            'updatedAt' => $task->updated_at?->toIso8601String(),
            'error' => $task->a2a_status === Config::get('a2a.status_map.failed') ? ($task->meta['error'] ?? 'Unknown error') : null, // Add error if failed
        ];
    }
}
