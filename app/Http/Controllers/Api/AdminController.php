<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Meal;
use App\Models\Permission;
use App\Models\Reservation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin'),
        ];
    }

    public function index()
    {
        $users = User::with('role')->get();

        return response()->json(
            $users->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'image' => $user->image,
                    'role' => $user->role ? [
                        'id' => $user->role->id,
                        'name' => $user->role->name,
                        'slug' => $user->role->slug,
                    ] : null,
                ];
            })
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'image' => ['nullable', 'image', 'max:2048'],
            'direct_permission_slugs' => ['nullable', 'array'],
            'direct_permission_slugs.*' => ['string', 'exists:permissions,slug'],
        ]);

        $role = Role::with('assignablePermissions')->findOrFail($validated['role_id']);

        $allowedSlugs = $role->assignablePermissions->pluck('slug')->toArray();
        $selectedSlugs = $validated['direct_permission_slugs'] ?? [];

        if (array_diff($selectedSlugs, $allowedSlugs)) {
            throw ValidationException::withMessages([
                'direct_permission_slugs' => ['Some selected permissions are not assignable for this role.'],
            ]);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('users', 'public');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'image' => $imagePath,
        ]);

        if (!empty($selectedSlugs)) {
            $permissionIds = Permission::whereIn('slug', $selectedSlugs)->pluck('id');
            $user->directPermissions()->sync($permissionIds);
        }

        $user->load(['role.permissions', 'role.assignablePermissions', 'directPermissions']);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'image' => $user->image,
            'role' => $user->role ? [
                'id' => $user->role->id,
                'name' => $user->role->name,
                'slug' => $user->role->slug,
            ] : null,
            'direct_permissions' => $user->directPermissions->pluck('slug')->values(),
            'effective_permissions' => $user->getAllPermissions()->pluck('slug')->values(),
        ], 201);
    }

    public function show(User $user)
    {
        $user->load(['role', 'directPermissions']);

        $roles = Role::all(['id', 'name', 'slug']);

        $assignablePermissions = $user->role
            ? $user->role->assignablePermissions()->get(['permissions.id', 'permissions.name', 'permissions.slug'])
            : collect();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'image' => $user->image,
                'role' => $user->role ? [
                    'id' => $user->role->id,
                    'name' => $user->role->name,
                    'slug' => $user->role->slug,
                ] : null,
            ],
            'roles' => $roles,
            'direct_permissions' => $user->directPermissions->pluck('slug')->values(),
            'assignable_permissions' => $assignablePermissions->values(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['sometimes', 'string', 'min:8'],
            'role_id' => ['sometimes', 'exists:roles,id'],
            'image' => ['sometimes', 'nullable', 'image', 'max:2048'],
            'direct_permission_slugs' => ['nullable', 'array'],
            'direct_permission_slugs.*' => ['string', 'exists:permissions,slug'],
        ]);

        if (array_key_exists('password', $validated)) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $newRoleId = $validated['role_id'] ?? $user->role_id;
        $role = Role::with('assignablePermissions')->findOrFail($newRoleId);

        $selectedSlugs = $validated['direct_permission_slugs'] ?? null;

        if ($selectedSlugs !== null) {
            $allowedSlugs = $role->assignablePermissions->pluck('slug')->toArray();

            if (array_diff($selectedSlugs, $allowedSlugs)) {
                throw ValidationException::withMessages([
                    'direct_permission_slugs' => ['Some selected permissions are not assignable for this role.'],
                ]);
            }
        }

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($user->getRawOriginal('image')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->getRawOriginal('image'));
            }
            $validated['image'] = $request->file('image')->store('users', 'public');
        }

        $user->update(collect($validated)->except('direct_permission_slugs')->toArray());

        if ($selectedSlugs !== null) {
            $permissionIds = Permission::whereIn('slug', $selectedSlugs)->pluck('id');
            $user->directPermissions()->sync($permissionIds);
        }

        $user->load(['role.permissions', 'role.assignablePermissions', 'directPermissions']);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'image' => $user->image,
            'role' => $user->role ? [
                'id' => $user->role->id,
                'name' => $user->role->name,
                'slug' => $user->role->slug,
            ] : null,
            'direct_permissions' => $user->directPermissions->pluck('slug')->values(),
            'effective_permissions' => $user->getAllPermissions()->pluck('slug')->values(),
        ]);
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return response()->json([
                'error' => 'You cannot delete yourself.',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User archived successfully.',
        ]);
    }

    public function roles()
    {
        return response()->json(
            Role::all(['id', 'name', 'slug'])
        );
    }

    public function assignablePermissions(Role $role)
    {
        $permissions = $role->assignablePermissions()
            ->get(['permissions.id', 'permissions.name', 'permissions.slug']);

        return response()->json($permissions);
    }

    public function complaints()
    {
        $complaints = Complaint::with(['user:id,name,email'])
            ->latest()
            ->get()
            ->map(function ($complaint) {
                return [
                    'id' => $complaint->id,
                    'subject' => $complaint->subject,
                    'description' => $complaint->description,
                    'status' => $complaint->status,
                    'created_at' => $complaint->created_at,
                    'user' => [
                        'id' => $complaint->user?->id,
                        'name' => $complaint->user?->name,
                        'email' => $complaint->user?->email,
                    ],
                ];
            });

        return response()->json($complaints);
    }

    public function statistics()
    {
        $usersCount = User::count();
        $studentsCount = User::whereHas('role', fn($q) => $q->where('slug', 'student'))->count();
        $staffCount = User::whereHas('role', fn($q) => $q->where('slug', 'staff'))->count();
        $adminCount = User::whereHas('role', fn($q) => $q->where('slug', 'admin'))->count();

        $totalReservations = Reservation::count();
        $todayReservations = Reservation::whereHas('menuMeal', function($q) {
            $q->whereDate('served_at', now());
        })->count();

        $totalComplaints = Complaint::count();
        $pendingComplaints = Complaint::where('status', 'pending')->count();
        $resolvedComplaints = Complaint::where('status', 'resolved')->count();

        $totalMeals = Meal::count();

        // Recent reservations for activity feed
        $recentReservations = Reservation::with(['user:id,name,image', 'menuMeal.meal'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'user_name' => $reservation->user->name,
                    'user_image' => $reservation->user->image,
                    'meal_name' => $reservation->menuMeal->meal->name,
                    'served_at' => $reservation->menuMeal->served_at,
                    'created_at' => $reservation->created_at,
                ];
            });

        return response()->json([
            'users' => [
                'total' => $usersCount,
                'students' => $studentsCount,
                'staff' => $staffCount,
                'admins' => $adminCount,
            ],
            'reservations' => [
                'total' => $totalReservations,
                'today' => $todayReservations,
            ],
            'complaints' => [
                'total' => $totalComplaints,
                'pending' => $pendingComplaints,
                'resolved' => $resolvedComplaints,
            ],
            'meals' => [
                'total' => $totalMeals,
            ],
            'recent_activity' => $recentReservations,
        ]);
    }
}
