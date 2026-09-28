<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class AuthenticationTimeoutTest extends TestCase
{
    public function test_login_is_valid_within_one_week(): void
    {
        $user = new User;
        $user->last_login_at = now()->subDays(6);

        $this->assertFalse($user->authenticationHasExpired());
    }

    public function test_login_expires_after_one_week(): void
    {
        $user = new User;
        $user->last_login_at = now()->subDays(7)->subMinute();

        $this->assertTrue($user->authenticationHasExpired());
    }
}
