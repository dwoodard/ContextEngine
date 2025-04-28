<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Prism\Prism\Prism;
use Prism\Prism\Text\PendingRequest;
use Prism\Relay\Facades\Relay;

use function Laravel\Prompts\note;
use function Laravel\Prompts\textarea;

class MCP extends Command
{
    protected $signature = 'prism:mcp';

    public function handle()
    {
        $response = $this->agent(textarea('Prompt'))->asText();

        note($response->text);
    }

    protected function agent(string $prompt): PendingRequest
    {
        return Prism::text()
            ->using('ollama', 'llama3.2:latest')
            ->withSystemPrompt(view('prompts.nova-v2'))
            ->withPrompt($prompt)
            ->withTools([
                ...Relay::tools('puppeteer'),
            ])
            ->usingTopP(1)
            ->withMaxSteps(99)
            ->withMaxTokens(8192);
    }
}
