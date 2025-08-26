<?php

namespace LarAgent\Core\Contracts;

use LarAgent\Core\Contracts\Tool as ToolInterface;
use LarAgent\Core\Contracts\ToolCall as ToolCallInterface;
use LarAgent\Messages\AssistantMessage;

interface LlmDriver
{
    /**
     * Send a message or prompt to the LLM and receive a response.
     *
     * @param  array  $messages  Array of messages in the format:
     *                           ['role' => 'user|system|assistant', 'content' => '...']
     * @param  array  $options  Additional options like temperature, max_tokens, etc.
     * @return AssistantMessage The response from the LLM in a structured format.
     */
    public function sendMessage(array $messages, array $options = []): AssistantMessage;

    /**
     * Register a tool for the LLM to use.
     *
     * @param  ToolInterface  $tool  The tool instance.
     */
    public function registerTool(ToolInterface $tool): self;

    /**
     * Get all registered tools.
     *
     * @return array Array of registered tools keyed by their names.
     */
    public function getRegisteredTools(): array;

    /**
     * Get registered tool by name.
     *
     * @return ToolInterface registered tool by name.
     */
    public function getTool(string $name): ToolInterface;

    /**
     * Set a schema for structured output.
     *
     * @param  array  $schema  JSON Schema defining the expected output structure.
     */
    public function setResponseSchema(array $schema): self;

    /**
     * Get the current response schema.
     *
     * @return array|null The current response schema or null if not set.
     */
    public function getResponseSchema(): ?array;

    /**
     * Set configuration parameters for the LLM.
     *
     * @param  array  $config  Configuration options (e.g., temperature, model).
     */
    public function setConfig(array $config): self;

    /**
     * Get the current configuration parameters.
     *
     * @return array The current configuration options.
     */
    public function getConfig(): array;

    /**
     * Retrieve the last response from the LLM.
     *
     * @return array|null The last response or null if no response exists.
     */
    public function getLastResponse(): ?array;

    /**
     * Send a message or prompt to the LLM and receive a streamed response.
     *
     * @param  array  $messages  Array of messages in the format:
     *                           ['role' => 'user|system|assistant', 'content' => '...']
     * @param  array  $options  Additional options like temperature, max_tokens, etc.
     * @param  callable|null  $callback  Optional callback function to process each chunk of the stream
     * @return \Generator A generator that yields chunks of the response
     */
    public function sendMessageStreamed(array $messages, array $options = [], ?callable $callback = null): \Generator;

    /**
     * Get the provider data merged with the model defined settings.
     * Model settings override provider settings.
     *
     * @return array The settings.
     */
    public function getSettings(): array;

    public function structuredOutputEnabled(): bool;

    public function toolResultToMessage(ToolCallInterface $toolCall, mixed $result): array;

    public function toolCallsToMessage(array $toolCalls): array;
}
