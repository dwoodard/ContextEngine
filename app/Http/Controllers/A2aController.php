<?php

namespace App\Http\Controllers;

use App\Http\Requests\A2aTasksSendRequest;
use App\Jobs\ProcessTaskJob;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class A2aController extends Controller
{
    /**
     * Serve the Agent Card JSON.
     */
    public function agentCard(): JsonResponse
    {
        $config = Config::get('a2a'); // Use Config facade

        $agentCard = [
            'a2aVersion' => '1.0.0', // Specify the A2A protocol version you support
            'agent' => $config['agent'],
            'capabilities' => $config['protocol']['capabilities'],
            'endpointUrl' => $config['protocol']['endpointBaseUrl'],
            'authentication' => $config['protocol']['authentication'],
            // Add other required/optional fields from the spec as needed
            'skills' => [
                // You might dynamically generate this based on your 'patterns'
                ['name' => 'General Task Processing', 'description' => 'Processes general user queries.'],
                ['name' => 'Debate Simulation', 'description' => 'Simulates a debate between perspectives.'],
                // ... add more based on your patterns (planner, parallel, etc.)
            ],
        ];

        return response()->json($agentCard);
    }

    /**
     * Handle A2A tasks/send requests.
     */
    public function tasksSend(A2aTasksSendRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $a2aTaskId = $validated['taskId'];
        $message = $validated['message'];

        $task = Task::where('a2a_task_id', $a2aTaskId)->first();
        $isNewTask = false;

        if (! $task) {
            $isNewTask = true;
            $userInput = '';
            foreach ($message['parts'] as $part) {
                if ($part['type'] === 'text' && isset($part['text'])) {
                    $userInput .= $part['text']."\n";
                }
            }
            $userInput = trim($userInput);

            if (empty($userInput)) {
                return response()->json(['error' => 'Could not extract valid input text.'], 400);
            }

            $pattern = $request->input('pattern') ?? $this->matchPattern($userInput);

            $task = Task::create([
                'input' => $userInput,
                'pattern' => $pattern,
                'status' => 'pending',
                'a2a_task_id' => $a2aTaskId,
                'a2a_status' => Config::get('a2a.status_map.pending'),
                'meta' => ['initial_a2a_message' => $message],
                'a2a_meta' => ['messages' => [$message], 'artifacts' => []],
                'a2a_last_message_sequence' => 1,
            ]);

            ProcessTaskJob::dispatch($task->id, $pattern);

        } else {
            if ($task->a2a_status !== Config::get('a2a.status_map.awaiting_input') && $task->a2a_status !== 'input-required') {
                return response()->json([
                    'error' => 'Task is not currently awaiting input.',
                    'taskId' => $task->a2a_task_id,
                    'status' => $task->a2a_status,
                ], 409);
            }

            $a2aMeta = $task->a2a_meta ?? ['messages' => [], 'artifacts' => []];
            $a2aMeta['messages'][] = $message;
            $task->a2a_meta = $a2aMeta;
            $task->status = 'running';
            $task->a2a_status = Config::get('a2a.status_map.running');
            $task->a2a_last_message_sequence = ($task->a2a_last_message_sequence ?? 0) + 1;
            $task->save();
        }

        return response()->json($this->formatA2aTask($task), $isNewTask ? 202 : 200);
    }

    /**
     * Formats an internal Task model into the A2A Task JSON structure.
     */
    protected function formatA2aTask(Task $task): array
    {
        $a2aMeta = $task->a2a_meta ?? ['messages' => [], 'artifacts' => []];
        $messages = collect($a2aMeta['messages'] ?? []);
        $lastAgentMessage = $messages->where('role', 'agent')->sortByDesc('sequenceId')->first();
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
            'lastAgentMessage' => $lastAgentMessage,
            'artifacts' => $artifacts,
            'createdAt' => $task->created_at?->toIso8601String(),
            'updatedAt' => $task->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Simple Task Interpreter/Pattern Matcher.
     */
    protected function matchPattern(string $userInput): string
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

        return 'planner';
    }
}
