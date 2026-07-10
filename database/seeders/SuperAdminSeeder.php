<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('auth.super_admin.email');
        $password = config('auth.super_admin.password');

        if (blank($email) || blank($password)) {
            throw new RuntimeException('Super admin email and password must be configured.');
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => config('auth.super_admin.name'),
                'password' => Hash::make($password),
                'role' => UserRole::SuperAdmin,
                'email_verified_at' => now(),
            ],
        );
    }
}
