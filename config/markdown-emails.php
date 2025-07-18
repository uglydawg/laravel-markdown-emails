<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Template Settings
    |--------------------------------------------------------------------------
    */
    'template' => [
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
        'sanitize_content' => false, // Allow button HTML and styled content
        'allowed_html_tags' => [
            'p', 'br', 'strong', 'em', 'u', 'a', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'table', 'thead', 'tbody', 'tr', 'th', 'td', 'del', 'pre', 'code', 'blockquote'
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
    | Button Styling Configuration
    |--------------------------------------------------------------------------
    */
    'buttons' => [
        'primary' => [
            'background_color' => '#3498db',
            'text_color' => 'white',
            'padding' => '12px 24px',
            'border_radius' => '5px',
            'font_weight' => 'bold',
            'margin' => '10px 0',
        ],
        'secondary' => [
            'background_color' => '#95a5a6',
            'text_color' => 'white',
            'padding' => '12px 24px',
            'border_radius' => '5px',
            'font_weight' => 'bold',
            'margin' => '10px 0',
        ],
        'success' => [
            'background_color' => '#27ae60',
            'text_color' => 'white',
            'padding' => '12px 24px',
            'border_radius' => '5px',
            'font_weight' => 'bold',
            'margin' => '10px 0',
        ],
        'danger' => [
            'background_color' => '#e74c3c',
            'text_color' => 'white',
            'padding' => '12px 24px',
            'border_radius' => '5px',
            'font_weight' => 'bold',
            'margin' => '10px 0',
        ],
        'warning' => [
            'background_color' => '#f39c12',
            'text_color' => 'white',
            'padding' => '12px 24px',
            'border_radius' => '5px',
            'font_weight' => 'bold',
            'margin' => '10px 0',
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