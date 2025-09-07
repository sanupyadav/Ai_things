<?php

namespace App\AiAgents;

use LarAgent\Agent;
use App\Models\User;
use App\Models\Transaction;
use LarAgent\Attributes\Tool;

class OrgChatAgent extends Agent
{
    protected $model = 'meta/Meta-Llama-3.1-405B-Instruct';

    protected $history = 'session';

    protected $provider = 'meta';

    //  protected $model = 'qwen2.5:7b';   //'llama2-uncensored:latest';

    // protected $history = 'session';

    // protected $provider = 'ollama';

    protected $tools = [];

    public function instructions()
    {

        $tools = $this->getTools();

        return View('chatAgent.chat_instructions', compact('tools'));
    }

    public function prompt($message)
    {
        return $message;

    }


    public function isUserId(string $input): bool
    {
        return is_numeric(trim($input));
    }


    #[Tool('Get any data from DB safely', ['query' => 'user ask question', '$currentUserId' => 'login users id'])]
    public function getAnyDataFromDB(string $query, int $currentUserId)
    {

        $service = new \App\Services\DBQueryService;

        return $service->handleQuery($query, $currentUserId);

    }


    #[Tool('Get complete Pay1 company profile with structured data', [
    'query' => 'user question about Pay1'
])]
public function getPay1Info(string $query = ''): array
{
    return [
        'overview' => 'Pay1 (MindsArray Network Pvt. Ltd.) is a Mumbai-based retail-tech platform founded around 2012. It empowers local shopkeepers by offering financial, digital, and business services.',
        'founders' => 'Founders: Alakh Gargiya (CEO), Ashish Arya (CTO), Chirutha Dalal (COO), Vinit Khanvilkar (CBO), Abhinav Mathur (CFO).',
        'funding' => 'Mostly bootstrapped. No major funding publicly disclosed.',
        'services' => 'Bill payments, mobile/DTH recharges, AePS banking, UPI/QR payments, insurance, travel booking, ledger tools.',
        'market' => '400,000+ retailers, 30M customers, 2,300+ cities. Strong rural focus.',
        'news' => '2024: Pay1 rebranded to umbrella brand "Dhanak" with verticals Pay1, Device Hub, Mera Fayda, Refurb.',
        'awards' => 'SiliconIndia "Company of the Year 2019". Recognized for women leadership (40% top roles).',
        'links' => [
            'official' => 'https://www.pay1.in/',
            'yourstory' => 'https://yourstory.com/companies/pay1',
            'siliconindia' => 'https://www.siliconindia.com/magazine_articles/pay1-dukandaron-ka-network-REXK424018824.html',
            'finextra' => 'https://www.finextra.com/pressarticle/98538/pay1-rebrands-as-dhanak',
            'crunchbase' => 'https://www.crunchbase.com/organization/pay1',
        ]
    ];
}


}
