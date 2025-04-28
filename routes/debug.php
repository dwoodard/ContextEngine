<?php

use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\Route;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

Route::get('/debug', function () {
    $response = Prism::text()
        ->using(Provider::Ollama, 'llama3.2:latest')
        ->withSystemPrompt('You are an expert mathematician who explains concepts simply.')
        ->withPrompt('Explain the Pythagorean theorem.')
        ->asText();

    echo Markdown::parse($response->text);
});
