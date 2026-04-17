<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class WeeklyTeamReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string      $teamLeaderName,
        public ?string     $teamLeaderEmail,  // null = do not CC the team leader
        public string      $department,
        public string      $weekLabel,
        public string      $weekStart,
        public string      $weekEnd,
        public Collection  $userStats,
        public int         $totalTeamHours,
        public string      $hrEmail,
    ) {}

    public function envelope(): Envelope
    {
        $envelope = new Envelope(
            from: new Address(
                config('mail.from.address', 'system@asclam.com'),
                config('mail.from.name',    'ASCLAM Monitor')
            ),
            to:      [$this->hrEmail],
            subject: "Weekly Performance Report — {$this->department} | {$this->weekLabel}",
        );

        // Only CC the team leader if the admin has enabled the toggle
        if ($this->teamLeaderEmail) {
            $envelope->cc([$this->teamLeaderEmail]);
        }

        return $envelope;
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly_report',
        );
    }
}
