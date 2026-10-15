<?php

namespace App\Mail;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class BirthdayMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Member $member) {}

    public function envelope(): Envelope
    {
        $first = explode(' ', trim($this->member->name))[0] ?: $this->member->name;

        return new Envelope(subject: 'Joyeux anniversaire '.$first.' 🎂');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.birthday');
    }

    public function attachments(): array
    {
        $path = $this->member->birthday_card_path;

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return [];
        }

        return [
            Attachment::fromPath(Storage::disk('public')->path($path))
                ->as('ma-carte-anniversaire.png')->withMime('image/png'),
        ];
    }
}
