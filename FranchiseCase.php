<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class StaffWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $tempPassword;

    public function __construct(User $user, string $tempPassword)
    {
        $this->user = $user;
        $this->tempPassword = $tempPassword;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Eurotaxi Fleet Management System - Your Account Credentials',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.staff_welcome',
        );
    }
}
