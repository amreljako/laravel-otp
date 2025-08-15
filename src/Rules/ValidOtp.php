<?php
namespace Amreljako\Otp\Rules;

use Illuminate\Contracts\Validation\Rule;
use Amreljako\Otp\Facades\Otp;

class ValidOtp implements Rule {
    public function __construct(public string $destinationField, public string $purpose) {}
    public function passes($attribute, $value): bool {
        $destination = request()->input($this->destinationField);
        return Otp::verify($destination, $this->purpose, (string) $value);
    }
    public function message(): string { return 'Invalid or expired OTP.'; }
}
