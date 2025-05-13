<?php

namespace App\Agents;

use App\Agents\Contracts\AgentInterface;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class RouterAgent implements AgentInterface
{
    public function name(): string
    {
        return 'RouterAgent';
    }

    public function description(): string
    {
        return 'Coordinates actions among multiple agents.';
    }

    public function handle(string $input): array
    {
        dump(['RouterAgent', $input]);

        $response = Prism::text()
            ->using(Provider::Ollama, 'llama3.2:latest')
            ->withMaxSteps(999)
            ->withPrompt($input)

            ->asText();

        dump($response);

        return [
            'response' => $response,
        ];
    }
}
