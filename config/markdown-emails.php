<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Template Settings
    |--------------------------------------------------------------------------
    */
    'template' => [
        'business_name' => env('MARKDOWN_EMAILS_BUSINESS_NAME', 'Your Business Name'),
        'logo_url' => env('MARKDOWN_EMAILS_LOGO_URL', '/images/logo.png'),
        'logo_width' => env('MARKDOWN_EMAILS_LOGO_WIDTH', 200),
        'logo_height' => env('MARKDOWN_EMAILS_LOGO_HEIGHT', 80),
        'base_view' => 'markdown-emails::base-template',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Settings
    |--------------------------------------------------------------------------
    */
    'database' => [
        'connection' => env('MARKDOWN_EMAILS_DB_CONNECTION', 'pgsql'),
        'table_name' => 'markdown_emails',
        'store_emails' => env('MARKDOWN_EMAILS_STORE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    'security' => [
        'sanitize_content' => true,
        'allowed_html_tags' => [
            'p', 'br', 'strong', 'em', 'u', 'a', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown Parser Settings
    |--------------------------------------------------------------------------
    */
    'markdown' => [
        'parser' => 'commonmark', // Options: commonmark
        'extensions' => [
            'table',
            'strikethrough',
            'autolink',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Links Configuration
    |--------------------------------------------------------------------------
    */
    'links' => [
        'unsubscribe_url' => env('MARKDOWN_EMAILS_UNSUBSCRIBE_URL'),
        'privacy_policy_url' => env('MARKDOWN_EMAILS_PRIVACY_URL'),
        'terms_of_service_url' => env('MARKDOWN_EMAILS_TERMS_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Settings
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => env('MARKDOWN_EMAILS_LOGGING', true),
        'channel' => env('MARKDOWN_EMAILS_LOG_CHANNEL', 'default'),
        'log_content' => env('MARKDOWN_EMAILS_LOG_CONTENT', false),
    ],
];