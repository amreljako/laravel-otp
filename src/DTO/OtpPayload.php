<?php
namespace Amreljako\Otp\DTO;

class OtpPayload {
    public function __construct(
        public string $channel,
        public string $destination,
        public string $purpose,
        public int $digits,
        public int $ttlSeconds,
        public string $code,
        public ?string $signature = null,
        public array $meta = []
    ) {}
    public function message(): string {
        $mins = (int) ceil($this->ttlSeconds / 60);
        return "Your {$this->purpose} code is {$this->code}. Expires in {$mins} min.";
    }
}
