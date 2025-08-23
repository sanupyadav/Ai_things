<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {

        // Disable FK checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate transactions table
        Transaction::truncate();
        $users = User::all();

        // Re-enable FK checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($users as $user) {
            // Each user gets 3 sample transactions
            for ($i = 0; $i < 8; $i++) {
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'amount' => rand(100, 10000) / 100,
                    'type' => rand(0, 1) ? 'credit' : 'debit',
                    'description' => 'Sample transaction',
                    'transaction_id' => 'TX'.str_pad(rand(1, 9999999999), 10, '0', STR_PAD_LEFT),
                ]);
            }
        }
    }
}
