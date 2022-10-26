<?php

use AloiaCms\Auth\User;
use Illuminate\Support\Facades\Auth;

it('can get user from guard', function () {
    $user = User::find('test@example.com')->save();

    Auth::login($user);

    $logged_in_user = Auth::user();

    $this->assertSame($user->getAuthIdentifier(), $logged_in_user->getAuthIdentifier());
});
