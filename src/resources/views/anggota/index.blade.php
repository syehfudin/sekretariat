@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Master Anggota</h2>
        @if ($canManage)
            <a href="{{ route('anggota.create') }}" class="btn btn-primary">+ Tambah Anggota</a>
        @endif
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width:60px">ID</th>
                <th>Nama Lengkap</th>
                <th>Pekerjaan</th>
                <th style="width:180px">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($anggota as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->nama_lengkap }}</td>
                    <td>{{ $item->pekerjaan ?: '-' }}</td>
                    <td>
                        @if ($canManage)
                            <a href="{{ route('anggota.edit', $item) }}" class="btn btn-sm">Edit</a>
                        @else
                            <span class="readonly-badge">Hanya Lihat</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center; padding:32px; color:#94a3b8;">
                        Belum ada anggota{{ $canManage ? ' — tambahkan anggota pertama' : '' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection