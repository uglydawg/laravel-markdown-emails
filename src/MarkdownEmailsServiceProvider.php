<?php

declare(strict_types=1);

namespace uglydawg\LaravelMarkdownEmails;

use Illuminate\Support\ServiceProvider;

class MarkdownEmailsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/markdown-emails.php' => config_path('markdown-emails.php'),
        ], 'config');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/markdown-emails'),
        ], 'views');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'migrations');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'markdown-emails');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Event listeners can be registered in EventServiceProvider
    }

    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/markdown-emails.php', 'markdown-emails');

        // Register main service
        $this->app->singleton(MarkdownEmailRenderer::class, fn ($app) => 
            new MarkdownEmailRenderer($app['config']['markdown-emails'])
        );

        // Register facade
        $this->app->bind('markdown-emails', fn ($app) => 
            $app->make(MarkdownEmailRenderer::class)
        );
    }
}