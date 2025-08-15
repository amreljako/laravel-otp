# Laravel OTP by @amreljako

Advanced, secure OTP (Email/SMS/WhatsApp) for Laravel. Hashed codes, TTL, one-time use, rate limiting, drivers, rules, and clean API.

![License](https://img.shields.io/badge/license-MIT-blue.svg)

## Features
- Random OTP with configurable digits and TTL
- Store **only hashed** codes
- **One-time use** via `consumed_at`
- Rate limit sending/verification
- Attempts counter & lockout pattern
- HMAC signature binding (purpose + destination)
- Channels (Mail by default) + extend SMS/WhatsApp
- Migration, Config publish, Facade, Rule
- Framework-agnostic tests via Testbench + Pest

## Install
```bash
composer require amreljako/laravel-otp
php artisan vendor:publish --tag=otp-config
php artisan migrate
```

## Quick Start
```php
use Otp;

Otp::send([
  'destination' => 'user@example.com',
  'purpose' => 'login',
  'channel' => 'mail', // or your sms/whatsapp driver
  // 'ttl' => 300, 'digits' => 6, 'max_attempts' => 5,
]);
```

Verify:
```php
$ok = Otp::verify('user@example.com', 'login', $request->code);
if ($ok) { /* grant access */ } else { /* error */ }
```

### Validation Rule
```php
$request->validate([
  'email' => ['required','email'],
  'code'  => ['required', new \Amreljako\Otp\Rules\ValidOtp('email','login')],
]);
```

### Create your own SMS/WhatsApp channel
```php
class MySmsChannel implements \Amreljako\Otp\Contracts\OtpChannel {
  public function send(\Amreljako\Otp\DTO\OtpPayload $p): bool {
    // call provider API using $p->destination and $p->message()
    return true;
  }
}
```
Then register in `config/otp.php`:
```php
'channels' => [
  'mail' => \Amreljako\Otp\Channels\MailChannel::class,
  'sms'  => \App\Otp\Channels\MySmsChannel::class,
],
```

## Security
- No plaintext codes stored
- Expires with `expires_at`
- One-time consumption
- Throttle abuse with RateLimiter
- Optional HMAC signature

See **SECURITY.md** to report vulnerabilities.

## Testing
```bash
composer install
vendor/bin/pest
```

## License
MIT © 2025 Amr Elsayed
