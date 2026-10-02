<?php

namespace App\Mail;

use App\Models\BrandliftStudy;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BrandliftTagsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BrandliftStudy $study,
        public string $customMessage,
        public string $excelContent,
        public string $filename = 'Brandlift_Tags.xls'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tags BrandLift — {$this->study->campaign_name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.brandlift-tags',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->excelContent, $this->filename)
                ->withMime('application/vnd.ms-excel'),
        ];
    }
}
