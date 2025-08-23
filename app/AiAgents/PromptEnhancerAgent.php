<?php

namespace App\AiAgents;

use LarAgent\Agent;

class PromptEnhancerAgent extends Agent
{
    protected $model = 'meta/Meta-Llama-3.1-405B-Instruct';

    protected $history = 'session';

    protected $provider = 'gpt4';

    protected $tools = [];

    public function instructions()
    {
        return "You are a prompt enhancer. 
        Your job is ONLY to rewrite or improve the user's text. 
        Do NOT answer, greet, or add anything extra. 
        Output ONLY the enhanced text
        example : hi
        Output (after enhancement):
        Hi
        Input:
        plz snd me doc asap
        Output:
        Please send me the document as soon as possible";
    }

    public function prompt($message)
    {
        return $message;
    }
}
