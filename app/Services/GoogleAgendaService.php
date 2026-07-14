<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Lê a agenda do Google via o "endereço secreto em iCal" do usuário
 * (Configurações do Google Agenda → Integrar agenda). Sem OAuth: o link
 * já é autenticado por token na própria URL.
 */
class GoogleAgendaService
{
    /** Eventos do Google numa data. Retorna [['titulo','hora'(H:i|null)], ...] */
    public function eventosDoDia(int $userId, Carbon $dia): array
    {
        $eventos = [];

        foreach ($this->eventosBrutos($userId) as $e) {
            if ($this->ocorreEm($e, $dia)) {
                $eventos[] = ['titulo' => $e['titulo'], 'hora' => $e['hora']];
            }
        }

        usort($eventos, fn($a, $b) => strcmp($a['hora'] ?? '', $b['hora'] ?? ''));

        return $eventos;
    }

    /** Baixa e parseia o ICS, com cache de 15 min. Nunca lança exceção. */
    private function eventosBrutos(int $userId): array
    {
        $url = Setting::where('user_id', $userId)->where('chave', 'google_ics_url')->value('valor');
        if (!$url) {
            return [];
        }

        return Cache::remember("gcal_{$userId}", 900, function () use ($url) {
            try {
                $resposta = Http::timeout(6)->get($url);
                if (!$resposta->successful()) {
                    return [];
                }
                return $this->parsearIcs($resposta->body());
            } catch (\Throwable) {
                return [];
            }
        });
    }

    private function parsearIcs(string $ics): array
    {
        // Unfold: linha começando com espaço/tab continua a anterior (RFC 5545)
        $ics    = preg_replace("/\r?\n[ \t]/", '', $ics);
        $linhas = preg_split("/\r?\n/", $ics);

        $eventos = [];
        $atual   = null;

        foreach ($linhas as $linha) {
            if ($linha === 'BEGIN:VEVENT') {
                $atual = ['titulo' => '(sem título)', 'hora' => null, 'data' => null,
                          'rrule' => null, 'exdates' => [], 'cancelado' => false];
                continue;
            }
            if ($linha === 'END:VEVENT') {
                if ($atual && $atual['data'] && !$atual['cancelado']) {
                    // Carbon não sobrevive bem ao cache em arquivo — guarda string
                    $atual['data'] = $atual['data']->toDateString();
                    $eventos[] = $atual;
                }
                $atual = null;
                continue;
            }
            if ($atual === null || !str_contains($linha, ':')) {
                continue;
            }

            [$chave, $valor] = explode(':', $linha, 2);
            $nome = strtoupper(explode(';', $chave, 2)[0]);

            match ($nome) {
                'SUMMARY' => $atual['titulo'] = trim(str_replace(['\\,', '\\;', '\\n'], [',', ';', ' '], $valor)) ?: '(sem título)',
                'DTSTART' => [$atual['data'], $atual['hora']] = $this->parsearDtStart($chave, $valor),
                'RRULE'   => $atual['rrule'] = $this->parsearRrule($valor),
                'EXDATE'  => $atual['exdates'] = array_merge($atual['exdates'], $this->parsearExdate($chave, $valor)),
                'STATUS'  => $atual['cancelado'] = strtoupper(trim($valor)) === 'CANCELLED',
                default   => null,
            };
        }

        return $eventos;
    }

    /** @return array{0: ?Carbon, 1: ?string} [data local, hora H:i ou null se dia todo] */
    private function parsearDtStart(string $chave, string $valor): array
    {
        $valor = trim($valor);

        // Dia todo: DTSTART;VALUE=DATE:20260713
        if (str_contains($chave, 'VALUE=DATE') || preg_match('/^\d{8}$/', $valor)) {
            try {
                return [Carbon::createFromFormat('Ymd', $valor, config('app.timezone'))->startOfDay(), null];
            } catch (\Throwable) {
                return [null, null];
            }
        }

        // Com hora: 20260713T140000 (com TZID ou Z no fim = UTC)
        try {
            if (str_ends_with($valor, 'Z')) {
                $dt = Carbon::createFromFormat('Ymd\THis\Z', $valor, 'UTC')->tz(config('app.timezone'));
            } else {
                $tz = 'America/Sao_Paulo';
                if (preg_match('/TZID=([^;:]+)/', $chave, $m)) {
                    $tz = $m[1];
                }
                $dt = Carbon::createFromFormat('Ymd\THis', $valor, $tz)->tz(config('app.timezone'));
            }
            return [$dt->copy()->startOfDay(), $dt->format('H:i')];
        } catch (\Throwable) {
            return [null, null];
        }
    }

    private function parsearRrule(string $valor): array
    {
        $regra = [];
        foreach (explode(';', trim($valor)) as $par) {
            if (str_contains($par, '=')) {
                [$k, $v] = explode('=', $par, 2);
                $regra[strtoupper($k)] = strtoupper($v);
            }
        }
        return $regra;
    }

    private function parsearExdate(string $chave, string $valor): array
    {
        $datas = [];
        foreach (explode(',', trim($valor)) as $v) {
            if (preg_match('/^(\d{8})/', trim($v), $m)) {
                $datas[] = $m[1];
            }
        }
        return $datas;
    }

    /** O evento (único ou recorrente, aproximação das regras comuns) ocorre nesta data? */
    private function ocorreEm(array $e, Carbon $dia): bool
    {
        $inicio = Carbon::parse($e['data'])->startOfDay();

        if ($dia->lt($inicio)) {
            return false;
        }
        if (in_array($dia->format('Ymd'), $e['exdates'], true)) {
            return false;
        }

        $rrule = $e['rrule'];
        if (!$rrule) {
            return $dia->isSameDay($inicio);
        }

        // UNTIL (só a parte da data já basta como aproximação)
        if (isset($rrule['UNTIL']) && preg_match('/^(\d{8})/', $rrule['UNTIL'], $m)) {
            if ($dia->format('Ymd') > $m[1]) {
                return false;
            }
        }

        $intervalo = max(1, (int) ($rrule['INTERVAL'] ?? 1));

        return match ($rrule['FREQ'] ?? '') {
            'DAILY'   => $inicio->diffInDays($dia) % $intervalo === 0,
            'WEEKLY'  => $this->casaSemanal($rrule, $inicio, $dia, $intervalo),
            'MONTHLY' => $dia->day === $inicio->day
                         && ($inicio->diffInMonths($dia) % $intervalo === 0),
            'YEARLY'  => $dia->day === $inicio->day && $dia->month === $inicio->month,
            default   => $dia->isSameDay($inicio),
        };
    }

    private function casaSemanal(array $rrule, Carbon $inicio, Carbon $dia, int $intervalo): bool
    {
        $mapa = ['MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6, 'SU' => 7];

        $diasSemana = isset($rrule['BYDAY'])
            ? array_values(array_intersect_key($mapa, array_flip(explode(',', $rrule['BYDAY']))))
            : [$inicio->dayOfWeekIso];

        if (!in_array($dia->dayOfWeekIso, $diasSemana, true)) {
            return false;
        }

        return $inicio->copy()->startOfWeek()->diffInWeeks($dia->copy()->startOfWeek()) % $intervalo === 0;
    }
}
