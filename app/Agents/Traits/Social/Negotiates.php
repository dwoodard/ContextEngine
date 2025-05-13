<?php

namespace App\Agents\Traits\Social;

trait Negotiates
{
    public function negotiate(string $topic): string
    {
        // Example implementation
        return 'Negotiated on topic: '.$topic;
    }
}
