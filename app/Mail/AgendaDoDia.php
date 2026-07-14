<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

class AgendaDoDia extends Mailable
{
    public function __construct(
        public User $user,
        public Collection $compromissos,
        public Collection $rotinas,
    ) {}

    public function envelope(): Envelope
    {
        $partes = [];
        if ($this->compromissos->isNotEmpty()) {
            $partes[] = $this->compromissos->count() . ' compromisso' . ($this->compromissos->count() > 1 ? 's' : '');
        }
        if ($this->rotinas->isNotEmpty()) {
            $partes[] = $this->rotinas->count() . ' rotina' . ($this->rotinas->count() > 1 ? 's' : '');
        }

        return new Envelope(subject: 'Seu dia hoje — ' . implode(' e ', $partes));
    }

    public function content(): Content
    {
        // View HTML simples (sem markdown): mesmo padrão do aviso-vencimentos
        return new Content(view: 'emails.agenda-do-dia');
    }
}
