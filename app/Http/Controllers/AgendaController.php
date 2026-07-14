<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentStep;
use App\Models\Routine;
use App\Models\Setting;
use App\Models\User;
use App\Services\GoogleAgendaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AgendaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $dia = $request->filled('data')
                ? Carbon::parse($request->query('data'))->startOfDay()
                : today();
        } catch (\Throwable) {
            $dia = today();
        }

        $compromissos = Appointment::where('user_id', auth()->id())
            ->doDia($dia)
            ->with('steps')
            ->get();

        $rotinas = Routine::where('user_id', auth()->id())
            ->doDia($dia)
            ->with(['checks' => fn($q) => $q->whereDate('data', '>=', today()->subDays(400))])
            ->get();

        // Token do feed iCal (Google Calendar assina esta URL) — criado na 1ª visita
        $icsToken = Setting::get('ics_token');
        if (!$icsToken) {
            $icsToken = Str::random(40);
            Setting::set('ics_token', $icsToken);
        }

        return view('agenda.index', [
            'dia'           => $dia,
            'compromissos'  => $compromissos,
            'rotinas'       => $rotinas,
            'eventosGoogle' => app(GoogleAgendaService::class)->eventosDoDia(auth()->id(), $dia),
            'icsUrl'        => route('agenda.feed', ['token' => $icsToken]),
        ]);
    }

    public function semana(Request $request)
    {
        try {
            $referencia = $request->filled('data')
                ? Carbon::parse($request->query('data'))->startOfDay()
                : today();
        } catch (\Throwable) {
            $referencia = today();
        }

        $inicio = $referencia->copy()->startOfWeek();
        $fim    = $inicio->copy()->addDays(6);

        $compromissos = Appointment::where('user_id', auth()->id())
            ->whereBetween('data', [$inicio->toDateString(), $fim->toDateString()])
            ->orderByRaw('hora IS NULL DESC')
            ->orderBy('hora')
            ->get()
            ->groupBy(fn($c) => $c->data->toDateString());

        $rotinas = Routine::where('user_id', auth()->id())
            ->with(['checks' => fn($q) => $q->whereBetween('data', [$inicio->toDateString(), $fim->toDateString()])])
            ->get();

        $google = app(GoogleAgendaService::class);
        $eventosGoogleSemana = [];
        for ($i = 0; $i < 7; $i++) {
            $d = $inicio->copy()->addDays($i);
            $eventosGoogleSemana[$d->toDateString()] = $google->eventosDoDia(auth()->id(), $d);
        }

        return view('agenda.semana', [
            'inicio'              => $inicio,
            'fim'                 => $fim,
            'compromissos'        => $compromissos,
            'rotinas'             => $rotinas,
            'eventosGoogleSemana' => $eventosGoogleSemana,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|max:80',
            'data'   => 'required|date',
            'hora'   => 'nullable|date_format:H:i',
        ], [
            'titulo.required' => 'Diga o que é o compromisso.',
            'data.required'   => 'A data é obrigatória.',
            'hora.date_format'=> 'Hora inválida.',
        ]);

        Appointment::create([
            'user_id' => auth()->id(),
            'titulo'  => $request->titulo,
            'data'    => $request->data,
            'hora'    => $request->hora,
        ]);

        return redirect()->route('agenda.index', ['data' => $request->data])
            ->with('sucesso', 'Compromisso salvo!');
    }

    public function concluir(Appointment $appointment)
    {
        abort_unless($appointment->user_id === auth()->id(), 403);
        $appointment->update(['concluido' => !$appointment->concluido]);

        return redirect()->route('agenda.index', ['data' => $appointment->data->toDateString()]);
    }

    public function destroy(Appointment $appointment)
    {
        abort_unless($appointment->user_id === auth()->id(), 403);
        $data = $appointment->data->toDateString();
        $appointment->delete();

        return redirect()->route('agenda.index', ['data' => $data])
            ->with('sucesso', 'Compromisso removido.');
    }

    /** Micro-passo: quebrar o compromisso em pedaços pequenos. */
    public function storePasso(Request $request, Appointment $appointment)
    {
        abort_unless($appointment->user_id === auth()->id(), 403);

        $request->validate(
            ['titulo' => 'required|max:80'],
            ['titulo.required' => 'Escreva o passo.']
        );

        $appointment->steps()->create(['titulo' => $request->titulo]);

        return redirect()->route('agenda.index', ['data' => $appointment->data->toDateString()]);
    }

    public function togglePasso(AppointmentStep $step)
    {
        abort_unless($step->appointment->user_id === auth()->id(), 403);
        $step->update(['concluido' => !$step->concluido]);

        return redirect()->route('agenda.index', ['data' => $step->appointment->data->toDateString()]);
    }

    public function destroyPasso(AppointmentStep $step)
    {
        abort_unless($step->appointment->user_id === auth()->id(), 403);
        $data = $step->appointment->data->toDateString();
        $step->delete();

        return redirect()->route('agenda.index', ['data' => $data]);
    }

    /**
     * Feed iCal público (por token secreto) — o Google Calendar assina esta URL
     * e os compromissos do FinFoco aparecem lá automaticamente.
     */
    public function feed(string $token)
    {
        $userId = Setting::where('chave', 'ics_token')->where('valor', $token)->value('user_id');
        abort_if($userId === null, 404);

        $eventos = Appointment::where('user_id', $userId)
            ->whereDate('data', '>=', today()->subDays(30))
            ->orderBy('data')
            ->get();

        $linhas = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//FinFoco//Agenda//PT-BR',
            'CALSCALE:GREGORIAN',
            'X-WR-CALNAME:FinFoco',
        ];

        foreach ($eventos as $e) {
            $uid    = "finfoco-{$e->id}@finfoco.nexialabs.com.br";
            $titulo = addcslashes($e->titulo, ",;\\");

            $linhas[] = 'BEGIN:VEVENT';
            $linhas[] = "UID:{$uid}";
            $linhas[] = 'DTSTAMP:' . $e->updated_at->utc()->format('Ymd\THis\Z');

            if ($e->hora) {
                $inicio = Carbon::parse($e->data->toDateString() . ' ' . $e->hora);
                $linhas[] = 'DTSTART;TZID=America/Sao_Paulo:' . $inicio->format('Ymd\THis');
                $linhas[] = 'DTEND;TZID=America/Sao_Paulo:' . $inicio->copy()->addHour()->format('Ymd\THis');
            } else {
                $linhas[] = 'DTSTART;VALUE=DATE:' . $e->data->format('Ymd');
            }

            $linhas[] = "SUMMARY:{$titulo}";
            $linhas[] = 'END:VEVENT';
        }

        $linhas[] = 'END:VCALENDAR';

        return response(implode("\r\n", $linhas), 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="finfoco.ics"',
        ]);
    }
}
