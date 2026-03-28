<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Mail\WelcomeUserMail;
use App\Models\Organization;
use App\Models\Store;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public function index(Request $request)
    {
        $currentUser = $request->user();

        $users = User::query()
            ->with(['organization', 'subscriptionPayments' => fn ($query) => $query->latest(), 'subscriptionPayments.plan'])
            ->when(! $currentUser?->isSuperAdmin(), fn ($query) => $query->where('role', '!=', User::ROLE_SUPER_ADMIN))
            ->when(! $currentUser?->isSuperAdmin(), fn ($query) => $query->where('organization_id', $currentUser?->organization_id))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => $this->availableRolesFor($currentUser),
        ]);
    }

    public function create(Request $request)
    {
        return view('users.create', [
            'managedUser' => new User(),
            'roles' => $this->availableRolesFor($request->user()),
            'organizations' => Organization::query()->orderBy('name')->get(),
            'stores' => $this->availableStoresFor($request),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $plainPassword = $request->input('password');
        $data = $this->sanitizeUserData($request, $data);
        $user = User::create($data);

        $this->activityLogService->log($request->user()->id, 'created', 'users', "Created user {$user->name}.", $user);
        $this->sendWelcomeEmail($user, $plainPassword);

        return redirect()->route($this->userIndexRouteName($request))->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $this->authorizeManagedUser($user, request()->user());

        return view('users.edit', [
            'managedUser' => $user,
            'roles' => $this->availableRolesFor(request()->user()),
            'organizations' => Organization::query()->orderBy('name')->get(),
            'stores' => $this->availableStoresFor(request()),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorizeManagedUser($user, $request->user());
        $before = $this->userSnapshot($user);

        $data = $request->validated();
        $data = $this->sanitizeUserData($request, $data);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        $this->activityLogService->log(
            $request->user()->id,
            'updated',
            'users',
            "Updated user {$user->name}.",
            $user,
            $before,
            $this->userSnapshot($user->fresh(['organization', 'store']))
        );

        return redirect()->route($this->userIndexRouteName($request))->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorizeManagedUser($user, $request->user());

        if ($request->user()->is($user)) {
            return back()->withErrors([
                'delete' => 'You cannot delete your own admin account.',
            ]);
        }

        $name = $user->name;

        try {
            $user->delete();
        } catch (QueryException) {
            return back()->withErrors([
                'delete' => 'This user cannot be deleted because it is linked to existing records.',
            ]);
        }

        $this->activityLogService->log($request->user()->id, 'deleted', 'users', "Deleted user {$name}.");

        return redirect()->route($this->userIndexRouteName($request))->with('success', 'User deleted successfully.');
    }

    private function sendWelcomeEmail(User $user, string $plainPassword): void
    {
        try {
            Mail::to($user->email)->send(new WelcomeUserMail($user, $plainPassword));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function availableRolesFor(?User $user): array
    {
        return array_values(array_filter(User::ROLES, fn (string $role) => $role !== User::ROLE_SUPER_ADMIN));
    }

    private function authorizeManagedUser(User $managedUser, ?User $actor): void
    {
        if ($managedUser->isSuperAdmin()) {
            abort(403);
        }

        if (! $actor?->isSuperAdmin() && $managedUser->organization_id !== $actor?->organization_id) {
            abort(403);
        }
    }

    private function sanitizeUserData(Request $request, array $data): array
    {
        if ($request->routeIs('owner.*')) {
            unset($data['store_id']);
        }

        if (! $request->user()?->isSuperAdmin()) {
            unset($data['access_enabled'], $data['access_expires_at'], $data['organization_id']);
            $data['role'] = $data['role'] === User::ROLE_SUPER_ADMIN ? User::ROLE_ADMIN : $data['role'];
            $data['organization_id'] = $request->user()?->organization_id;
        }

        return $data;
    }

    private function userIndexRouteName(Request $request): string
    {
        return $request->routeIs('owner.*') ? 'owner.users.index' : 'users.index';
    }

    private function availableStoresFor(Request $request): Collection
    {
        if ($request->routeIs('owner.*')) {
            return new Collection();
        }

        return Store::query()->orderBy('name')->get();
    }

    private function userSnapshot(User $user): array
    {
        $user->loadMissing(['organization', 'store']);

        return [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'organization' => $user->organization?->name,
            'store' => $user->store?->name,
            'access_enabled' => (bool) $user->access_enabled,
            'access_expires_at' => optional($user->access_expires_at)->format('Y-m-d H:i:s'),
        ];
    }
}
