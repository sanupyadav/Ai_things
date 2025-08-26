<?php

namespace LarAgent\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AgentChatCommand extends Command
{
    protected $signature = 'agent:chat {agent : The name of the agent to chat with} {--history= : Chat history name}';

    protected $description = 'Start an interactive chat session with an agent';

    public function handle()
    {
        $agentName = $this->argument('agent');
        $historyName = $this->option('history') ?? Str::random(10);

        // Try both namespaces
        $agentClass = $this->findAgentClass($agentName);
        if (! $agentClass) {
            $this->error("Agent not found: {$agentName}");

            return 1;
        }

        $agent = $agentClass::for($historyName);

        $this->info("Starting chat with {$agentName}");
        $this->line("Using history: {$historyName}");
        $this->line("Type 'exit' to end the chat\n");

        while (true) {
            $message = $this->ask('You');

            if ($message === null || strtolower($message) === 'exit') {
                $this->info('Chat ended');

                return 0;
            }

            try {
                $response = $agent->respond($message);
                $this->line("\n<comment>{$agentName}:</comment>");
                $this->formatResponse($response);
                $this->line("\n");
            } catch (\Exception $e) {
                $this->error('Error: '.$e->getMessage());

                return 1;
            }
        }
    }

    protected function findAgentClass(string $agentName): ?string
    {
        $namespaces = config('laragent.namespaces');

        foreach ($namespaces as $namespace) {
            $fqcn = $namespace.$agentName;
            if (class_exists($fqcn)) {
                return $fqcn;
            }
        }

        return null;
    }

    /**
     * Format and display the agent's response
     *
     * @param  mixed  $response
     */
    protected function formatResponse($response): void
    {
        if (is_array($response)) {
            // Check if it's a single array with a key containing a list
            if (count($response) === 1 && isset(array_values($response)[0]) && is_array(array_values($response)[0])) {
                $key = array_key_first($response);
                $values = array_values($response)[0];

                if (array_is_list($values)) {
                    // If the first item is an object/array, use its keys as headers
                    if (! empty($values) && is_array($values[0])) {
                        $headers = array_keys((array) $values[0]);
                        $rows = array_map(function ($item) {
                            return array_map(function ($value) {
                                return is_array($value) ? json_encode($value) : (string) $value;
                            }, (array) $item);
                        }, $values);
                        $this->info($key.':');
                        $this->table($headers, $rows);
                    } else {
                        // For simple arrays, show numbered list
                        $this->info($key.':');
                        $this->table(['#', $key], array_map(fn ($i, $item) => [$i + 1, $item], array_keys($values), $values));
                    }

                    return;
                }
            }

            // Otherwise format as JSON with proper indentation
            $this->line(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $this->line($response);
        }
    }
}
