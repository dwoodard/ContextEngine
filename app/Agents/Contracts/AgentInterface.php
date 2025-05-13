<?php

namespace App\Agents\Contracts;

interface AgentInterface
{
    // --- Core behavior for A2A task lifecycle ---

    /**
     * Handle a given task and return the result as an array.
     *
     * @param  array  $task  The task to be handled.
     * @return array The result of the task.
     */
    public function handleTask(array $task): array;

    /**
     * Get the status of a task by its ID.
     *
     * @param  string  $taskId  The ID of the task.
     * @return string The status of the task.
     */
    public function getStatus(string $taskId): string;

    /**
     * Get the results of a task by its ID.
     *
     * @param  string  $taskId  The ID of the task.
     * @return mixed The results of the task.
     */
    public function getResults(string $taskId): mixed;

    // --- Metadata for AgentCard ---

    /**
     * Get the name of the agent.
     *
     * @return string The name of the agent.
     */
    public function getName(): string;

    /**
     * Get the description of the agent.
     *
     * @return string The description of the agent.
     */
    public function getDescription(): string;

    /**
     * Get the URL of the agent.
     * should be the full URL to the agent's API endpoint.
     *
     * @return string The URL of the agent.
     */
    public function getUrl(): string;

    /**
     * Get the capabilities of the agent.
     *
     * @return array The capabilities of the agent.
     */
    public function getCapabilities(): array;

    /**
     * Get the supported modalities for the agent.
     * such as text, image, audio, etc.
     */
    public function getSupportedModalities(): array;

    /**
     * Get the authentication requirements for the agent.
     */
    public function getAuthentication(): array;

    /**
     * The skills of the agent, which can be used to filter agents
     **/
    public function getSkills(): array;
}
