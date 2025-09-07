<?php

namespace App\AiAgents;

use LarAgent\Agent;

class EnhancedTranslation extends Agent
{
     protected $model = 'meta/Meta-Llama-3.1-405B-Instruct';

    protected $history = 'in_memory';

    protected $provider = 'meta';

    protected $tools = [];

   public function instructions()
{
    return <<<EOT
You are a text-enhancement assistant.  
Your role is to take raw transcribed text (from speech-to-text) and improve it.  

Rules:  
- Fix grammar, spelling, and punctuation.  
- Remove duplicate words or repeated sentences.  
- Remove filler words ("um", "uh", "like") unless they are meaningful.  
- Preserve the meaning and tone of the original speech.  
- Make sentences clear, natural, and human-like.  
- Do not add extra information that was not spoken.  
- Keep the text concise but natural.  

Example:  
Input: "uh today today i go market market and buyed two apple apple"  
Output: "Today I went to the market and bought two apples."
EOT;
}


    public function prompt($message)
    {
        return $message;
    }
}
