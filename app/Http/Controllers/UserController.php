<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Mail\WelcomeUserMail;
use App\Models\Store;
use App\Models\User;
use App\Services\ActivityLogService;
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
        $users = User::query()
            ->with('store')
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
            'roles' => User::ROLES,
        ]);
    }

    public function create()
    {
        return view('users.create', [
            'managedUser' => new User(),
            'roles' => User::ROLES,
            'stores' => Store::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $plainPassword = $request->input('password');
        $user = User::create($data);

        $this->activityLogService->log($request->user()->id, 'created', 'users', "Created user {$user->name}.", $user);
        $this->sendWelcomeEmail($user, $plainPassword);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('users.edit', [
            'managedUser' => $user,
            'roles' => User::ROLES,
            'stores' => Store::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        $this->activityLogService->log($request->user()->id, 'updated', 'users', "Updated user {$user->name}.", $user);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user)
    {
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

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    private function sendWelcomeEmail(User $user, string $plainPassword): void
    {
        try {
            Mail::to($user->email)->send(new WelcomeUserMail($user, $plainPassword));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
