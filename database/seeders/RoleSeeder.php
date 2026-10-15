<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * firstOrCreate rather than insert, so a second `db:seed` on a database
     * that already has its roles does not add a duplicate of each one.
     */
    public function run(): void
    {
        foreach (['admin', 'customer', 'seller'] as $name) {
            $keep = Role::where('name', $name)->orderBy('id')->first()
                ?? Role::create(['name' => $name]);

            // The old insert() left a copy of every role on each re-seed.
            // Remove those copies, but only the ones no user holds.
            Role::where('name', $name)
                ->where('id', '>', $keep->id)
                ->whereDoesntHave('users')
                ->delete();
        }
    }
}
