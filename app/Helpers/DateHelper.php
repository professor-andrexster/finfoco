<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    public static function formatarDataRelativa(Carbon|string $data): string
    {
        $data = Carbon::parse($data)->startOfDay();
        $hoje = Carbon::today();
        $diff = $hoje->diffInDays($data, false); // negativo = passado

        if ($diff == 0)  return 'hoje';
        if ($diff == 1)  return 'amanhã';
        if ($diff == -1) return 'ontem';
        if ($diff > 1)   return "em {$diff} dias";
        if ($diff < -1)  return 'há ' . abs($diff) . ' dias';

        return $data->format('d/m/Y');
    }

    public static function semaforo(Carbon|string $vencimento): string
    {
        $data = Carbon::parse($vencimento)->startOfDay();
        $hoje = Carbon::today();
        $diff = $hoje->diffInDays($data, false);

        if ($diff < 0)  return 'red';    // vencida
        if ($diff <= 3) return 'yellow'; // vence em até 3 dias (ou hoje)
        return 'green';                  // mais de 3 dias
    }
}
