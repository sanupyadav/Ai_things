<?php

namespace App\Http\Controllers;

use App\AiAgents\SmartAgent;
use Illuminate\Http\Request;
use App\AiAgents\OrgChatAgent;

class ChatController extends Controller
{
    protected $chatHistory = [];

    public function index()
    {
        $this->chatHistory = $this->setHistoryFromAgent();

        $normalized = [];
        $lastKey = null;

        foreach ($this->chatHistory as $msg) {
            $text = $msg['content'] ?? $msg['text'] ?? '';

            if (is_array($text)) {
                $parts = [];
                foreach ($text as $part) {
                    if (($part['type'] ?? null) === 'text') {
                        $parts[] = $part['text'];
                    }
                }
                $text = implode("\n", $parts);
            }

            $key = ($msg['role'] ?? $msg['sender']).'|'.trim($text);

            // Only skip if this message is the same as the immediately previous one
            if ($key !== $lastKey) {
                $normalized[] = [
                    'sender' => $msg['role'] ?? $msg['sender'],
                    'text' => trim($text),
                ];
                $lastKey = $key;
            }
        }

        return view('chat', [
            'chatHistory' => $normalized,
        ]);
    }

    public function sendMessage(Request $request)
    {
        ini_set('max_execution_time', 120);

        $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $userMessage = $request->input('message');

        // Send message to agent
        $this->getAgentInstance()->respond($userMessage);

        // Refresh chat history
        $this->chatHistory = $this->setHistoryFromAgent();

        return response()->json([
            'success' => true,
            'chatHistory' => $this->getChatHistory(),
        ]);
    }

    /**
     * Get OrgChatAgent instance for current user.
     */
    protected function getAgentInstance(): OrgChatAgent
    {
        return OrgChatAgent::forUser(auth()->user());
    }

    /**
     * Fetch history from agent and assign it to controller property.
     */
    protected function setHistoryFromAgent(): array
    {
        $agentHistory = $this->getAgentInstance()->chatHistory()->toArray();
        $this->chatHistory = $agentHistory;

        return $this->chatHistory;
    }

    /**
     * Return chat history formatted for frontend (sender + text).
     */
    public function getChatHistory(): array
    {
        $filtered = array_filter($this->chatHistory, fn ($m) => in_array($m['role'] ?? $m['sender'], ['user', 'assistant']));

        $messages = array_values(array_map(function ($msg) {
            $sender = $msg['role'] ?? $msg['sender'] ?? 'system';
            $text = $msg['content'] ?? $msg['text'] ?? '';

            // If "content" is an array of objects like [["type"=>"text","text"=>"..."]]
            if (is_array($text)) {
                $parts = [];
                foreach ($text as $part) {
                    if (is_array($part) && ($part['type'] ?? null) === 'text') {
                        $parts[] = $part['text'];
                    }
                }
                $text = implode("\n", $parts); // join multiple segments
            }

            return [
                'sender' => $sender,
                'text' => $text,
            ];
        }, $filtered));

        return $messages;
    }

    /**
     * Clear agent history and reset local history.
     */
    public function clearHistoryFromAgent()
    {
        $this->getAgentInstance()->clear();
        // Reset history in controller & session
        $this->chatHistory = [];
        session()->forget('chatHistory');
        SmartAgent::forUser(auth()->user())->clear();
        
        return response()->json([
            'success' => true,
            'message' => 'Chat history cleared successfully',
        ]);
    }
}
