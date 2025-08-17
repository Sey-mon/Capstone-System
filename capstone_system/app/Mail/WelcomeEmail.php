<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Welcome to BMI Malnutrition Monitoring System')
                    ->view('emails.welcome')
                    ->with([
                        'userName' => $this->user->first_name . ' ' . $this->user->last_name,
                        'userEmail' => $this->user->email,
                    ]);
    }
}
