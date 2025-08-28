<?php

namespace App\AiAgents;

use App\Models\Transaction;
use App\Models\User;
use LarAgent\Agent;
use LarAgent\Attributes\Tool;

class OrgChatAgent extends Agent
{
    protected $model = 'meta/Meta-Llama-3.1-405B-Instruct';

    protected $history = 'session';

    protected $provider = 'gpt4';

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

    // #[Tool('Retrieve user information and profile data by user ID', ['id' => 'user id'])]
    // public function getUserData(int $id): array
    // {
    //     if (! $this->isUserId($id)) {
    //         return ['error' => 'Invalid user ID format. User ID must be numeric.'];
    //     }

    //     $user = User::where('id', $id)->select('name', 'email')->first();
    //     if ($user) {
    //         $attributes = $user->getAttributes();

    //         // $attributes will be an array containing 'name' and 'email'
    //         return $attributes;
    //     }

    // }

    public function isUserId(string $input): bool
    {
        return is_numeric(trim($input));
    }

    // #[Tool('Get transaction details and history by transaction ID', ['transaction_id' => 'transaction id'])]
    // public function getDataByTransactionId(string $transactionId): array
    // {
    //     if (! $this->isTransactionId($transactionId)) {
    //         return [
    //             'success' => false,
    //             'error' => 'Invalid transaction ID format. Transaction ID must start with capital TX followed by hyphen and numbers (e.g., TX00000001).',
    //         ];
    //     }

    //     $transaction = Transaction::where('transaction_id', $transactionId)
    //         ->select('transaction_id', 'amount', 'type', 'description', 'created_at')
    //         ->first();

    //     if (! $transaction) {
    //         return [
    //             'success' => false,
    //             'error' => 'Transaction not found.',
    //         ];
    //     }

    //     return [
    //         'success' => true,
    //         'transaction' => $transaction->toArray(),
    //     ];
    // }

    // public function isTransactionId(?string $input): bool
    // {
    //     if (! $input) {
    //         return false;
    //     } // Reject null or empty strings

    //     return preg_match('/^TX\d{8,}$/', trim($input)) === 1;

    // }

    // #[Tool('Get all transaction details of a user', ['id' => ' it should be user id'])]
    // public function getAllTransactionData(string $id): array
    // {
    //     $user = User::find($id);
    //     if (! $user) {
    //         return [
    //             'success' => false,
    //             'error' => 'User not found.',
    //         ];
    //     }

    //     $transactions = Transaction::where('user_id', $id)
    //         ->select('transaction_id', 'amount', 'type', 'description', 'created_at')
    //         ->get();

    //     if (! $transactions) {
    //         return [
    //             'success' => false,
    //             'error' => 'No transactions found.',
    //         ];
    //     }

    //     return [
    //         'success' => true,
    //         'transactions' => $transactions->toArray(),
    //     ];
    // }

    #[Tool('Get any data from DB safely', ['query' => 'user ask question', '$currentUserId' => 'login users id'])]
    public function getAnyDataFromDB(string $query, int $currentUserId)
    {

        $service = new \App\Services\DBQueryService;

        return $service->handleQuery($query, $currentUserId);

    }
}
