<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::with('role')->orderBy('id')->get(),
            'roles' => Role::orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('users.create', [
            'roles' => Role::orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:5'],
        ]);

        $validated['name'] = $validated['nama_lengkap'];
        User::create($validated);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user,
            'roles' => Role::orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:users,username,'.$user->id],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:5'],
        ]);

        $data = [
            'username' => $validated['username'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
            'name' => $validated['nama_lengkap'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui');
    }
}