<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountApprovalMail extends Mailable
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
        return $this->subject('Your Nutritionist Account Has Been Approved!')
                    ->view('emails.account-approval')
                    ->with([
                        'userName' => $this->user->first_name . ' ' . $this->user->last_name,
                        'userEmail' => $this->user->email,
                        'loginUrl' => url('/login'),
                    ]);
    }
}
