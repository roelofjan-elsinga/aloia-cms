<?php

test('config can be published through command', function () {
    $this
        ->artisan('aloiacms:publish:config')
        ->assertExitCode(0);
});
