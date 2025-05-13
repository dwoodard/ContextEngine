<?php

namespace App\Agents\Traits\System;

trait AdaptsPersona
{
    public function adaptPersona(string $persona): string
    {
        // Example implementation
        return 'Adapted to persona: '.$persona;
    }
}
