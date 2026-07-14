<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#F7F7FD;font-family:Arial,Helvetica,sans-serif;color:#1E1B4B">
<div style="max-width:520px;margin:0 auto;padding:32px 16px">

    <div style="text-align:center;margin-bottom:24px">
        <span style="font-size:24px;font-weight:800">
            <span style="color:#1E1B4B">Norte</span>
        </span>
    </div>

    <div style="background:#ffffff;border-radius:16px;padding:28px;border:1px solid #E4E4F0">
        <p style="margin:0 0 4px;font-size:16px">Bom dia, <strong>{{ $user->name }}</strong>! ☀️</p>
        <p style="margin:0 0 20px;font-size:14px;color:#555">
            Seu dia de {{ today()->translatedFormat('l, d \d\e F') }}, num relance — sem precisar lembrar de nada:
        </p>

        @if($compromissos->isNotEmpty())
        <p style="margin:0 0 8px;font-size:13px;font-weight:bold;color:#6366F1;text-transform:uppercase">📅 Compromissos</p>
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:18px">
            @foreach($compromissos as $c)
            <tr>
                <td width="70" style="padding:6px 0;font-size:14px;font-weight:bold;color:#6366F1;border-bottom:1px solid #F3F3FB">
                    {{ $c->hora ? substr($c->hora, 0, 5) : 'Dia todo' }}
                </td>
                <td style="padding:6px 0;font-size:14px;border-bottom:1px solid #F3F3FB">{{ $c->titulo }}</td>
            </tr>
            @endforeach
        </table>
        @endif

        @if($rotinas->isNotEmpty())
        <p style="margin:0 0 8px;font-size:13px;font-weight:bold;color:#6366F1;text-transform:uppercase">🔁 Rotinas de hoje</p>
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:18px">
            @foreach($rotinas as $r)
            <tr>
                <td width="70" style="padding:6px 0;font-size:14px;font-weight:bold;color:#6366F1;border-bottom:1px solid #F3F3FB">
                    {{ $r->hora ? substr($r->hora, 0, 5) : '—' }}
                </td>
                <td style="padding:6px 0;font-size:14px;border-bottom:1px solid #F3F3FB">
                    {{ $r->titulo }}
                    @if($r->streak() > 0)
                        <span style="color:#6366F1;font-weight:bold;font-size:12px">&nbsp;🔥 {{ $r->streak() }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
        @endif

        <div style="text-align:center;margin-top:24px">
            <a href="{{ route('agenda.index') }}"
               style="display:inline-block;background:#6366F1;color:#ffffff;font-weight:bold;font-size:15px;
                      padding:12px 28px;border-radius:12px;text-decoration:none">
                Abrir minha agenda
            </a>
        </div>
        <p style="text-align:center;font-size:12px;color:#9794B8;margin:12px 0 0">
            Um passo de cada vez. Você consegue. 💜
        </p>
    </div>

    <p style="text-align:center;font-size:12px;color:#9794B8;margin-top:20px">
        Você recebe este resumo porque tem compromissos ou rotinas hoje no Norte.<br>
        {{ config('app.url') }}
    </p>
</div>
</body>
</html>
