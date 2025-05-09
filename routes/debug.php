<?php

use App\Models\Task;
use App\Services\MCP;
use App\Services\Patterns\DebateAgent;
use App\Services\Patterns\SelfReflectorAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

Route::get('/debug', function () {
    $markdown = File::get(resource_path('views/prompts/system.md'));

    $response = Prism::text()
        ->using(Provider::Ollama, 'llama3.2:latest')
        ->withSystemPrompt((new \Parsedown)->text($markdown))
        ->withPrompt('Explain quantum computing to a 5-year-old.')
        ->asText();

    return $response->text;

});

Route::get('/debug-debate', function () {
    // Create a mock Task instance
    $taska = new Task;
    // $task->input = 'Is artificial intelligence beneficial to humanity?';
    // $task->input = 'Is artificial intelligence beneficial to humanity?';
    $taska->input = 'tell me a quick story about a cat';

    dump(
        $taska->input,
        $taska->meta,
        $taska->result
    );

    // Instantiate and execute the DebateAgent
    $debateAgent = new DebateAgent;
    $debateAgent->execute($taska);

    dump(
        $taska->meta,
        $taska->result
    );

    $taskb = new Task;
    $taskb->input = $taska->input;
    dump(
        $taskb->meta,
        $taskb->result
    );
    (new SelfReflectorAgent)->execute($taskb);

    // Return the task result
    return response()->json([
        'meta' => $taskb->meta,
        'result' => $taskb->result,
    ]);
});

// debug/stream
Route::get('/debug/stream/{prompt}', function (Request $request, $prompt) {

    // just call it instead:
    $httpClient = new \Illuminate\Support\Facades\Http;
    $response = $httpClient::post('http://localhost:11434/api/generate', [
        'model' => 'llama3.2:latest',
        'prompt' => 'Why is the sky blue?',
        'stream' => true,
    ]);

    // Process each chunk as it arrives
    foreach ($response as $chunk) {
        dump(
            $chunk,

        );

        // Flush the output buffer to send text to the browser immediately
        ob_flush();
        flush();
    }

    return response()->json([
        'message' => 'Streaming complete.',
    ]);
});

// lets add mcp example
Route::get('/debug/mcp/puppeteer', function (Request $request) {
    $response = Prism::text()
        ->using(Provider::Ollama, 'llama3.2:latest')
        ->withStringParameter('url', 'laravel.com/docs/12.x')
        ->withTools([
            ...Relay::tools('puppeteer'),
            // Relay::tools('time'),
        ])
        ->asText();

    dd(
        $response->text,
    );

    return $response->text;
});
