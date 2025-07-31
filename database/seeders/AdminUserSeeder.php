<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate([
            'email' => 'admin@atipay.com',
        ], [
            'username' => 'admin',
            'email' => 'admin@atipay.com',
            'password' => 'Admin123#',
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'reference_code' => Str::random(10),
            'referred_by' => null
        ]);
    }
}
