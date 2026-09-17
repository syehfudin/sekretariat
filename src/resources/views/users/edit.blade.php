@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="card" style="max-width:600px;">
    <div class="card-header">
        <h2>Edit User #{{ $user->id }}</h2>
    </div>
    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf
        @method('PUT')
        <label for="username">Username <span style="color:#dc2626">*</span></label>
        <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" required>

        <label for="nama_lengkap">Nama Lengkap <span style="color:#dc2626">*</span></label>
        <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $user->nama_lengkap ?? $user->name) }}" required>

        <label for="email">Email</label>
        <input type="text" id="email" name="email" value="{{ old('email', $user->email) }}">

        <label for="role_id">Role <span style="color:#dc2626">*</span></label>
        <select id="role_id" name="role_id" required>
            <option value="">-- Pilih Role --</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                    {{ $role->nama }} ({{ $role->kode }})
                </option>
            @endforeach
        </select>

        <label for="password">Password Baru <span style="color:#94a3b8; font-weight:400;">(kosongkan jika tidak diubah)</span></label>
        <input type="password" id="password" name="password">

        <div class="form-actions">
            <button type="submit" class="btn">Update</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection