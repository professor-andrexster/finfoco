<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar — FinFoco</title>
    <link rel="icon" href="/icon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300..700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #F7F7FD; }
        .input { width:100%; border: 1px solid #E4E4F0; border-radius: 12px; padding: 12px 16px; font-size:15px; color:#1E1B4B; background:#fff; outline:none; transition: border-color .15s, box-shadow .15s; }
        .input:focus { border-color: #6366F1; box-shadow: 0 0 0 3px rgba(99,102,241,.12); }
        .btn { background:#6366F1; color:#fff; font-weight:700; font-size:16px; border-radius:14px; padding:14px; width:100%; border:none; cursor:pointer; transition: background .15s, box-shadow .15s; }
        .btn:hover { background:#4F46E5; box-shadow: 0 4px 14px rgba(99,102,241,.35); }
        .error { color:#DC2626; font-size:13px; margin-top:4px; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4">

<div style="width:100%;max-width:400px">
    {{-- Logo --}}
    <div class="text-center mb-10">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-3 justify-center">
            <svg width="40" height="40" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                <circle cx="32" cy="32" r="26" fill="none" stroke="#E0DFFA" stroke-width="5"/>
                <circle cx="32" cy="32" r="16" fill="none" stroke="#6366F1" stroke-width="5"/>
                <circle cx="32" cy="32" r="7" fill="#22C55E"/>
            </svg>
            <span style="font-size:26px;font-weight:700;letter-spacing:-0.03em">
                <span style="color:#1E1B4B">Fin</span><span style="color:#6366F1">Foco</span>
            </span>
        </a>
        <p style="color:#9794B8;font-size:14px;margin-top:8px">Controle financeiro para mente ativa</p>
    </div>

    {{-- Card --}}
    <div style="background:#fff;border-radius:20px;padding:36px;box-shadow:0 2px 16px rgba(99,102,241,.1),0 0 0 1px rgba(99,102,241,.06)">
        <h1 style="font-size:20px;font-weight:700;color:#1E1B4B;margin-bottom:24px">Entrar na sua conta</h1>

        @if($errors->any())
        <div style="background:#FEF2F2;border-left:3px solid #DC2626;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#991B1B;font-size:14px">
            @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
        </div>
        @endif

        <form action="{{ route('login') }}" method="POST" style="display:flex;flex-direction:column;gap:16px">
            @csrf

            <div>
                <label style="display:block;font-size:13px;font-weight:600;color:#9794B8;margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em">E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" class="input" placeholder="seu@email.com" autocomplete="email" autofocus required>
            </div>

            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                    <label style="font-size:13px;font-weight:600;color:#9794B8;text-transform:uppercase;letter-spacing:.05em">Senha</label>
                </div>
                <input type="password" name="password" class="input" placeholder="••••••••" autocomplete="current-password" required>
            </div>

            <label style="display:flex;align-items:center;gap:8px;font-size:14px;color:#6B6B8A;cursor:pointer">
                <input type="checkbox" name="remember" style="accent-color:#6366F1;width:16px;height:16px">
                Lembrar de mim
            </label>

            <button type="submit" class="btn" style="margin-top:4px">Entrar</button>
        </form>
    </div>

    <p style="text-align:center;margin-top:20px;font-size:14px;color:#9794B8">
        Não tem conta?
        <a href="{{ route('register') }}" style="color:#6366F1;font-weight:600;text-decoration:none">Criar conta grátis</a>
    </p>
</div>

</body>
</html>
