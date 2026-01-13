<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

use function Symfony\Component\Clock\now;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::latest()->paginate(10);
        $count = User::count();
        $roles = \Spatie\Permission\Models\Role::all();
        return view('users.index', compact('users', 'count', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = \Spatie\Permission\Models\Role::all();
        return view('users.add_content', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'last_login_at' => now(),
        ]);

        if($request->has('roles')){
            $user->assignRole($request->roles);
        }

        return response()->json(['success' => true, 'message' => 'User created successfully.']);
    }

    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return view('users.delete', compact('user'));
    }

    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = \Spatie\Permission\Models\Role::all();
        $userRoles = $user->roles->pluck('name')->toArray();
        return view('users.edit_content', compact('user', 'roles', 'userRoles'));
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8|confirmed']);
            $updateData['password'] = bcrypt($request->password);
        }

        $user->update($updateData);

        if($request->has('roles')){
            $user->syncRoles($request->roles);
        }

        return response()->json(['success' => true, 'message' => 'User updated successfully.']);
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
    }

    public function datatables()
    {
        $users = User::with('roles')->get();
        return response()->json([
            'data' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->map(function($role) {
                        return '<span class="badge badge-soft-primary me-1 text-capitalize">'.$role->name.'</span>';
                    })->implode(''),
                    'created' => $user->created_at->format('d M Y, h:i a'),
                    'last_activity' => $user->last_login_formatted ?? '-',
                    'status' => $user->status ?? 'Active',
                ];
            })
        ]);
    }
}
