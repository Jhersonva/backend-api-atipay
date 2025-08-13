<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $username = config('app.default_admin_username');
        $email = config('app.default_admin_email');
        $password = config('app.default_admin_password');

        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        User::firstOrCreate([
            'email' => $email,
        ], [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role_id' => $adminRole->id,
            'status' => 'active',
            'reference_code' => Str::random(10),
            'referred_by' => null,
            'registration_date' => now('America/Lima')->toDateString(),
            'registration_time' => now('America/Lima')->format('h:i:s A'),
        ]);
    }
}
