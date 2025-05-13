<?php

namespace App\Agents\Contracts;

interface AgentCardInterface
{
    public function getTitle(): string;

    public function getDescription(): string;

    public function getActions(): array;
}
