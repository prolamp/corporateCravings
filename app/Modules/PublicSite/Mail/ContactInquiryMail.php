<?php

namespace App\Modules\PublicSite\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactInquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{
     *     name: string,
     *     email: string,
     *     phone?: string|null,
     *     subject: string,
     *     message: string,
     * }  $inquiry
     */
    public function __construct(
        public readonly array $inquiry,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [
                new Address($this->inquiry['email'], $this->inquiry['name']),
            ],
            subject: 'Website inquiry: '.$this->inquiry['subject'],
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.contact-inquiry',
            with: [
                'inquiry' => $this->inquiry,
            ],
        );
    }
}
