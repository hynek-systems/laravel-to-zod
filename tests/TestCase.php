<?php

namespace Hynek\LaravelToZod\Tests;

use Hynek\LaravelToZod\LaravelToZodServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelToZodServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        // Configure any environment variables if needed
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Any additional setup needed for tests
    }
}
