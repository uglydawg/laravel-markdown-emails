<?php

declare(strict_types=1);

namespace uglydawg\LaravelMarkdownEmails\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use uglydawg\LaravelMarkdownEmails\MarkdownEmailsServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            MarkdownEmailsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}