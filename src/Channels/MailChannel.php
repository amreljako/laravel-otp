<?php
namespace Amreljako\Otp\Channels;

use Illuminate\Support\Facades\Mail;
use Amreljako\Otp\Contracts\OtpChannel;
use Amreljako\Otp\DTO\OtpPayload;

class MailChannel implements OtpChannel {
    public function send(OtpPayload $p): bool {
        // Expect user to provide a Mailable at App\Mail\OtpCodeMail
        Mail::to($p->destination)->queue(new \App\Mail\OtpCodeMail($p));
        return true;
    }
}
