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
        public string     $teamLeaderName,
        public string     $teamLeaderEmail,
        public string     $department,
        public string     $weekLabel,       // e.g. "17 Mar – 23 Mar 2026"
        public string     $weekStart,       // e.g. "2026-03-17"
        public string     $weekEnd,         // e.g. "2026-03-23"
        public Collection $userStats,       // per-user: name, email, hours, top_app, days_active
        public int        $totalTeamHours,
        public string     $hrEmail,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address', 'system@archengpro.com'),
                config('mail.from.name',    'ArchEng Pro Monitor')
            ),
            to: [$this->hrEmail],
            cc: [$this->teamLeaderEmail],
            subject: "Weekly Performance Report — {$this->department} | {$this->weekLabel}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly_report',
        );
    }
}
