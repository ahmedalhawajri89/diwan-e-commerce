<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * The public demo's two accounts, each only when its environment asks for
 * it (config/demo.php): the owner, a real administrator with a password
 * nobody else knows, and the viewer, who sees the panel and changes nothing.
 * Nothing here has a default, so a copy of the shop without these settings
 * gets neither.
 */
class DemoAccountsSeeder extends Seeder
{
    public function run(): void
    {
        if (filled(config('demo.owner_email')) && filled(config('demo.owner_password'))) {
            $owner = User::firstOrCreate(
                ['email' => config('demo.owner_email')],
                ['name' => config('demo.owner_name'), 'password' => Hash::make(config('demo.owner_password')), 'is_protected' => true],
            );
            $owner->roles()->syncWithoutDetaching([Role::firstOrCreate(['name' => 'admin'])->id]);
        }

        if (filled(config('demo.viewer_email')) && filled(config('demo.viewer_password'))) {
            $viewer = User::firstOrCreate(
                ['email' => config('demo.viewer_email')],
                ['name' => 'Demo Viewer', 'password' => Hash::make(config('demo.viewer_password')), 'is_protected' => true],
            );
            $viewer->roles()->syncWithoutDetaching([Role::firstOrCreate(['name' => User::VIEWER_ROLE])->id]);
        }
    }
}
