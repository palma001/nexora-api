<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Services\TeamService;
use App\Http\Requests\Api\StoreUserRequest;
use App\Http\Requests\Api\UpdateUserRequest;
use App\Http\Requests\Api\StoreRoleRequest;
use App\Http\Requests\Api\UpdateRoleRequest;
use App\Http\Resources\Api\UserResource;
use App\Http\Resources\Api\RoleResource;
use App\Http\Resources\Api\PermissionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Controller to manage the company's team, including users, roles, and permissions.
 */
class TeamController extends Controller
{
    /**
     * @var TeamService
     */
    protected $teamService;

    /**
     * TeamController constructor.
     *
     * @param TeamService $teamService
     */
    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }

    /**
     * Get all users in the current company.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function indexUsers(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);
        
        $companyId = $request->header('X-Company-Id') ?? auth()->user()->current_company_id;
        
        // TenantScope handles the main query filtering (assoc with company).
        // We still filter the relationship for specific pivot data needed by UserResource.
        $query = User::with(['companies']);

        // Apply BaseModel scopes and paginate
        $users = $query->filters($request->only(['search', 'sort', 'role_id', 'status']))
                      ->paginate($request->get('per_page', 15));

        return UserResource::collection($users);
    }

    /**
     * Invite or create a new user for the company.
     *
     * @param StoreUserRequest $request
     * @return UserResource
     */
    public function storeUser(StoreUserRequest $request): UserResource
    {
        $this->authorize('manage', User::class);
        
        $companyId = $request->header('X-Company-Id') ?? auth()->user()->current_company_id;

        $user = $this->teamService->createUser($request->validated(), $companyId);

        return new UserResource($user->load(['companies' => function ($query) use ($companyId) {
            $query->where('companies.id', $companyId);
        }]));
    }

    /**
     * Update a user's role or status.
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return UserResource
     */
    public function updateUser(UpdateUserRequest $request, User $user): UserResource
    {
        $this->authorize('update', $user);
        
        $companyId = $request->header('X-Company-Id') ?? auth()->user()->current_company_id;

        $this->teamService->updateUserInCompany($user, $companyId, $request->validated());

        return new UserResource($user->load(['companies' => function ($query) use ($companyId) {
            $query->where('companies.id', $companyId);
        }]));
    }

    /**
     * Get all roles for the current company.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function indexRoles(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Role::class);
        
        // TenantScope handles filtering by company_id automatically
        $roles = Role::with('permissions')
            ->filters($request->only(['search', 'sort']))
            ->paginate($request->get('per_page', 15));

        return RoleResource::collection($roles);
    }

    /**
     * Store a new role.
     *
     * @param StoreRoleRequest $request
     * @return RoleResource
     */
    public function storeRole(StoreRoleRequest $request): RoleResource
    {
        $this->authorize('manage', Role::class);
        
        $companyId = $request->header('X-Company-Id') ?? auth()->user()->current_company_id;

        $role = $this->teamService->createRole($request->validated(), $companyId);

        return new RoleResource($role->load('permissions'));
    }

    /**
     * Update a role and its permissions.
     *
     * @param UpdateRoleRequest $request
     * @param Role $role
     * @return RoleResource
     */
    public function updateRole(UpdateRoleRequest $request, Role $role): RoleResource
    {
        $this->authorize('manage', $role);
        
        $updatedRole = $this->teamService->updateRole($role, $request->validated());

        return new RoleResource($updatedRole->load('permissions'));
    }

    /**
     * Get all available permissions.
     *
     * @return AnonymousResourceCollection
     */
    public function indexPermissions(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Permission::class);
        return PermissionResource::collection(Permission::all());
    }
}
