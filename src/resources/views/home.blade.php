<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home - Sistem Informasi Sekretariat</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f1f5f9; min-height: 100vh; }
        nav { background: #1e3a8a; color: #fff; padding: 14px 32px; display: flex; align-items: center; justify-content: space-between; }
        nav .title { font-size: 17px; font-weight: 700; }
        nav .right { display: flex; align-items: center; gap: 20px; font-size: 14px; }
        nav a.menu { color: #cbd5e1; text-decoration: none; font-size: 14px; }
        nav a.menu:hover { color: #fff; }
        nav form { display: inline; }
        .logout-btn { background: rgba(255,255,255,.15); color: #fff; border: none; padding: 7px 16px; border-radius: 6px; font-size: 13px; cursor: pointer; }
        .logout-btn:hover { background: rgba(255,255,255,.25); }
        .container { max-width: 1100px; margin: 32px auto; padding: 0 24px; }
        .welcome { background: #fff; border-radius: 12px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        .welcome h2 { color: #1e3a8a; font-size: 22px; margin-bottom: 8px; }
        .welcome p { color: #64748b; font-size: 14px; }
        .role-badge { display: inline-block; background: #eff6ff; color: #1e3a8a; padding: 3px 12px; border-radius: 99px; font-size: 12px; font-weight: 600; margin-top: 8px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; margin-top: 24px; }
        .stat { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,.1); text-decoration: none; display: block; }
        .stat .num { font-size: 28px; font-weight: 700; color: #1e3a8a; }
        .stat .label { font-size: 13px; color: #64748b; margin-top: 4px; }
    </style>
</head>
<body>
    <nav>
        <div class="title">Sistem Informasi Sekretariat</div>
        <div class="right">
            <a class="menu" href="{{ route('home') }}">Home</a>
            @if (auth()->user()->isSuperadmin())
                <a class="menu" href="{{ route('roles.index') }}">Master Role</a>
            @endif
            <a class="menu" href="{{ route('anggota.index') }}">Master Anggota</a>
            <span>{{ auth()->user()->nama_lengkap }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">Keluar</button>
            </form>
        </div>
    </nav>

    <div class="container">
        <div class="welcome">
            <h2>Selamat datang, {{ auth()->user()->nama_lengkap }}</h2>
            <p>Dashboard Sistem Informasi Sekretariat</p>
            @if (auth()->user()->role)
                <span class="role-badge">{{ auth()->user()->role->nama }}</span>
            @endif
        </div>

        <div class="grid">
            <a class="stat" href="{{ route('anggota.index') }}">
                <div class="num">{{ $totalAnggota ?? 0 }}</div>
                <div class="label">Total Anggota</div>
            </a>
            <div class="stat">
                <div class="num">&mdash;</div>
                <div class="label">Surat Masuk</div>
            </div>
            <div class="stat">
                <div class="num">&mdash;</div>
                <div class="label">Surat Keluar</div>
            </div>
            <div class="stat">
                <div class="num">&mdash;</div>
                <div class="label">Agenda</div>
            </div>
        </div>
    </div>
</body>
</html>