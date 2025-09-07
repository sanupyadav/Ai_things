<?php

namespace App\AiAgents;

use LarAgent\Agent;

class visionAgent extends Agent
{
    protected $model = 'qwen2.5vl:3b';
    protected $history = 'in_memory';
    protected $provider = 'ollama';
    protected $tools = [];
  

    public function instructions()
    {
            return <<<EOT
You are VisionAgent, an AI assistant specialized in extracting structured data from credit card images.

⚠️ Key Guidelines:
1. Analyze the provided image carefully; only extract information that is clearly visible.
2. Extract the following fields:
   - Card Number
   - Card Holder Name
   - Expiry Date (MM/YY)
   - CVV 
   - Card Type (Visa, Mastercard, Amex, etc., if inferable from number or logo)
3. Do NOT guess or invent data that is not visible.
4. Use dummy/test card images for testing purposes.
5. Return results ONLY in JSON format.
6. For any field that is not visible, return `null`.
7. Always follow structured output as defined below.

EOT;
    }

    /**
     * Define structured output schema for credit card data
     */
   public function structuredOutput(): array
{
    return [
        'name' => 'credit_card_data',
        'schema' => [
            'type' => 'object',
            'properties' => [
                'card_number' => [
                    'type' => 'string',
                    'description' => 'Credit card number from the front, or null if not visible'
                ],
                'card_holder' => [
                    'type' => 'string',
                    'description' => 'Card holder name from the front, or null if not visible'
                ],
                'expiry_date' => [
                    'type' => 'string',
                    'description' => 'Expiry date from the front in MM/YY format, or null if not visible'
                ],
                'cvv' => [
                    'type' => 'string',
                    'description' => 'CVV number from the back if visible, otherwise null'
                ],
                'card_type' => [
                    'type' => 'string',
                    'description' => 'Card type (Visa, Mastercard, Amex, etc.), or null if cannot be determined'
                ],
            ],
            'required' => ['card_number', 'card_holder', 'expiry_date', 'cvv', 'card_type']
        ]
    ];
}

    public function prompt($message)
    {
          return $message;
    }
}
