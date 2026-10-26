<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaxIdSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public $taxId) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tax ID Submitted for Verification',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tax-id-submitted',
        );
    }
}
