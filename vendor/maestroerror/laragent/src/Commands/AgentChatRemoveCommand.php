<?php

namespace LarAgent\Commands;

use Illuminate\Console\Command;

class AgentChatRemoveCommand extends Command
{
    protected $signature = 'agent:chat:remove {agent : The name of the agent to remove chat history for}';

    protected $description = 'Remove chat history for a specific agent';

    public function handle()
    {
        $agentName = $this->argument('agent');

        // Try both namespaces
        $agentClass = "\\App\\AiAgents\\{$agentName}";
        if (! class_exists($agentClass)) {
            $agentClass = "\\App\\Agents\\{$agentName}";
            if (! class_exists($agentClass)) {
                $this->error("Agent not found: {$agentName}");

                return 1;
            }
        }

        // Create a temporary instance to get chat keys
        $agent = $agentClass::for('temp');
        $chatKeys = $agent->getChatKeys();

        if (! empty($chatKeys)) {
            // Remove each chat history
            $this->info('Found '.count($chatKeys).' chat histories to remove...');

            foreach ($chatKeys as $key) {
                $this->line("Removing chat history: {$key}");
                $agent->chatHistory()->removeChatFromMemory($key);
            }

            $this->info("Successfully removed all chat histories for agent: {$agentName}");
        } else {
            $this->info("No chat histories found for agent: {$agentName}");
        }

        return 0;
    }
}
