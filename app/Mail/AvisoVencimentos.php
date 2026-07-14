<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

class AvisoVencimentos extends Mailable
{
    public function __construct(
        public User $user,
        public Collection $atrasadas,
        public Collection $vencemHoje,
        public Collection $vencemAmanha,
    ) {}

    public function envelope(): Envelope
    {
        $partes = [];
        if ($this->atrasadas->isNotEmpty())    $partes[] = $this->atrasadas->count() . ' atrasada(s)';
        if ($this->vencemHoje->isNotEmpty())   $partes[] = $this->vencemHoje->count() . ' vence(m) hoje';
        if ($this->vencemAmanha->isNotEmpty()) $partes[] = $this->vencemAmanha->count() . ' vence(m) amanhã';

        return new Envelope(subject: 'Norte — Contas: ' . implode(', ', $partes));
    }

    public function content(): Content
    {
        // View HTML simples (sem markdown): não depende de ext-dom e dá controle
        // total sobre o visual do e-mail
        return new Content(view: 'emails.aviso-vencimentos');
    }
}
