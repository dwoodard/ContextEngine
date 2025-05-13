<?php

namespace App\Agents\Traits\Capabilities;

trait HandlesMemory
{
    private array $memory = [];

    public function remember(string $key, $value): void
    {
        $this->memory[$key] = $value;
    }

    public function recall(string $key)
    {
        return $this->memory[$key] ?? null;
    }
}
