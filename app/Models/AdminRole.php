<?php

namespace App\Models;

use App\Support\AdminMenu;
use Illuminate\Database\Eloquent\Model;

class AdminRole extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
        'is_super_admin',
        'is_system',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_super_admin' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Users assigned to this role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'admin_role_user')->withTimestamps();
    }

    /**
     * Check whether the role grants access to a menu.
     */
    public function hasPermission(string $key): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        return in_array($key, $this->permissions ?? [], true);
    }

    /**
     * Menu keys this role grants, with stale keys removed.
     *
     * @return array<int, string>
     */
    public function permissionKeys(): array
    {
        if ($this->is_super_admin) {
            return AdminMenu::keys();
        }

        return AdminMenu::filterKeys($this->permissions ?? []);
    }

    /**
     * Scope to roles that can be listed/edited in the role manager.
     */
    public function scopeOrdered($query)
    {
        return $query->orderByDesc('is_super_admin')->orderBy('name');
    }
}
