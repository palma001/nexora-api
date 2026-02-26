<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\Auditable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasBaseScopes;
use App\Traits\FilterByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, Auditable, SoftDeletes, HasApiTokens, HasBaseScopes, FilterByTenant;

    /**
     * Columns searchable via the scopeSearch.
     * 
     * @var array
     */
    protected $searchable = ['name', 'email', 'username'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username', // Added this line
        'password',
        'is_root',
        'current_company_id',
        'company_config_pending',
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
            'is_root' => 'boolean',
            'company_config_pending' => 'boolean',
        ];
    }

    // --- Relationships ---

    public function currentCompany()
    {
        return $this->belongsTo(Company::class, 'current_company_id');
    }

    public function ownedCompanies()
    {
        return $this->hasMany(Company::class, 'owner_id');
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_user')
                    ->withPivot(['role_id', 'is_owner', 'status'])
                    ->withTimestamps();
    }

    /**
     * Get the role of the user in the current company.
     */
    public function currentRole()
    {
        // This is a bit complex because it depends on the pivot of the current company
        // We can get it via the companies relationship where company_id = current_company_id
        $companyUser = $this->companies()
                            ->where('company_id', $this->current_company_id)
                            ->first();

        return $companyUser ? Role::find($companyUser->pivot->role_id) : null;
    }

    /**
     * Direct permissions assigned to the user (filtered by current company).
     */
    public function directPermissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user')
                    ->wherePivot('company_id', $this->current_company_id);
    }

    // --- Authorization Logic ---

    /**
     * Check if user has a permission in the current company context.
     * Priority:
     * 1. Direct Permission (if exists)
     * 2. Role Permission
     */
    public function hasPermission(string $permissionName): bool
    {
        if ($this->is_root) {
            return true;
        }

        return in_array($permissionName, $this->getAllPermissions());
    }

    /**
     * Get all permission names for the user in the current company context.
     */
    public function getAllPermissions(): array
    {
        if ($this->is_root) {
            // Ideally fetch all from DB if is_root, but for now:
            return Permission::pluck('name')->toArray();
        }

        if (!$this->current_company_id) {
            return [];
        }

        // Cache or eager load this in a real app
        $direct = $this->directPermissions()->pluck('name')->toArray();
        
        $roles = $this->rolesInCompany($this->current_company_id)->with('permissions')->get();
        $rolePerms = $roles->flatMap(function ($role) {
            return $role->permissions->pluck('name');
        })->unique()->toArray();

        return array_unique(array_merge($direct, $rolePerms));
    }
    /**
     * Get the roles for the user in a specific company.
     */
    public function rolesInCompany(int $companyId): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user_company')
                    ->wherePivot('company_id', $companyId)
                    ->withTimestamps();
    }
}
