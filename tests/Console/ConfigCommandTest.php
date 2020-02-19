<?php


namespace AloiaCms\Tests\Console;

use AloiaCms\Tests\TestCase;

class ConfigCommandTest extends TestCase
{
    public function test_config_can_be_published_through_command()
    {
        $this
            ->artisan('aloiacms:publish:config')
            ->assertExitCode(0);
    }
}
