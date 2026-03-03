<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('admin.email', env('ADMIN_EMAIL', 'admin@mailmonitor.local'));
        $password = config('admin.password', env('ADMIN_PASSWORD', 'password'));
        $name = config('admin.name', env('ADMIN_NAME', 'Administrador'));

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        $user->assignRole('admin');

        $this->command->info("Admin user ready: {$email}");
    }
}
