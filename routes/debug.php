<?php

use Illuminate\Support\Facades\Route;
use Parsedown;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

Route::get('/debug', function () {
    $markdown = File::get(resource_path('views/prompts/system.md'));

    $response = Prism::text()
        ->using(Provider::Ollama, 'llama3.2:latest')
        ->withSystemPrompt((new Parsedown)->text($markdown))
        ->withPrompt('Explain quantum computing to a 5-year-old.')
        ->asText();

    return $response->text;

});
