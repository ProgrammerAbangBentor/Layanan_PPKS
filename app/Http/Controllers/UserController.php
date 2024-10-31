<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role; // Pastikan ini diimpor

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = DB::table('users')
            ->when($request->input('name'), function ($query, $name) {
                $query->where('name', 'like', '%' . $name . '%')
                    ->orWhere('email', 'like', '%' . $name . '%');
            })
            ->orderByRaw("CASE
                WHEN role = 'admin' THEN 1
                WHEN role = 'anggota' THEN 2
                WHEN role = 'user' THEN 3
                ELSE 4
            END") // Membuat agar usernya berurutan
            ->paginate(10);

        return view('pages.users.index', compact('users'));
    }

    public function create()
    {
        // Ambil semua role untuk dropdown
        $roles = Role::all();
        return view('pages.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required|exists:roles,name', // Memastikan role ada di tabel roles
        ]);

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        // Menetapkan role setelah menyimpan pengguna
        $user->assignRole($request->role);

        return redirect()->route('user.index')->with('success', 'User created successfully');
    }

    public function show($id)
    {
        return view('pages.users.show');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        // Ambil semua role untuk dropdown
        $roles = Role::all();
        return view('pages.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'role' => 'required|exists:roles,name', // Memastikan role ada di tabel roles
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;

        // Mengupdate role
        $user->syncRoles($request->role); // Sync role

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('user.index')->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('user.index')->with('success', 'User deleted successfully');
    }
}
