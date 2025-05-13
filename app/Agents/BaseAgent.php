<?php

namespace App\Agents;

use App\Agents\Contracts\AgentInterface;

abstract class BaseAgent implements AgentInterface
{
    protected string $name = '';

    protected string $description = '';

    protected string $url = '';

    protected array $capabilities = [];

    protected array $modalities = [];

    protected array $authentication = [];

    protected array $skills = [];

    public function name(): string
    {
        return $this->name ?: class_basename(static::class);
    }

    public function description(): string
    {
        return $this->description ?: 'No description provided.';
    }

    public function handle(string $input): array
    {
        return [
            'response' => $this->respond($input),
        ];
    }

    /**
     * Each concrete agent must implement this logic.
     */
    abstract protected function respond(string $input): string;

    public function handleTask(array $task): array
    {
        // Default implementation; override in concrete agents
        return ['status' => 'Task handled'];
    }

    public function getStatus(string $taskId): string
    {
        // Default implementation; override in concrete agents
        return 'Unknown';
    }

    public function getResults(string $taskId): mixed
    {
        // Default implementation; override in concrete agents
        return null;
    }

    public function getName(): string
    {
        return $this->name();
    }

    public function getDescription(): string
    {
        return $this->description();
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getCapabilities(): array
    {
        return $this->capabilities;
    }

    public function getSupportedModalities(): array
    {
        return $this->modalities;
    }

    public function getAuthentication(): array
    {
        return $this->authentication;
    }

    public function getSkills(): array
    {
        return $this->skills;
    }

    /**
     * Generates the Agent Card as per A2A specification.
     */
    public function getAgentCard(): array
    {
        return [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'url' => $this->getUrl(),
            'capabilities' => $this->getCapabilities(),
            'modalities' => $this->getSupportedModalities(),
            'authentication' => $this->getAuthentication(),
            'skills' => $this->getSkills(),
        ];
    }
}
