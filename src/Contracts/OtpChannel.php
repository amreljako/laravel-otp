<?php
namespace Amreljako\Otp\Contracts;
use Amreljako\Otp\DTO\OtpPayload;

interface OtpChannel {
    /** Send OTP; return true on success. */
    public function send(OtpPayload $payload): bool;
}
