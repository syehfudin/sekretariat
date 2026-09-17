@extends('layouts.app')

@section('title', 'Master Role')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Master Role</h2>
        <span class="readonly-badge">{{ $roles->sum('users_count') }} user terdaftar</span>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width:60px">ID</th>
                <th style="width:140px">Kode</th>
                <th style="width:160px">Nama</th>
                <th>Keterangan</th>
                <th style="width:100px">Jml User</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($roles as $role)
                <tr>
                    <td>{{ $role->id }}</td>
                    <td>
                        @if ($role->kode === 'superadmin')
                            <span class="badge badge-superadmin">{{ $role->kode }}</span>
                        @elseif ($role->isSekre())
                            <span class="badge badge-sekre">{{ $role->kode }}</span>
                        @elseif ($role->isKa())
                            <span class="badge badge-ka">{{ $role->kode }}</span>
                        @else
                            <span class="badge">{{ $role->kode }}</span>
                        @endif
                    </td>
                    <td>{{ $role->nama }}</td>
                    <td>{{ $role->keterangan ?: '-' }}</td>
                    <td>{{ $role->users_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection