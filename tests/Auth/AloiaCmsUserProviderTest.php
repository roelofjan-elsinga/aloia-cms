<?php

namespace AloiaCms\Tests\Auth;

use AloiaCms\Auth\User;
use AloiaCms\Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class AloiaCmsUserProviderTest extends TestCase
{
    public function test_can_get_user_from_guard()
    {
        $user = User::find('test@example.com')->save();

        Auth::login($user);

        $logged_in_user = Auth::user();

        $this->assertSame($user->getAuthIdentifier(), $logged_in_user->getAuthIdentifier());
    }
}