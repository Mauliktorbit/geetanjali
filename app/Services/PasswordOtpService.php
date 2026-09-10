<?php

namespace App\Services;

use App\Mail\PasswordOtpMail;
use App\Models\PasswordOtp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordOtpService
{
    public const TTL_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    public const RESEND_SECONDS = 60;

    public function tooSoon(string $email): bool
    {
        $latest = PasswordOtp::query()->where('email', $email)->latest('id')->first();

        return $latest !== null && $latest->created_at->gt(now()->subSeconds(self::RESEND_SECONDS));
    }

    public function send(User $user): void
    {
        $otp = (string) random_int(100000, 999999);

        PasswordOtp::query()->where('email', $user->email)->delete();

        $record = PasswordOtp::query()->create([
            'email' => $user->email,
            'otp_hash' => Hash::make($otp),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
        ]);

        try {
            Mail::to($user->email)->send(new PasswordOtpMail($otp, $user->name ?: 'there'));
        } catch (\Throwable $e) {
            $record->delete();
            throw $e;
        }
    }

    public function verify(string $email, string $otp): string
    {
        $record = PasswordOtp::query()
            ->where('email', $email)
            ->latest('id')
            ->first();

        if (! $record) {
            return 'expired';
        }

        if ($record->verified_at) {
            return 'ok';
        }

        if ($record->expires_at->isPast()) {
            return 'expired';
        }

        if ($record->attempts >= self::MAX_ATTEMPTS) {
            return 'locked';
        }

        $record->increment('attempts');

        if (! Hash::check($otp, $record->otp_hash)) {
            return 'invalid';
        }

        $record->update([
            'verified_at' => now(),
            'expires_at' => now()->addMinutes(15),
        ]);

        return 'ok';
    }

    public function isVerified(string $email): bool
    {
        return PasswordOtp::query()
            ->where('email', $email)
            ->whereNotNull('verified_at')
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function clear(string $email): void
    {
        PasswordOtp::query()->where('email', $email)->delete();
    }
}
