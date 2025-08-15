<?php
namespace Amreljako\Otp\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Carbon\Carbon;
use Amreljako\Otp\DTO\OtpPayload;
use Amreljako\Otp\Contracts\OtpChannel;

class OtpManager {
    public function send(array $data): array {
        $channel = $data['channel'] ?? 'mail';
        $digits  = (int) ($data['digits'] ?? config('otp.default_digits'));
        $ttl     = (int) ($data['ttl'] ?? config('otp.default_ttl'));
        $dest    = $data['destination'];
        $purpose = $data['purpose'];

        $this->throttle('send', $dest);

        $code = str_pad((string) random_int(0, (10 ** $digits) - 1), $digits, '0', STR_PAD_LEFT);
        $signature = $this->signature($purpose, $dest);

        $payload = new OtpPayload($channel, $dest, $purpose, $digits, $ttl, $code, $signature, $data['meta'] ?? []);
        $now = now();

        $id = DB::table('otps')->insertGetId([
            'otpable_type' => $data['otpable_type'] ?? null,
            'otpable_id'   => $data['otpable_id'] ?? null,
            'channel' => $channel,
            'destination' => $dest,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'digits' => $digits,
            'ttl_seconds' => $ttl,
            'expires_at' => $now->copy()->addSeconds($ttl),
            'max_attempts' => $data['max_attempts'] ?? 5,
            'signature' => $signature,
            'meta' => json_encode(array_merge([
                'ip' => request()?->ip(),
                'ua' => request()?->userAgent(),
            ], $data['meta'] ?? [])),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        /** @var OtpChannel $driver */
        $driver = app(config("otp.channels.$channel"));
        $driver->send($payload);

        return ['id' => $id, 'expires_at' => $now->addSeconds($ttl), 'digits' => $digits];
    }

    public function verify(string $destination, string $purpose, string $code): bool {
        $this->throttle('verify', $destination);

        $otp = DB::table('otps')
            ->where('destination', $destination)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->orderByDesc('id')
            ->first();

        if (!$otp) return false;

        if (now()->greaterThan(Carbon::parse($otp->expires_at))) {
            return false;
        }

        if ($otp->attempts >= $otp->max_attempts) {
            return false;
        }

        $ok = password_verify($code, $otp->code_hash);
        DB::table('otps')->where('id', $otp->id)->update([
            'attempts' => DB::raw('attempts + 1'),
            'updated_at' => now(),
        ]);

        if (!$ok) return false;

        DB::table('otps')->where('id', $otp->id)->update([ 'consumed_at' => now() ]);

        if (config('otp.delete_on_verify')) {
            DB::table('otps')->where('id', $otp->id)->delete();
        }

        return true;
    }

    protected function signature(string $purpose, string $dest): ?string {
        if (!config('otp.signature.enabled')) return null;
        $key = config('otp.signature.key');
        $ctx = implode('|', array_map(fn($k)=>$k==='purpose'?$purpose:$dest, config('otp.signature.context')));
        return hash_hmac('sha256', $ctx, $key);
    }

    protected function throttle(string $action, string $dest): void {
        $lim = config("otp.rate_limits.{$action}_per_destination_per_minute");
        if (!$lim) return;
        $key = "otp:{$action}:".sha1($dest);
        if (RateLimiter::tooManyAttempts($key, $lim)) {
            abort(429, "Too many {$action} attempts. Try later.");
        }
        RateLimiter::hit($key, 60);
    }
}
