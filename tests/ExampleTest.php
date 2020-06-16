<?php

namespace Tpaksu\XdebugToggle\Tests;

use Orchestra\Testbench\TestCase;
use Tpaksu\XdebugToggle\XdebugToggleServiceProvider;

class ExampleTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [XdebugToggleServiceProvider::class];
    }

    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
