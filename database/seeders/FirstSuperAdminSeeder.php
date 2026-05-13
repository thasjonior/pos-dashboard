<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FirstSuperAdminSeeder extends Seeder
{
    /**
     * Idempotent — only creates the super_admin if the email doesn't exist.
     * Reads credentials from env: INITIAL_ADMIN_EMAIL, INITIAL_ADMIN_PASSWORD, INITIAL_ADMIN_NAME.
     */
    public function run(): void
    {
        $email    = env('INITIAL_ADMIN_EMAIL');
        $password = env('INITIAL_ADMIN_PASSWORD');
        $name     = env('INITIAL_ADMIN_NAME', 'Super Admin');

        if (! $email || ! $password) {
            $this->command->warn('INITIAL_ADMIN_EMAIL or INITIAL_ADMIN_PASSWORD not set — skipping FirstSuperAdminSeeder.');
            return;
        }

        if (User::where('email', $email)->exists()) {
            $this->command->info("Super admin {$email} already exists — skipping.");
            return;
        }

        User::create([
            'name'      => $name,
            'email'     => $email,
            'password'  => Hash::make($password),
            'role'      => 'super_admin',
            'is_active' => true,
        ]);

        $this->command->info("Super admin {$email} created.");
    }
}
