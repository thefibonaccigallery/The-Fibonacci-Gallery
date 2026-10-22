<?php

namespace App\Mail;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CourseCertificateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Certificate $certificate,
        public string $pdfContent,
        public string $verifyUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🎓 Congratulations! Your Certificate for {$this->certificate->course_title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.certificate',
            with: [
                'recipientName'     => $this->certificate->recipient_name,
                'courseTitle'       => $this->certificate->course_title,
                'certificateNumber' => $this->certificate->certificate_number,
                'issuedAt'          => $this->certificate->issued_at,
                'verifyUrl'         => $this->verifyUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, "Fibonacci-Gallery-Certificate-{$this->certificate->certificate_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
