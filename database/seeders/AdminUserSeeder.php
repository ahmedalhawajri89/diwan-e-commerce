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
        // Create protected system admin user
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@fu.local',
            'password' => Hash::make('admin1234'),
            'phone' => '+966501234567',
            'is_protected' => true,
        ]);

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $admin->roles()->attach($adminRole);
        }

        // Create sample customer
        User::create([
            'name' => 'Customer User',
            'email' => 'customer@fu.local',
            'password' => Hash::make('customer1234'),
            'phone' => '+966509876543',
            'is_protected' => false,
        ]);
    }
}
