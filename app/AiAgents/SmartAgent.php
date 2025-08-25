<?php

namespace App\AiAgents;

use LarAgent\Agent;

class SmartAgent extends Agent
{
    protected $model = 'meta/Meta-Llama-3.1-405B-Instruct';

    protected $history = 'session';

    protected $provider = 'gpt4';

    protected $tools = [];

    protected $query;

    protected $currentUserId;
    protected $allowedTables;
    protected $schema;

    public function setContext(string $query, int $currentUserId, $schema, $allowedTables)
    {
        $this->query = $query;
        $this->currentUserId = $currentUserId;
        $this->schema = $schema;
        $this->allowedTables = $allowedTables; // Fixed missing assignment
    }

    public function instructions()
    {
        $schema = json_encode($this->schema, JSON_PRETTY_PRINT);
        $allowedTables = json_encode($this->allowedTables, JSON_PRETTY_PRINT);

        return <<<EOT
# Laravel AI Agent - Database Query Generator

## INPUT
- Query: "{$this->query}"
- Current User ID: {$this->currentUserId}

## TASK
Convert the natural language query into a structured database action.

## ALLOWED TABLES & COLUMNS
$allowedTables

## COMPLETE SCHEMA (reference only)
$schema

Response format (always in JSON, no extra text):
This is only example for llm dont return this
{
  "type": "read",
  "table": "table_name",
  "columns": ["col1", "col2"],
  "filters": {"col": "value"}
}


## FILTERING RULES (CRITICAL)

### 1. User-Specific Tables
ALWAYS include: `"user_id": {$this->currentUserId}`

### 2. Specific Record Queries
When user mentions a specific ID:
- Transaction ID → use `"transaction_id": "value"`
- Order ID → use `"order_id": "value"`
- Product ID → use `"product_id": "value"`

### 3. Example Patterns
```json
// "Show my transactions"
"type": "read", "table": "transactions", "filters": {"transction_id": from query

// "Show transaction 12345"
{"type": "read", "table": "transactions", "filters": {"transaction_id": "12345"}}

// "Update my profile name to John"
{"type": "update", "table": "users", "filters": {"user_id": {$this->currentUserId}}, "data": {"name": "John"}}
```

## VALIDATION CHECKLIST
- ✅ Table exists in allowed tables
- ✅ Columns exist in schema
- ✅ User ID filter applied for user-specific data
- ✅ Specific ID filter applied when mentioned
- ✅ Valid JSON format

## SECURITY
- Only query allowed tables
- Always filter by user_id for user data
- Use exact column names from schema
EOT;
    }

    public function prompt($message)
    {
        return $message;
    }
}