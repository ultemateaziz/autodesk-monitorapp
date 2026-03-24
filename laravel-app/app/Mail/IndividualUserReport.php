<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IndividualUserReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string  $userName,
        public string  $userEmail,
        public string  $department,
        public string  $occupation,
        public string  $teamLeaderName,
        public string  $teamLeaderEmail,
        public string  $weekLabel,
        public string  $weekStart,
        public string  $weekEnd,
        public int     $totalHours,
        public int     $totalMinutes,
        public int     $daysActive,
        public array   $appBreakdown,   // [ 'AutoCAD' => 12, 'Revit' => 5, ... ]
        public string  $topApp,
        public string  $hrEmail,
        public string  $hrName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->hrEmail, $this->hrName),
            to:   [$this->userEmail],
            cc:   [$this->teamLeaderEmail],
            subject: "Your Weekly Performance Summary — {$this->weekLabel}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.individual_report');
    }
}
