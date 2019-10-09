<?php


namespace FlatFileCms\Tests\Console;

use FlatFileCms\Tests\TestCase;

class ConfigCommandTest extends TestCase
{
    public function test_config_can_be_published_through_command()
    {
        $this
            ->artisan('flatfilecms:publish:config')
            ->assertExitCode(0);
    }
}
