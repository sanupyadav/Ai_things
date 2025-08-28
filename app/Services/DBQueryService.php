<?php

namespace App\Services;

use App\AiAgents\SmartAgent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DBQueryService
{
    // Allowed tables & columns for safety
    protected array $allowedTables = [
        'transactions' => ['id', 'user_id', 'transaction_id', 'amount', 'status', 'created_at'],
        'users' => ['id', 'name', 'email', 'created_at'],
    ];

    protected array $schema = [];

    public function __construct()
    {
        $this->schema = $this->getSchema(); // Dynamic schema
    }

    public function handleQuery(string $query, int $currentUserId, int $page = 1, int $perPage = 10): array
    {
        // dd($query, $currentUserId);
        // Step 1: Convert natural language query to structured DB action using LLM
        $action = $this->parseQueryWithLLM($query, $currentUserId);

        // Step 2: Perform DB action safely
        $result = $this->performAction($action, $page, $perPage);

        // Step 3: Format response
        return $this->formatResponse($result);
    }

    /**
     * Use LLM to parse natural language query into structured action
     */
    protected function parseQueryWithLLM(string $query, int $currentUserId): array
    {
        $allowedTables = $this->allowedTables;
        // $prompt = $this->generateInstructions($query, $currentUserId);
        $agent = SmartAgent::forUser(auth()->user());

        $agent->setContext($query, $currentUserId, $this->schema, $allowedTables);
        $response = $agent->respond($query);
        $action = json_decode($response, true);

        if (! $action) {
            // fallback simple parsing if AI fails
            $action = $this->simpleFallback($query, $currentUserId);
        }
    //dd($action);
        return $action;
    }

    public function getSchema(): array
    {
        $schema = [];

        // Get all tables in the current database
        $tables = DB::select('SHOW TABLES');
        $dbName = env('DB_DATABASE');

        foreach ($tables as $tableObj) {
            $tableName = $tableObj->{"Tables_in_$dbName"}; // dynamic property
            $columns = Schema::getColumnListing($tableName);
            $schema[$tableName] = $columns;
        }

        return $schema;
    }

    /**
     * Fallback parsing if AI failssh
     */
    protected function simpleFallback(string $query, int $currentUserId): array
    {
        $query = strtolower($query);

        if (str_contains($query, 'payment') || str_contains($query, 'transactions')) {
            $filters = ['user_id' => $currentUserId];

            if (preg_match('/last month/', $query)) {
                $filters['created_at'] = ['>=', now()->subMonth()->startOfMonth()];
                $filters['created_at_to'] = ['<=', now()->subMonth()->endOfMonth()];
            }

            if (preg_match('/pending/', $query)) {
                $filters['status'] = 'pending';
            }

            return [
                'type' => 'read',
                'table' => 'transactions',
                'filters' => $filters,
            ];
        }

        return ['type' => 'none'];
    }

    /**
     * Perform DB action safely with pagination for reads
     */
    protected function performAction(array $action, int $page = 1, int $perPage = 10): array
    {

        $table = $action['table'] ?? null;

        if (! isset($this->allowedTables[$table])) {
            return ['error' => 'Access denied for table '.$table];
        }

        foreach ($action['filters'] ?? [] as $col => $val) {
            if (! in_array($col, $this->allowedTables[$table])) {
                return ['error' => "Column $col not allowed in table $table"];
            }
        }

        switch ($action['type'] ?? 'none') {
            case 'read':
                return $this->performRead($action, $page, $perPage);
            case 'write':
                return $this->performWrite($action);
            case 'update':
                return $this->performUpdate($action);
            case 'delete':
                return $this->performDelete($action);
            default:
                return ['error' => 'Unable to understand your request.'];
        }
    }

    protected function performRead(array $action, int $page, int $perPage)
    {
        $query = DB::table($action['table']);

        foreach ($action['filters'] ?? [] as $column => $value) {
            if (! in_array($column, $this->allowedTables[$action['table']])) {
                continue;
            }

            if (is_array($value) && isset($value[0]) && isset($value[1])) {
                $query->where($column, $value[0], $value[1]);
            } else {
                $query->where($column, $value);
            }
        }

        $total = $query->count();
        $results = $query->forPage($page, $perPage)->get();

        return [
            'data' => $results,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
            ],
        ];
    }

    protected function performWrite(array $action)
    {
        DB::table($action['table'])->insert($action['data'] ?? []);

        return ['success' => true, 'message' => 'Record inserted'];
    }

    protected function performUpdate(array $action)
    {
        $query = DB::table($action['table']);
        foreach ($action['filters'] ?? [] as $col => $val) {
            $query->where($col, $val);
        }
        $query->update($action['data'] ?? []);

        return ['success' => true, 'message' => 'Record updated'];
    }

    protected function performDelete(array $action)
    {
        $query = DB::table($action['table']);
        foreach ($action['filters'] ?? [] as $col => $val) {
            $query->where($col, $val);
        }
        $query->delete();

        return ['success' => true, 'message' => 'Record deleted'];
    }

    protected function formatResponse($result)
    {
        if (isset($result['data'])) {
            $formatted = [];
            foreach ($result['data'] as $row) {
                $formatted[] = (array) $row;
            }
            $result['data'] = $formatted;
        }

        return $result;
    }
}
