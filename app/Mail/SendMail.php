<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($reports)
    {
        //
        $this->reports = $reports;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // 送信元、送信する内容の設定
        return $this->text('emails.test_text')
                    ->from('pillopillo7123@gmail.com', 'from name')
                    ->subject('this is a test mail')
                    ->with(['reports' => $this->reports]);
    }
}
