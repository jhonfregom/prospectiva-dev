<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class NewUserRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $newUser;
    public $activationToken;
    public $activationUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $newUser, string $activationToken = null, string $activationUrl = null)
    {
        $this->newUser = $newUser;
        // Usar los valores proporcionados por el listener para mantener consistencia
        $this->activationToken = $activationToken ?? Str::random(64);
        $this->activationUrl = $activationUrl ?? route('user.activation.show', [
            'userId' => $newUser->id,
            'token' => $this->activationToken,
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo Usuario Registrado - Prospectiva',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new-user-registration',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
