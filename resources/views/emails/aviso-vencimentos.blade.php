<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#F7F7FD;font-family:Arial,Helvetica,sans-serif;color:#1E1B4B">
<div style="max-width:520px;margin:0 auto;padding:32px 16px">

    <div style="text-align:center;margin-bottom:24px">
        <span style="font-size:24px;font-weight:800">
            <span style="color:#1E1B4B">Fin</span><span style="color:#6366F1">Foco</span>
        </span>
    </div>

    <div style="background:#ffffff;border-radius:16px;padding:28px;border:1px solid #E4E4F0">
        <p style="margin:0 0 16px;font-size:16px">Olá, <strong>{{ $user->name }}</strong>!</p>
        <p style="margin:0 0 20px;font-size:14px;color:#555">Um lembrete rápido das suas contas:</p>

        @if($atrasadas->isNotEmpty())
        <p style="margin:0 0 8px;font-size:13px;font-weight:bold;color:#DC2626;text-transform:uppercase">🔴 Atrasadas</p>
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:18px">
            @foreach($atrasadas as $b)
            <tr>
                <td style="padding:6px 0;font-size:14px;border-bottom:1px solid #F3F3FB">{{ $b->descricao }}</td>
                <td align="right" style="padding:6px 0;font-size:14px;font-weight:bold;color:#DC2626;border-bottom:1px solid #F3F3FB">
                    R$ {{ number_format($b->restante(), 2, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </table>
        @endif

        @if($vencemHoje->isNotEmpty())
        <p style="margin:0 0 8px;font-size:13px;font-weight:bold;color:#DC2626;text-transform:uppercase">Vencem hoje</p>
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:18px">
            @foreach($vencemHoje as $b)
            <tr>
                <td style="padding:6px 0;font-size:14px;border-bottom:1px solid #F3F3FB">{{ $b->descricao }}</td>
                <td align="right" style="padding:6px 0;font-size:14px;font-weight:bold;color:#DC2626;border-bottom:1px solid #F3F3FB">
                    R$ {{ number_format($b->restante(), 2, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </table>
        @endif

        @if($vencemAmanha->isNotEmpty())
        <p style="margin:0 0 8px;font-size:13px;font-weight:bold;color:#D97706;text-transform:uppercase">🟡 Vencem amanhã</p>
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:18px">
            @foreach($vencemAmanha as $b)
            <tr>
                <td style="padding:6px 0;font-size:14px;border-bottom:1px solid #F3F3FB">{{ $b->descricao }}</td>
                <td align="right" style="padding:6px 0;font-size:14px;font-weight:bold;color:#D97706;border-bottom:1px solid #F3F3FB">
                    R$ {{ number_format($b->restante(), 2, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </table>
        @endif

        <div style="text-align:center;margin-top:24px">
            <a href="{{ route('bills.index') }}"
               style="display:inline-block;background:#6366F1;color:#ffffff;font-weight:bold;font-size:15px;
                      padding:12px 28px;border-radius:12px;text-decoration:none">
                Ver minhas contas
            </a>
        </div>
    </div>

    <p style="text-align:center;font-size:12px;color:#9794B8;margin-top:20px">
        Você recebe este aviso porque tem contas vencendo no FinFoco.<br>
        {{ config('app.url') }}
    </p>
</div>
</body>
</html>
