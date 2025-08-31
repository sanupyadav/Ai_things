<?php

namespace App\AiAgents;

use LarAgent\Agent;

class TranslationAgent extends Agent
{
    // protected $model = 'llama3.2:3b';

    // protected $history = 'in_memory';openai/gpt-oss-120b

    // protected $provider = 'ollama';


     protected $model = 'deepseek-ai/DeepSeek-V3.1:fireworks-ai';

    protected $history = 'in_memory';

    protected $provider = 'huggingface';
    protected $tools = [];

    public function instructions()
{
    return <<<EOT
        You are an AI Reviewer analyzing customer support conversations between an agent and a customer. 
        Your role is to evaluate the agent’s performance and provide structured, dashboard-ready insights.  

        ### Evaluation Criteria

        1. **Tone of the Agent**  
           - Categories: friendly, professional, empathetic, neutral, robotic, rude  

        2. **Customer Sentiment**  
           - Categories: positive, neutral, negative  
           - Scoring: positive = 5, neutral = 3, negative = 1  

        3. **Guideline Adherence**  
           - Categories: excellent, good, average, poor  
           - Scoring: excellent = 5, good = 4, average = 3, poor = 1  

        4. **Issue Resolution**  
           - Categories: resolved, partially_resolved, unresolved  
           - Scoring: resolved = 5, partially_resolved = 3, unresolved = 1  

        5. **Communication Clarity**  
           - Scale: 0 (confusing) → 1 (very clear)  

        6. **Empathy Level**  
           - Scale: 0 (none) → 1 (very empathetic)  

        7. **Strengths & Weaknesses**  
           - Identify from the conversation  
           - StrengthWeaknessScore = min(5, max(1, (count(strengths) - count(weaknesses)) + 3))  

        ---

        ### Conversation Rating Formula (0–5 Scale)

        The final conversation rating is calculated using four weighted factors:

        - **Sentiment (S)** → customer’s emotional state (range: -1 = very negative → 1 = very positive) → weight 35%  
        - **Clarity (C)** → how clear the agent’s communication was (0–1) → weight 25%  
        - **Empathy (E)** → empathy shown by the agent (0–1) → weight 25%  
        - **Resolution (R)** → issue outcome (0 = unresolved, 0.5 = partial, 1 = resolved) → weight 15%  

        **Formula:**
        Rating = 5 × (0.35S + 0.25C + 0.25E + 0.15R)

        ---

        ### Example Calculation

        - Sentiment (S) = 0.8 (positive)  
        - Clarity (C) = 0.9  
        - Empathy (E) = 0.7  
        - Resolution (R) = 1 (resolved)  

        Rating = 5 × (0.35(0.8) + 0.25(0.9) + 0.25(0.7) + 0.15(1))  
        Rating = 5 × (0.28 + 0.225 + 0.175 + 0.15)  
        Rating = 5 × 0.83 = **4.15 ≈ 4.2 / 5 ⭐**

    EOT;
}


    public function prompt($message)
    {
        return $message;
    }




    public function structuredOutput(): array
{
    return [
       'name' => 'agent_performance_review',
        'schema' => [
            'type' => 'object',
            'properties' => [
                'customerSentiment' => [
                    'type' => 'string',
                    'enum' => ['positive', 'neutral', 'negative'],
                    'description' => 'Overall sentiment of the customer during the interaction',
                ],
                'guidelineAdherence' => [
                    'type' => 'string',
                    'enum' => ['excellent', 'good', 'average', 'poor'],
                    'description' => 'How well the agent followed company guidelines and protocols',
                ],
                'issueResolution' => [
                    'type' => 'string',
                    'enum' => ['resolved', 'partially_resolved', 'unresolved'],
                    'description' => 'Final status of the customer issue',
                ],
                'communicationClarity' => [
                    'type' => 'string',
                    'enum' => ['excellent', 'good', 'average', 'poor'],
                    'description' => 'Clarity of communication, tone, and professionalism',
                ],
                'empathyLevel' => [
                    'type' => 'string',
                    'enum' => ['high', 'moderate', 'low'],
                    'description' => 'How empathetic the agent was during the interaction',
                ],
                'strengths' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Notable positive aspects of the agent’s performance',
                ],
                'weaknesses' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Areas where the agent needs improvement',
                ],
                'suggestions' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Actionable suggestions to improve performance',
                ],
                'rating' => [
                    'type' => 'number',
                    'minimum' => 1,
                    'maximum' => 5,
                    'description' => 'Overall rating of the agent based on performance, calculated using a formula',
                ],
                'overallSummary' => [
                    'type' => 'string',
                    'description' => 'Comprehensive summary of the agent performance review',
                ],
            ],
            'required' => [
                'customerSentiment',
                'guidelineAdherence',
                'issueResolution',
                'communicationClarity',
                'empathyLevel',
                'rating',
                'overallSummary',
                'strengths',
                'weaknesses','suggestions'
            ],
            'additionalProperties' => false,
        ],
        'strict' => true,
    ];
}

   



}
