@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
<div class="card" style="max-width:600px;">
    <div class="card-header">
        <h2>Tambah User</h2>
    </div>
    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <label for="username">Username <span style="color:#dc2626">*</span></label>
        <input type="text" id="username" name="username" value="{{ old('username') }}" required>

        <label for="nama_lengkap">Nama Lengkap <span style="color:#dc2626">*</span></label>
        <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required>

        <label for="email">Email</label>
        <input type="text" id="email" name="email" value="{{ old('email') }}">

        <label for="role_id">Role <span style="color:#dc2626">*</span></label>
        <select id="role_id" name="role_id" required>
            <option value="">-- Pilih Role --</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                    {{ $role->nama }} ({{ $role->kode }})
                </option>
            @endforeach
        </select>

        <label for="password">Password <span style="color:#dc2626">*</span></label>
        <input type="password" id="password" name="password" required>

        <div class="form-actions">
            <button type="submit" class="btn">Simpan</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection