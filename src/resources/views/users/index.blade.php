@extends('layouts.app')

@section('title', 'Master User')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Master User</h2>
        <a href="{{ route('users.create') }}" class="btn btn-primary">+ Tambah User</a>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width:60px">ID</th>
                <th style="width:140px">Username</th>
                <th>Nama Lengkap</th>
                <th style="width:180px">Role</th>
                <th style="width:120px">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td><strong>{{ $item->username }}</strong></td>
                    <td>{{ $item->nama_lengkap ?: $item->name }}</td>
                    <td>
                        @if ($item->role)
                            @if ($item->role->kode === 'superadmin')
                                <span class="badge badge-superadmin">{{ $item->role->nama }}</span>
                            @elseif ($item->role->isSekre())
                                <span class="badge badge-sekre">{{ $item->role->nama }}</span>
                            @elseif ($item->role->isKa())
                                <span class="badge badge-ka">{{ $item->role->nama }}</span>
                            @else
                                <span class="badge">{{ $item->role->nama }}</span>
                            @endif
                        @else
                            <span class="readonly-badge">Tanpa Role</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('users.edit', $item) }}" class="btn btn-sm">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center; padding:32px; color:#94a3b8;">Belum ada user</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection