<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar — FinFoco</title>
    <link rel="icon" href="/icon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300..700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #F7F7FD; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px 16px; color: #1E1B4B; }
        .card { background: #fff; border-radius: 20px; padding: 40px; box-shadow: 0 2px 20px rgba(99,102,241,.08), 0 0 0 1px rgba(99,102,241,.06); }
        label { display: block; font-size: 12px; font-weight: 600; color: #9794B8; margin-bottom: 6px; text-transform: uppercase; letter-spacing: .06em; }
        input[type="email"], input[type="password"] {
            width: 100%; border: 1.5px solid #E4E4F0; border-radius: 12px;
            padding: 12px 16px; font-size: 15px; color: #1E1B4B; background: #fff;
            outline: none; transition: border-color .15s, box-shadow .15s; font-family: inherit;
        }
        input:focus { border-color: #6366F1; box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
        .btn { background: #6366F1; color: #fff; font-weight: 700; font-size: 16px; border-radius: 14px;
               padding: 14px; width: 100%; border: none; cursor: pointer; font-family: inherit;
               transition: background .15s, box-shadow .15s; }
        .btn:hover { background: #4F46E5; box-shadow: 0 4px 14px rgba(99,102,241,.3); }
        .error-box { background: #FEF2F2; border-left: 3px solid #DC2626; border-radius: 10px;
                     padding: 12px 16px; margin-bottom: 20px; color: #DC2626; font-size: 14px; }
    </style>
</head>
<body>
<div style="width:100%;max-width:400px">

    {{-- Logo --}}
    <div style="text-align:center;margin-bottom:36px">
        <a href="{{ route('login') }}" style="display:inline-flex;align-items:center;gap:12px;text-decoration:none">
            <svg width="44" height="44" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                <circle cx="32" cy="32" r="26" fill="none" stroke="#E0DFFA" stroke-width="5"/>
                <circle cx="32" cy="32" r="16" fill="none" stroke="#6366F1" stroke-width="5"/>
                <circle cx="32" cy="32" r="7" fill="#22C55E"/>
            </svg>
            <span style="font-size:28px;font-weight:800;letter-spacing:-0.04em">
                <span style="color:#1E1B4B">Fin</span><span style="color:#6366F1">Foco</span>
            </span>
        </a>
        <p style="color:#9794B8;font-size:14px;margin-top:10px">Controle financeiro para mente ativa</p>
    </div>

    <div class="card">
        <h1 style="font-size:22px;font-weight:700;margin-bottom:28px">Entrar na sua conta</h1>

        @if($errors->any())
        <div class="error-box">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
        @endif

        @if(session('sucesso'))
        <div style="background:#F0FDF4;border-left:3px solid #16A34A;border-radius:10px;padding:12px 16px;margin-bottom:20px;color:#16A34A;font-size:14px">
            {{ session('sucesso') }}
        </div>
        @endif

        <form action="{{ route('login') }}" method="POST" style="display:flex;flex-direction:column;gap:18px">
            @csrf
            <div>
                <label>E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       placeholder="seu@email.com" autocomplete="email" autofocus required>
            </div>
            <div>
                <label>Senha</label>
                <input type="password" name="password" placeholder="••••••••"
                       autocomplete="current-password" required>
            </div>
            <label style="display:flex;align-items:center;gap:8px;font-size:14px;color:#9794B8;text-transform:none;letter-spacing:0;cursor:pointer">
                <input type="checkbox" name="remember" style="accent-color:#6366F1;width:16px;height:16px">
                Lembrar de mim
            </label>
            <button type="submit" class="btn" style="margin-top:4px">Entrar</button>
        </form>
    </div>

    <p style="text-align:center;margin-top:24px;font-size:14px;color:#9794B8">
        Não tem conta?
        <a href="{{ route('register') }}" style="color:#6366F1;font-weight:700;text-decoration:none">Criar conta grátis →</a>
    </p>
</div>
</body>
</html>
