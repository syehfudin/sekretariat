<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $totalAnggota = $user->isSuperadmin()
            ? Anggota::count()
            : Anggota::forUser($user)->count();

        return view('home', ['totalAnggota' => $totalAnggota]);
    }
}