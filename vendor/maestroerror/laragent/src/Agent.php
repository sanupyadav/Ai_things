<?php

namespace LarAgent;

use LarAgent\Core\DTO\AgentDTO;
use LarAgent\Core\Traits\Events;
use LarAgent\Messages\UserMessage;
use LarAgent\Messages\ToolCallMessage;
use LarAgent\Attributes\Tool as ToolAttribute;
use Illuminate\Contracts\Auth\Authenticatable;
use LarAgent\Core\Contracts\Tool as ToolInterface;
use LarAgent\Messages\StreamedAssistantMessage;
use LarAgent\Core\Contracts\Message as MessageInterface;
use LarAgent\Core\Contracts\LlmDriver as LlmDriverInterface;
use LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;

/**
 * Class Agent
 * For creating Ai Agent by extending this class
 * Only class dependant on Laravel
 */
class Agent
{
    use Events;

    // Agent properties

    protected LarAgent $agent;

    protected LlmDriverInterface $llmDriver;

    /**
     * When true respond() will return MessageInterface instance instead of
     * casting it to string or array.
     */
    protected bool $returnMessage = false;

    protected ChatHistoryInterface $chatHistory;

    /** @var string|null */
    protected $message;

    /** @var UserMessage|null - ready made message to send to the agent */
    protected $readyMessage = null;

    /** @var string */
    protected $instructions;

    /** @var array */
    protected $responseSchema = [];

    /** @var array */
    protected $tools = [];

    /** @var string */
    protected $history;

    /** @var string */
    protected $driver;

    /** @var string */
    protected $provider = 'default';

    /** @var string */
    protected $providerName = '';

    /** @var bool */
    protected $developerRoleForInstructions = false;

    // Driver configs

    /** @var string */
    protected $model;

    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $apiUrl;

    /** @var int */
    protected $contextWindowSize;

    /**
     * Store message metadata with messages in chat history
     *
     * @var bool
     */
    protected $storeMeta;

    /** @var bool */
    protected $saveChatKeys;

    /**
     * Chat key associated with this agent
     *
     * @var string
     */
    protected $chatKey;

    /**
     * Include model name in chat session ID
     *
     * @var bool
     */
    protected $includeModelInChatSessionId = false;

    /** @var int */
    protected $maxCompletionTokens;

    /** @var float */
    protected $temperature;

    /** @var int */
    protected $reinjectInstructionsPer;

    /** @var ?bool */
    protected $parallelToolCalls;

    /** @var string|array|null */
    protected $toolChoice;

    /** @var int|null */
    protected $n;

    /** @var float|null */
    protected $topP;

    /** @var float|null */
    protected $frequencyPenalty;

    /** @var float|null */
    protected $presencePenalty;

    /** @var string */
    protected $chatSessionId;

    // Misc
    private array $builtInHistories = [
        'in_memory' => \LarAgent\History\InMemoryChatHistory::class,
        'session' => \LarAgent\History\SessionChatHistory::class,
        'cache' => \LarAgent\History\CacheChatHistory::class,
        'file' => \LarAgent\History\FileChatHistory::class,
        'json' => \LarAgent\History\JsonChatHistory::class,
    ];

    /** @var array */
    protected $images = [];

    /** @var array|null */
    protected $audioFiles = null;

    /** @var array */
    protected $modalities = [];

    /** @var array|null */
    protected $audio = null;

    public function __construct($key)
    {
        $this->setupProviderData();
        $this->setChatSessionId($key);
        $this->setupChatHistory();
        $this->onInitialize();
    }

    public function __destruct()
    {
        $this->onTerminate();
    }

    // Public API

    /**
     * Create an agent instance for a specific user
     *
     * @param  Authenticatable  $user  The user to create agent for
     */
    public static function forUser(Authenticatable $user): static
    {
        $userId = $user->getAuthIdentifier();
        $instance = new static($userId);

        return $instance;
    }

    /**
     * Create an agent instance with a specific key
     *
     * @param  string  $key  The key to identify this agent instance
     */
    public static function for(string $key): static
    {
        $instance = new static($key);

        return $instance;
    }

    /**
     * Set the message for the agent to process
     *
     * @param  string|MessageInterface  $message  The message to process
     */
    public function message(string|MessageInterface $message): static
    {
        if ($message instanceof MessageInterface) {
            $this->readyMessage = $message;
        } else {
            $this->message = $message;
        }

        return $this;
    }

    /**
     * Process a message and get the agent's response
     *
     * @param  string|null  $message  Optional message to process
     * @return string|array|MessageInterface The agent's response
     */
    public function respond(?string $message = null): string|array|MessageInterface
    {
        if ($message) {
            $this->message($message);
        }

        $this->setupBeforeRespond();

        $this->onConversationStart();

        $message = $this->prepareMessage();

        $this->prepareAgent($message);

        try {
            if ($this->returnMessage) {
                $this->agent->setReturnMessage(true);
            }
            $response = $this->agent->run();
        } catch (\Throwable $th) {
            $this->onEngineError($th);
            // Run fallback provider
            $fallbackProvider = config('laragent.fallback_provider');

            // Throw error if there is no fallback provider
            if (! $fallbackProvider) {
                throw $th;
            }

            // Throw error if fallback provider is same as current provider
            if ($fallbackProvider === $this->provider) {
                throw $th;
            } else {
                // Run fallback provider
                $this->changeProvider($fallbackProvider);

                return $this->respond();
            }
        }

        $this->onConversationEnd($response);

        if ($this->returnMessage) {
            return $response;
        }

        if ($response instanceof ToolCallMessage) {
            return $response->toArrayWithMeta();
        }

        if (is_array($response)) {
            return $response;
        }

        return (string) $response;
    }

    protected function changeProvider(string $provider)
    {
        $this->provider = $provider;
        $config = $this->getProviderData();
        $this->apiKey = $config['api_key'] ?? null;
        $this->apiUrl = $config['api_url'] ?? null;
        $this->model = $config['model'] ?? null;
        $this->driver = $config['driver'] ?? null;
        $this->setupProviderData();
    }

    /**
     * Process a message and get the agent's response as a stream
     *
     * @param  string|null  $message  Optional message to process
     * @param  callable|null  $callback  Optional callback to process each chunk
     * @return \Generator A stream of response chunks
     */
    public function respondStreamed(?string $message = null, ?callable $callback = null): \Generator
    {
        if ($message) {
            $this->message($message);
        }

        $this->setupBeforeRespond();

        $this->onConversationStart();

        $message = $this->prepareMessage();

        $this->prepareAgent($message);

        $instance = $this;

        $generator = (function () use ($instance, $message, $callback) {
            try {
                // Run the agent with streaming enabled
                if ($instance->returnMessage) {
                    $instance->agent->setReturnMessage(true);
                }
                $stream = $instance->agent->runStreamed(function ($streamedMessage) use ($callback, $instance) {
                    if ($streamedMessage instanceof StreamedAssistantMessage) {
                        // Call onConversationEnd when the stream message is complete
                        if ($streamedMessage->isComplete()) {
                            $instance->onConversationEnd($streamedMessage);
                        }
                    }

                    // Run callback if defined
                    if ($callback) {
                        $callback($streamedMessage);
                    }
                });

                foreach ($stream as $chunk) {
                    yield $chunk;
                }
            } catch (\Throwable $th) {
                $instance->onEngineError($th);
                $fallbackProvider = config('laragent.fallback_provider');

                if (! $fallbackProvider || $fallbackProvider === $instance->provider) {
                    throw $th;
                }

                $instance->changeProvider($fallbackProvider);

                $fallbackStream = $instance->respondStreamed($message, $callback);

                foreach ($fallbackStream as $chunk) {
                    yield $chunk;
                }
            }
        })();

        return $generator;
    }

    /**
     * Process a message and get the agent's response as a streamable response
     * for Laravel applications
     *
     * @param  string|null  $message  Optional message to process
     * @param  string  $format  Response format: 'plain', 'json', or 'sse'
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function streamResponse(?string $message = null, string $format = 'plain')
    {
        $contentType = match ($format) {
            'json' => 'application/json',
            'sse' => 'text/event-stream',
            default => 'text/plain',
        };

        return response()->stream(function () use ($message, $format) {
            $accumulated = '';
            $stream = $this->respondStreamed($message, function ($chunk) use (&$accumulated, $format) {
                if ($chunk instanceof \LarAgent\Messages\StreamedAssistantMessage) {
                    $delta = $chunk->getLastChunk();
                    $accumulated .= $delta;

                    if ($format === 'plain') {
                        echo $delta;
                    } elseif ($format === 'json') {
                        echo json_encode([
                            'delta' => $delta,
                            'content' => $chunk->getContent(),
                            'complete' => $chunk->isComplete(),
                        ])."\n";
                    } elseif ($format === 'sse') {
                        echo "event: chunk\n";
                        echo 'data: '.json_encode([
                            'delta' => $delta,
                            'content' => $chunk->getContent(),
                            'complete' => $chunk->isComplete(),
                        ])."\n\n";
                    }

                    ob_flush();
                    flush();
                } elseif (is_array($chunk)) {
                    // Handle structured output (JSON schema response)
                    if ($format === 'plain') {
                        echo json_encode($chunk, JSON_PRETTY_PRINT);
                    } elseif ($format === 'json') {
                        echo json_encode([
                            'type' => 'structured',
                            'delta' => '',
                            'content' => $chunk,
                            'complete' => true,
                        ])."\n";
                    } elseif ($format === 'sse') {
                        echo "event: structured\n";
                        echo 'data: '.json_encode([
                            'type' => 'structured',
                            'delta' => '',
                            'content' => $chunk,
                            'complete' => true,
                        ])."\n\n";
                    }

                    ob_flush();
                    flush();
                }
            });

            // Consume the stream
            foreach ($stream as $_) {
                // The callback handles the output
            }

            // Signal completion
            if ($format === 'sse') {
                echo "event: complete\n";
                echo 'data: '.json_encode(['content' => $accumulated])."\n\n";
                ob_flush();
                flush();
            }
        }, 200, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    // Overridables

    /**
     * Get the instructions for the agent
     *
     * @return string The agent's instructions
     */
    public function instructions()
    {
        return $this->instructions;
    }

    /**
     * Get the model for the agent
     *
     * @return string The agent's model
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * Dynamically set the API Key for the driver
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Dynamically set the API URL for the driver
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * Process a message before sending to the agent
     *
     * @param  string  $message  The message to process
     * @return string The processed message
     */
    public function prompt(string $message)
    {
        return $message;
    }

    /**
     * Get the structured output schema if any
     *
     * @return array|null The response schema or null if none set
     */
    public function structuredOutput()
    {
        return $this->responseSchema ?? null;
    }

    /**
     * Create a new chat history instance
     *
     * @param  string  $sessionId  The session ID for the chat history
     * @return ChatHistoryInterface The created chat history instance
     */
    public function createChatHistory(string $sessionId)
    {
        $historyClass = $this->builtInHistories[$this->history] ?? $this->history;

        return new $historyClass($sessionId, [
            'context_window' => $this->contextWindowSize,
            'store_meta' => $this->storeMeta,
            'save_chat_keys' => $this->saveChatKeys,
        ]);
    }

    /**
     * Register additional tools for the agent
     *
     * Override this method in child classes to register custom tools.
     * Tools should be instances of LarAgent\Tool class.
     *
     * Example:
     * ```php
     * public function registerTools() {
     *     return [
     *         Tool::create("user_location", "Returns user's current location")
     *              ->setCallback(function () use ($user) {
     *                   return $user->location()->city;
     *              }),
     *         Tool::create("get_current_weather", "Returns the current weather in a given location")
     *              ->addProperty("location", "string", "The city and state, e.g. San Francisco, CA")
     *              ->setCallback("getWeather"),
     *     ];
     * }
     * ```
     *
     * @return array Array of Tool instances
     */
    public function registerTools()
    {
        return [];
    }

    // Public accessors / mutators

    public function getChatSessionId(): string
    {
        return $this->chatSessionId;
    }

    public function keyIncludesModelName(): bool
    {
        return $this->includeModelInChatSessionId;
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function getTools(): array
    {
        // Get tools from $tools property (class names)
        $classTools = array_map(function ($tool) {
            if (is_string($tool) && class_exists($tool)) {
                return new $tool;
            }

            return $tool;
        }, $this->tools);

        // Get tools from registerTools method (instances)
        $registeredTools = $this->registerTools();

        $attributeTools = $this->buildToolsFromAttributeMethods();

        // Merge both arrays
        return array_merge($classTools, $registeredTools, $attributeTools);
    }

    public function chatHistory(): ChatHistoryInterface
    {
        return $this->chatHistory;
    }

    public function setChatHistory(ChatHistoryInterface $chatHistory): static
    {
        $this->chatHistory = $chatHistory;

        return $this;
    }

    /**
     * Configure respond() to return MessageInterface instance.
     */
    public function returnMessage(bool $return = true): static
    {
        $this->returnMessage = $return;

        return $this;
    }

    public function currentMessage(): ?string
    {
        return $this->message;
    }

    public function lastMessage(): ?MessageInterface
    {
        return $this->chatHistory->getLastMessage();
    }

    public function clear(): static
    {
        $this->onClear();
        $this->chatHistory->clear();
        $this->chatHistory->writeToMemory();

        return $this;
    }

    public function getChatKey(): string
    {
        return $this->chatKey;
    }

    /**
     * Get all chat keys associated with this agent class
     *
     * @return array Array of chat keys filtered by agent class name
     */
    public function getChatKeys(): array
    {
        $keys = $this->chatHistory->loadKeysFromMemory();
        $agentClass = class_basename(static::class);

        return array_filter($keys, function ($key) use ($agentClass) {
            return str_starts_with($key, $agentClass.'_');
        });
    }

    public function getModalities(): array
    {
        return $this->modalities;
    }

    public function getAudio(): ?array
    {
        return $this->audio;
    }

    public function withTool(string|ToolInterface $tool): static
    {
        if (is_string($tool) && class_exists($tool)) {
            $tool = new $tool;
        }
        $this->tools[] = $tool;
        $this->onToolChange($tool, true);

        return $this;
    }

    public function removeTool(string|ToolInterface $tool): static
    {
        $toolName = $this->getToolName($tool);

        $this->tools = array_filter($this->tools, function ($existingTool) use ($toolName) {
            if ($existingTool->getName() !== $toolName) {
                return true;
            }
            $this->onToolChange($existingTool, false);

            return false;
        });

        return $this;
    }

    private function getToolName(string|ToolInterface $tool): string
    {
        if (is_string($tool)) {
            return class_exists($tool) ? (new $tool)->getName() : $tool;
        }

        return $tool->getName();
    }

    public function withImages(array $imageUrls): static
    {
        $this->images = $imageUrls;

        return $this;
    }

    public function withAudios(array $audioStrings): static
    {
        // ['data' => 'base64', 'format' => 'wav']
        // Possible formats: "wav", "mp3", "ogg", "flac", "m4a", "webm"
        $this->audioFiles = $audioStrings;

        return $this;
    }

    // Possible formats: "wav", "mp3", "ogg", "flac", "m4a", "webm"
    public function generateAudio(string $format, string $voice): static
    {
        $this->audio = ['format' => $format, 'voice' => $voice];
        $this->modalities = ['text', 'audio'];

        return $this;
    }

    public function temperature(float $temp): static
    {
        $this->temperature = $temp;

        return $this;
    }

    public function n(int $n): static
    {
        $this->n = $n;

        return $this;
    }

    public function topP(float $topP): static
    {
        $this->topP = $topP;

        return $this;
    }

    public function frequencyPenalty(float $penalty): static
    {
        $this->frequencyPenalty = $penalty;

        return $this;
    }

    public function presencePenalty(float $penalty): static
    {
        $this->presencePenalty = $penalty;

        return $this;
    }

    public function maxCompletionTokens(int $tokens): static
    {
        $this->maxCompletionTokens = $tokens;

        return $this;
    }

    public function parallelToolCalls(?bool $parallel): static
    {
        $this->parallelToolCalls = $parallel;

        return $this;
    }

    public function responseSchema(?array $schema): static
    {
        $this->responseSchema = $schema;

        return $this;
    }

    /**
     * Set tool choice to 'auto' - model can choose to use zero, one, or multiple tools.
     * Only applies if tools are registered.
     */
    public function toolAuto(): static
    {
        $this->toolChoice = 'auto';

        return $this;
    }

    /**
     * Set tool choice to 'none' - prevent the model from using any tools.
     * This simulates the behavior of not passing any functions.
     */
    public function toolNone(): static
    {
        $this->toolChoice = 'none';

        return $this;
    }

    /**
     * Set tool choice to 'required' - model must use at least one tool.
     * Only applies if tools are registered.
     */
    public function toolRequired(): static
    {
        $this->toolChoice = 'required';

        return $this;
    }

    /**
     * Force the model to use a specific tool.
     * Only applies if the specified tool is registered.
     */
    public function forceTool(string $toolName): static
    {
        $this->toolChoice = [
            'type' => 'function',
            'function' => [
                'name' => $toolName,
            ],
        ];

        return $this;
    }

    /**
     * Get the current tool choice configuration.
     * Returns null if no tools are registered or tool choice is not set.
     */
    public function getToolChoice()
    {
        if (empty($this->tools) || $this->toolChoice === null) {
            return null;
        }

        if ($this->toolChoice === 'none') {
            return 'none';
        }

        return $this->toolChoice;
    }

    public function withModel(string $model): static
    {
        $this->model = $model;

        if ($this->keyIncludesModelName()) {
            $this->refreshChatHistory();
        }

        return $this;
    }

    protected function refreshChatHistory(): void
    {
        // Update chat session ID with new model
        $this->setChatSessionId($this->getChatKey());

        // Create new chat history with updated session ID
        $this->setupChatHistory();
    }

    public function withoutModelInChatSessionId(): static
    {
        $this->includeModelInChatSessionId = false;

        return $this;
    }

    public function withModelInChatSessionId(): static
    {
        $this->includeModelInChatSessionId = true;
        $this->refreshChatHistory();

        return $this;
    }

    public function addMessage(MessageInterface $message): static
    {
        $this->chatHistory()->addMessage($message);

        return $this;
    }

    /**
     * Convert Agent to DTO
     * // @todo mention DTO in the documentation as state for events
     */
    public function toDTO(): AgentDTO
    {
        $driverConfigs = array_filter([
            'model' => $this->model(),
            'contextWindowSize' => $this->contextWindowSize ?? null,
            'maxCompletionTokens' => $this->maxCompletionTokens ?? null,
            'temperature' => $this->temperature ?? null,
            'n' => $this->n ?? null,
            'topP' => $this->topP ?? null,
            'frequencyPenalty' => $this->frequencyPenalty ?? null,
            'presencePenalty' => $this->presencePenalty ?? null,
            'reinjectInstructionsPer' => $this->reinjectInstructionsPer ?? null,
            'parallelToolCalls' => $this->parallelToolCalls ?? null,
            'chatSessionId' => $this->chatSessionId,
        ], fn ($value) => ! is_null($value));

        return new AgentDTO(
            provider: $this->provider,
            providerName: $this->providerName,
            message: $this->message,
            tools: array_map(fn (ToolInterface $tool) => $tool->getName(), $this->getTools()),
            instructions: $this->instructions,
            responseSchema: $this->responseSchema,
            configuration: [
                'history' => $this->history,
                'model' => $this->model(),
                'driver' => $this->driver,
                ...$driverConfigs,
            ]
        );
    }

    // Helper methods

    protected function setChatSessionId(string $id): static
    {
        $this->chatKey = $id;
        $this->chatSessionId = $this->buildSessionId();

        return $this;
    }

    protected function buildSessionId()
    {
        if ($this->keyIncludesModelName()) {
            return sprintf(
                '%s_%s_%s',
                class_basename(static::class),
                $this->model(),
                $this->getChatKey()
            );
        }

        return sprintf(
            '%s_%s',
            class_basename(static::class),
            $this->getChatKey()
        );
    }

    protected function getProviderData(): ?array
    {
        return config("laragent.providers.{$this->provider}");
    }

    protected function setupDriverConfigs(array $providerData): void
    {
        if (! isset($this->apiKey) && isset($providerData['api_key'])) {
            $this->apiKey = $providerData['api_key'];
        }
        if (! isset($this->apiUrl) && isset($providerData['api_url'])) {
            $this->apiUrl = $providerData['api_url'];
        }

        if (! isset($this->model) && isset($providerData['model'])) {
            $this->model = $providerData['model'];
        }
        if (! isset($this->maxCompletionTokens) && isset($providerData['default_max_completion_tokens'])) {
            $this->maxCompletionTokens = $providerData['default_max_completion_tokens'];
        }
        if (! isset($this->contextWindowSize) && isset($providerData['default_context_window'])) {
            $this->contextWindowSize = $providerData['default_context_window'];
        }
        if (! isset($this->storeMeta) && isset($providerData['store_meta'])) {
            $this->storeMeta = $providerData['store_meta'];
        }
        if (! isset($this->saveChatKeys) && isset($providerData['save_chat_keys'])) {
            $this->saveChatKeys = $providerData['save_chat_keys'];
        }
        if (! isset($this->temperature) && isset($providerData['default_temperature'])) {
            $this->temperature = $providerData['default_temperature'];
        }
        if (! isset($this->n) && isset($providerData['default_n'])) {
            $this->n = $providerData['default_n'];
        }
        if (! isset($this->topP) && isset($providerData['default_top_p'])) {
            $this->topP = $providerData['default_top_p'];
        }
        if (! isset($this->frequencyPenalty) && isset($providerData['default_frequency_penalty'])) {
            $this->frequencyPenalty = $providerData['default_frequency_penalty'];
        }
        if (! isset($this->presencePenalty) && isset($providerData['default_presence_penalty'])) {
            $this->presencePenalty = $providerData['default_presence_penalty'];
        }
        if (! isset($this->parallelToolCalls) && isset($providerData['parallel_tool_calls'])) {
            $this->parallelToolCalls = $providerData['parallel_tool_calls'];
        }
    }

    protected function initDriver($settings): void
    {
        $this->llmDriver = new $this->driver($settings);
    }

    protected function setupProviderData(): void
    {
        $provider = $this->getProviderData();
        if (! isset($this->driver)) {
            $this->driver = $provider['driver'] ?? config('laragent.default_driver');
        }
        if (! isset($this->history)) {
            $this->history = $provider['chat_history'] ?? config('laragent.default_chat_history');
        }
        $this->providerName = $provider['name'] ?? '';
        $this->setupDriverConfigs($provider);

        $settings = array_merge($provider, $this->buildConfigsFromAgent());
        $this->initDriver($settings);
    }

    protected function setupAgent(): void
    {
        $config = $this->buildConfigsFromAgent();
        $this->agent = LarAgent::setup($this->llmDriver, $this->chatHistory, $config);
    }

    /**
     * Build configuration array from agent properties.
     * Overrides provider data with agent properties.
     *
     * @return array The configuration array with model, API key, API URL, and optional parameters.
     */
    protected function buildConfigsFromAgent(): array
    {
        $config = [
            'model' => $this->model(),
            'api_key' => $this->getApiKey(),
            'api_url' => $this->getApiUrl(),
        ];
        if (property_exists($this, 'maxCompletionTokens')) {
            $config['maxCompletionTokens'] = $this->maxCompletionTokens;
        }
        if (property_exists($this, 'temperature')) {
            $config['temperature'] = $this->temperature;
        }
        if (property_exists($this, 'n')) {
            $config['n'] = $this->n;
        }
        if (property_exists($this, 'topP')) {
            $config['topP'] = $this->topP;
        }
        if (property_exists($this, 'frequencyPenalty')) {
            $config['frequencyPenalty'] = $this->frequencyPenalty;
        }
        if (property_exists($this, 'presencePenalty')) {
            $config['presencePenalty'] = $this->presencePenalty;
        }
        if (property_exists($this, 'parallelToolCalls')) {
            $config['parallelToolCalls'] = $this->parallelToolCalls;
        }
        if (property_exists($this, 'toolChoice')) {
            $config['toolChoice'] = $this->toolChoice;
        }

        if (! empty($this->modalities)) {
            $config['modalities'] = $this->modalities;
        }

        if (! empty($this->audio)) {
            $config['audio'] = $this->audio;
        }

        return $config;
    }

    protected function registerEvents(): void
    {
        $instance = $this;

        $this->agent->beforeReinjectingInstructions(function ($agent, $chatHistory) use ($instance) {
            $returnValue = $instance->beforeReinjectingInstructions($chatHistory);

            // Explicitly check for false
            return $returnValue === false ? false : true;
        });

        $this->agent->beforeSend(function ($agent, $history, $message) use ($instance) {
            $returnValue = $instance->beforeSend($history, $message);

            // Explicitly check for false
            return $returnValue === false ? false : true;
        });

        $this->agent->afterSend(function ($agent, $history, $message) use ($instance) {
            $returnValue = $instance->afterSend($history, $message);

            // Explicitly check for false
            return $returnValue === false ? false : true;
        });

        $this->agent->beforeSaveHistory(function ($agent, $history) use ($instance) {
            $returnValue = $instance->beforeSaveHistory($history);

            // Explicitly check for false
            return $returnValue === false ? false : true;
        });

        $this->agent->beforeResponse(function ($agent, $history, $message) use ($instance) {
            $returnValue = $instance->beforeResponse($history, $message);

            // Explicitly check for false
            return $returnValue === false ? false : true;
        });

        $this->agent->afterResponse(function ($agent, $message) use ($instance) {
            $returnValue = $instance->afterResponse($message);

            // Explicitly check for false
            return $returnValue === false ? false : true;
        });

        $this->agent->beforeToolExecution(function ($agent, $tool) use ($instance) {
            $returnValue = $instance->beforeToolExecution($tool);

            // Explicitly check for false
            return $returnValue === false ? false : true;
        });

        $this->agent->afterToolExecution(function ($agent, $tool, &$result) use ($instance) {
            $returnValue = $instance->afterToolExecution($tool, $result);

            // Explicitly check for false
            return $returnValue === false ? false : true;
        });

        $this->agent->beforeStructuredOutput(function ($agent, &$response) use ($instance) {
            $returnValue = $instance->beforeStructuredOutput($response);

            // Explicitly check for false
            return $returnValue === false ? false : true;
        });
    }

    protected function setupBeforeRespond(): void
    {
        $this->setupAgent();
        $this->registerEvents();
    }

    protected function setupChatHistory(): void
    {
        $chatHistory = $this->createChatHistory($this->getChatSessionId());
        $this->setChatHistory($chatHistory);
    }

    protected function prepareMessage(): MessageInterface
    {
        if ($this->readyMessage) {
            $message = $this->readyMessage;
        } else {
            $message = Message::user($this->prompt($this->message));
        }

        $message->addMeta([
            'agent' => basename(static::class),
            'model' => $this->model(),
        ]);

        if (! empty($this->images)) {
            foreach ($this->images as $imageUrl) {
                $message = $message->withImage($imageUrl);
            }
        }

        if (! empty($this->audioFiles)) {
            foreach ($this->audioFiles as $audioFile) {
                $message = $message->withAudio($audioFile['format'], $audioFile['data']);
            }
        }

        return $message;
    }

    protected function prepareAgent(MessageInterface $message): void
    {
        $this->agent
            ->withInstructions($this->instructions(), $this->developerRoleForInstructions)
            ->withMessage($message)
            ->setTools($this->getTools());

        if ($this->structuredOutput()) {
            $this->agent->structured($this->structuredOutput());
        }
    }

    /**
     * Builds tools from methods annotated with #[Tool] attribute
     * Example:
     * ```php
     * #[Tool("Get weather information")]
     * public function getWeather(string $location): array {
     *     return WeatherService::get($location);
     * }
     * ```
     */
    protected function buildToolsFromAttributeMethods(): array
    {
        $tools = [];
        $reflection = new \ReflectionClass($this);

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = $method->getAttributes(ToolAttribute::class);
            if (empty($attributes)) {
                continue;
            }

            foreach ($attributes as $attribute) {
                $toolAttribute = $attribute->newInstance();
                $tool = Tool::create(
                    $method->getName(),
                    $toolAttribute->description
                );

                // Add parameters as tool properties
                foreach ($method->getParameters() as $param) {
                    $type = $param->getType()?->getName() ?? 'string';
                    $AiType = $this->convertToOpenAIType($type);
                    $tool->addProperty(
                        $param->getName(),
                        isset($AiType['type']) ? $AiType['type'] : $AiType,
                        isset($toolAttribute->parameterDescriptions[$param->getName()]) ? $toolAttribute->parameterDescriptions[$param->getName()] : '',
                        isset($AiType['enum']) ? $AiType['enum'] : []
                    );
                    if (! $param->isOptional()) {
                        $tool->setRequired($param->getName());
                    }
                }

                $instance = $this;
                // Bind the method to the tool, handling both static and instance methods
                $tool->setCallback($method->isStatic()
                    ? [static::class, $method->getName()]
                    : [$this, $method->getName()]
                );
                $tools[] = $tool;
            }
        }

        return $tools;
    }

    protected function convertToOpenAIType($type)
    {

        if ($type instanceof \ReflectionEnum || (is_string($type) && enum_exists($type))) {
            $enumClass = is_string($type) ? $type : $type->getName();

            return [
                'type' => 'string',
                'enum' => [
                    'values' => array_map(fn ($case) => $case->value, $enumClass::cases()),
                    'enumClass' => $enumClass, // Store the enum class name for conversion
                ],
            ];
        }

        switch ($type) {
            case 'string':
                return 'string';
            case 'int':
                return 'integer';
            case 'float':
                return 'number';
            case 'bool':
                return 'boolean';
            case 'array':
                return 'array';
            case 'object':
                return 'object';
            default:
                return 'string';
        }
    }
}
