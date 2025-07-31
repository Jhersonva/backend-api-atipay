<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $username = config('app.default_admin_username');
        $email = config('app.default_admin_email');
        $password = config('app.default_admin_password');

        User::firstOrCreate([
            'email' => $email,
        ], [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'reference_code' => Str::random(10),
            'referred_by' => null
        ]);
    }
}
