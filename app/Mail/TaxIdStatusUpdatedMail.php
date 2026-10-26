<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaxIdStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public $taxId, public $status) {}

    public function envelope(): Envelope
    {
        $statusText = ucfirst($this->status);
        return new Envelope(
            subject: "Your Tax ID has been {$statusText}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tax-id-status',
        );
    }
}
