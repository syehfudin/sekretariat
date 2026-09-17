<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Sistem Informasi Sekretariat</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;
        }
        .card {
            background: #fff; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,.3);
            width: 100%; max-width: 400px; padding: 40px;
        }
        .brand { text-align: center; margin-bottom: 32px; }
        .brand h1 { font-size: 20px; color: #1e3a8a; margin-bottom: 4px; }
        .brand p { font-size: 13px; color: #64748b; }
        label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; }
        input[type=text], input[type=password] {
            width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px;
            font-size: 14px; margin-bottom: 16px; transition: border-color .15s;
        }
        input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
        .remember { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #475569; margin-bottom: 20px; }
        .btn {
            width: 100%; padding: 12px; background: #1e3a8a; color: #fff; border: none;
            border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;
        }
        .btn:hover { background: #1e40af; }
        .error {
            background: #fef2f2; border: 1px solid #fecaca; color: #dc2626;
            padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">
            <h1>Sistem Informasi Sekretariat</h1>
            <p>Silakan login untuk melanjutkan</p>
        </div>

        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <div class="remember">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember" style="margin:0; font-weight:400;">Ingat saya</label>
            </div>

            <button type="submit" class="btn">Masuk</button>
        </form>
    </div>
</body>
</html>