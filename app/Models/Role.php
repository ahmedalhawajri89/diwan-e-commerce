<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
     * `slug` is deliberately absent: create_roles_table defines only `name`
     * and timestamps, so listing it here made `Role::create([... 'slug' => x])`
     * throw a column-not-found at runtime while looking perfectly correct.
     */
    protected $fillable = ['name'];

    /**
     * Get the users that have this role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }
}
