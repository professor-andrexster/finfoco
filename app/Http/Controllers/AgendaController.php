<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Setting;
use App\Models\User;
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
            ->get();

        // Token do feed iCal (Google Calendar assina esta URL) — criado na 1ª visita
        $icsToken = Setting::get('ics_token');
        if (!$icsToken) {
            $icsToken = Str::random(40);
            Setting::set('ics_token', $icsToken);
        }

        return view('agenda.index', [
            'dia'          => $dia,
            'compromissos' => $compromissos,
            'icsUrl'       => route('agenda.feed', ['token' => $icsToken]),
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
