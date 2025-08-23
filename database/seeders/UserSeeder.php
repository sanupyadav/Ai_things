<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Disable FK checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate users table
        User::truncate();

        // Re-enable FK checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1️⃣ Add your specific user
        User::create([
            'name' => 'Sky',
            'email' => 'yadavsanup90@gmail.com',
            'password' => bcrypt('asdf1234'),
        ]);

        // 2️⃣ Add 9 other fake users
        $users = [
            ['name' => 'John', 'email' => 'john@example.com', 'password' => bcrypt('password1')],
            ['name' => 'Alice', 'email' => 'alice@example.com', 'password' => bcrypt('password2')],
            ['name' => 'Bob', 'email' => 'bob@example.com', 'password' => bcrypt('password3')],
            ['name' => 'Charlie', 'email' => 'charlie@example.com', 'password' => bcrypt('password4')],
            ['name' => 'David', 'email' => 'david@example.com', 'password' => bcrypt('password5')],
            ['name' => 'Eva', 'email' => 'eva@example.com', 'password' => bcrypt('password6')],
            ['name' => 'Frank', 'email' => 'frank@example.com', 'password' => bcrypt('password7')],
            ['name' => 'Grace', 'email' => 'grace@example.com', 'password' => bcrypt('password8')],
            ['name' => 'Hannah', 'email' => 'hannah@example.com', 'password' => bcrypt('password9')],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
