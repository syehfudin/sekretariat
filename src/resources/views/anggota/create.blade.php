@extends('layouts.app')

@section('title', 'Tambah Anggota')

@section('content')
<div class="card" style="max-width:600px;">
    <div class="card-header">
        <h2>Tambah Anggota</h2>
    </div>
    <form method="POST" action="{{ route('anggota.store') }}">
        @csrf
        <label for="nama_lengkap">Nama Lengkap <span style="color:#dc2626">*</span></label>
        <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required>

        <label for="pekerjaan">Pekerjaan</label>
        <input type="text" id="pekerjaan" name="pekerjaan" value="{{ old('pekerjaan') }}">

        <div class="form-actions">
            <button type="submit" class="btn">Simpan</button>
            <a href="{{ route('anggota.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection