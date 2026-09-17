<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistem Informasi Sekretariat')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f1f5f9; min-height: 100vh; }
        nav { background: #1e3a8a; color: #fff; padding: 14px 32px; display: flex; align-items: center; justify-content: space-between; }
        nav .title { font-size: 17px; font-weight: 700; }
        nav .right { display: flex; align-items: center; gap: 20px; font-size: 14px; }
        nav a.menu { color: #cbd5e1; text-decoration: none; font-size: 14px; }
        nav a.menu:hover, nav a.menu.active { color: #fff; font-weight: 600; }
        nav form { display: inline; }
        .logout-btn { background: rgba(255,255,255,.15); color: #fff; border: none; padding: 7px 16px; border-radius: 6px; font-size: 13px; cursor: pointer; }
        .logout-btn:hover { background: rgba(255,255,255,.25); }
        .container { max-width: 1100px; margin: 32px auto; padding: 0 24px; }
        .card { background: #fff; border-radius: 12px; padding: 28px; box-shadow: 0 1px 3px rgba(0,0,0,.1); margin-bottom: 20px; }
        .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .card-header h2 { color: #1e3a8a; font-size: 19px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { text-align: left; padding: 10px 12px; background: #f8fafc; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; border-bottom: 2px solid #e2e8f0; }
        td { padding: 11px 12px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        tr:hover td { background: #f8fafc; }
        .btn { display: inline-block; padding: 9px 18px; background: #1e3a8a; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; }
        .btn:hover { background: #1e40af; }
        .btn-sm { padding: 6px 14px; font-size: 13px; }
        .btn-secondary { background: #64748b; }
        .btn-secondary:hover { background: #475569; }
        .alert { padding: 12px 16px; border-radius: 8px; font-size: 14px; margin-bottom: 20px; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
        label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; }
        input[type=text], input[type=password], select, textarea {
            width: 100%; max-width: 480px; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px;
            font-size: 14px; margin-bottom: 16px;
        }
        input:focus, select:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
        .readonly-badge { background: #f1f5f9; color: #64748b; padding: 4px 12px; border-radius: 99px; font-size: 12px; }
        .form-actions { display: flex; gap: 10px; margin-top: 8px; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 12px; font-weight: 600; background: #eff6ff; color: #1e3a8a; }
        .badge-superadmin { background: #fef3c7; color: #92400e; }
        .badge-sekre { background: #dcfce7; color: #166534; }
        .badge-ka { background: #e0e7ff; color: #3730a3; }
    </style>
</head>
<body>
    <nav>
        <div class="title">Sistem Informasi Sekretariat</div>
        <div class="right">
            <a class="menu {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
            @if (auth()->user()->isSuperadmin())
                <a class="menu {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">Master User</a>
                <a class="menu {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">Master Role</a>
            @endif
            <a class="menu {{ request()->routeIs('anggota.*') ? 'active' : '' }}" href="{{ route('anggota.index') }}">Master Anggota</a>
            <span>{{ auth()->user()->nama_lengkap }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">Keluar</button>
            </form>
        </div>
    </nav>

    <div class="container">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        @yield('content')
    </div>
</body>
</html>