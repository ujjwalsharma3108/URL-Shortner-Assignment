<?php

namespace App\Mail;

use App\Models\AdminInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AdminInvitation $invitation,
        public string $invitationUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You have been invited as an administrator',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.admin-invitation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
