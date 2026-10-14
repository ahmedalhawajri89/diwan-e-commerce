<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'is_protected',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            // Without this cast the column arrives from MySQL as int 1, and
            // isProtected()'s `=== true` was therefore always false — the
            // protected system administrator was deletable by anyone.
            'is_protected' => 'boolean',
            // The room and way in they measured, kept for their next device.
            'room' => 'array',
            'way_in' => 'array',
        ];
    }

    /**
     * Whether this account is system protected and must survive.
     */
    public function isProtected(): bool
    {
        return (bool) $this->is_protected;
    }

    /**
     * Whether this account may be deleted.
     */
    public function canBeDeleted(): bool
    {
        return ! $this->isProtected();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Whether this account may reach the admin panel.
     *
     * The `['admin', 'Admin']` pair is not defensive typing — both spellings
     * exist in real data. That knowledge belongs in exactly one place; it was
     * copied into eight call sites across controllers, middleware and two
     * Blade layouts, where a third spelling would have had to be added eight
     * times. The set is kept as-is rather than switched to a case-insensitive
     * comparison, because widening what counts as an admin is a security
     * decision and not something a refactor gets to make on its way past.
     *
     * Memoised per instance: the storefront layout asks twice per page — once
     * for the account dropdown, once for the mobile disclosure — and the
     * answer cannot change inside a single request.
     */
    public function isAdmin(): bool
    {
        if ($this->adminMemo !== null) {
            return $this->adminMemo;
        }

        // Already-loaded roles are answered from memory. A caller that eager
        // loads for a list of users then gets no query per row.
        return $this->adminMemo = $this->relationLoaded('roles')
            ? $this->roles->contains(fn (Role $role) => in_array($role->name, self::ADMIN_ROLE_NAMES, true))
            : $this->roles()->whereIn('name', self::ADMIN_ROLE_NAMES)->exists();
    }

    /** The role names that grant the admin panel. */
    public const ADMIN_ROLE_NAMES = ['admin', 'Admin'];

    /**
     * Backing store for isAdmin()'s memo. A declared property rather than an
     * attribute, so it never reaches the database, `toArray()`, or a form.
     */
    protected ?bool $adminMemo = null;

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * The panel's notices for this administrator, newest first. Named apart
     * from Notifiable's notifications(), which expects Laravel's own table
     * shape; see App\Models\Notification.
     */
    public function alerts()
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest()->orderByDesc('id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
