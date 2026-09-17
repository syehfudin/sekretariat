<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnggotaController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $anggota = $user->isSuperadmin()
            ? Anggota::with('users.role')->orderBy('nama_lengkap')->get()
            : Anggota::forUser($user)->with('users.role')->orderBy('nama_lengkap')->get();

        return view('anggota.index', [
            'anggota' => $anggota,
            'canManage' => $user->canManageAnggota(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->user()->canManageAnggota()) {
            abort(403, 'Anda hanya dapat melihat data');
        }

        return view('anggota.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $request->user()->canManageAnggota()) {
            abort(403, 'Anda hanya dapat melihat data');
        }

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'pekerjaan' => ['nullable', 'string', 'max:150'],
        ]);

        $anggota = Anggota::create($validated);

        // Link anggota ke user yang membuatnya
        $request->user()->anggota()->attach($anggota->id);

        return redirect()->route('anggota.index')->with('success', 'Anggota berhasil ditambahkan');
    }

    public function edit(Request $request, Anggota $anggota): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->canManageAnggota()) {
            abort(403, 'Anda hanya dapat melihat data');
        }

        if (! $user->isSuperadmin() && ! $user->anggota()->where('anggota.id', $anggota->id)->exists()) {
            abort(403, 'Anggota ini bukan di bawah Anda');
        }

        return view('anggota.edit', ['anggota' => $anggota]);
    }

    public function update(Request $request, Anggota $anggota): RedirectResponse
    {
        if (! $request->user()->canManageAnggota()) {
            abort(403, 'Anda hanya dapat melihat data');
        }

        if (! $request->user()->isSuperadmin() && ! $request->user()->anggota()->where('anggota.id', $anggota->id)->exists()) {
            abort(403, 'Anggota ini bukan di bawah Anda');
        }

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'pekerjaan' => ['nullable', 'string', 'max:150'],
        ]);

        $anggota->update($validated);

        return redirect()->route('anggota.index')->with('success', 'Anggota berhasil diperbarui');
    }
}