<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // These accounts have published passwords. On a production database
        // they would be a known way into the admin panel, so they are only
        // ever created on a developer's machine or in a test run.
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('AdminUserSeeder skipped: demo accounts are local-only.');

            return;
        }

        // Keyed on the email, so seeding twice finds the existing account
        // instead of failing on the unique index.
        $admin = User::firstOrCreate(
            ['email' => 'admin@fu.local'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('admin1234'),
                'phone' => '+966501234567',
                'is_protected' => true,
            ]
        );

        $adminRole = Role::where('name', 'admin')->orderBy('id')->first();
        if ($adminRole) {
            $admin->roles()->syncWithoutDetaching([$adminRole->id]);
        }

        User::firstOrCreate(
            ['email' => 'customer@fu.local'],
            [
                'name' => 'Customer User',
                'password' => Hash::make('customer1234'),
                'phone' => '+966509876543',
                'is_protected' => false,
            ]
        );
    }
}
