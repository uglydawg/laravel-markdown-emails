# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel package for rendering and managing Markdown-based email templates. The package provides functionality to store email templates in a database and render them as HTML emails using Laravel's Blade templating engine.

## Development Commands

### Setup
```bash
composer install
```

### Testing
```bash
# Run all tests (Pest - default)
./vendor/bin/pest
# or
composer test

# Run specific test file
./vendor/bin/pest tests/Feature/MarkdownEmailRenderingTest.php

# Run with coverage
./vendor/bin/pest --coverage
# or
composer test-coverage

# Run tests in parallel
./vendor/bin/pest --parallel
# or
composer test-parallel

# Alternative: PHPUnit (if needed)
./vendor/bin/phpunit
```

### Code Quality
```bash
# Laravel Pint for code styling (if configured)
./vendor/bin/pint

# PHPStan for static analysis (if configured)
./vendor/bin/phpstan analyse
```

## Architecture Overview

### Core Components

1. **Service Provider** (`src/MarkdownEmailsServiceProvider.php`)
   - Registers package services, views, and configuration
   - Publishes configuration and migrations
   - Key entry point for Laravel integration

2. **Model** (`src/MarkdownEmail.php`)
   - Eloquent model for markdown_emails table
   - Handles database operations for email templates

3. **Renderer** (`src/MarkdownEmailRenderer.php`)
   - Core rendering logic for converting Markdown to HTML
   - Integrates with Blade templating system
   - Handles variable substitution and template processing
   - **Generates styled buttons with configurable types and comprehensive security protection**

### Database Structure

- Migration: `database/migrations/2024_01_01_000000_create_markdown_emails_table.php`
- Expected to create a table for storing email templates with markdown content

### View Templates

- `resources/views/base-template.blade.php`: Base layout for all emails
- `resources/views/markdown-email.blade.php`: Specific template for rendering markdown emails

### Configuration

- `config/markdown-emails.php`: Package configuration file
- Contains settings for markdown parsing, default templates, and rendering options
- **Includes button styling configuration for primary, secondary, success, danger, and warning button types**

## Package Development Workflow

When developing features for this package:

1. Implement core functionality in the appropriate src/ file
2. Add corresponding tests in tests/Unit/ or tests/Feature/
3. Update views if UI changes are needed
4. Document configuration options in config file
5. Update migration if database schema changes

## Testing Strategy

- **Testing Framework**: Pest (default) with PHPUnit as fallback
- **Test Style**: Pest 3 with `it()` functions following Arrange-Act-Assert pattern
- **Database Isolation**: Database transactions (`beforeEach/afterEach`) instead of database refresh
- **Unit Tests** (`tests/Unit/`): Test individual components in isolation
- **Feature Tests** (`tests/Feature/`): Test complete email rendering workflows
- **Base test case** in `tests/TestCase.php` for common test setup
- **Factories**: Model factories for test data generation

### Testing Guidelines
- Use `it()` functions instead of `test()` methods
- Always use database transactions for test isolation
- Never use RefreshDatabase, DatabaseMigrations, or DatabaseTransactions traits
- Use `expect()` assertions for better readability
- Follow strict typing with `declare(strict_types=1);`

## Common Tasks

### Publishing Package Assets (for package users)
```bash
php artisan vendor:publish --provider="YourNamespace\MarkdownEmails\MarkdownEmailsServiceProvider"
```

### Running Migrations (for package users)
```bash
php artisan migrate
```

### Button Usage Examples

#### Creating Buttons
```php
$renderer = new MarkdownEmailRenderer($config);

// Create different button types
$primaryButton = $renderer->createButton('Get Started', 'https://example.com/get-started', 'primary');
$secondaryButton = $renderer->createButton('Learn More', 'https://example.com/learn-more', 'secondary');
$successButton = $renderer->createButton('Confirm', 'https://example.com/confirm', 'success');
$dangerButton = $renderer->createButton('Delete', 'https://example.com/delete', 'danger');
$warningButton = $renderer->createButton('Warning', 'https://example.com/warning', 'warning');
```

#### Using Buttons in Markdown
```markdown
# Welcome Email

Click the button below to get started:

{{ primary_button }}

Or learn more about our features:

{{ secondary_button }}
```

#### Button Configuration
```php
// In config/markdown-emails.php
'buttons' => [
    'primary' => [
        'background_color' => '#007bff',
        'text_color' => 'white',
        'padding' => '12px 24px',
        'border_radius' => '5px',
        'font_weight' => 'bold',
        'margin' => '10px 0',
    ],
    'custom' => [
        'background_color' => '#ff6b6b',
        'text_color' => '#ffffff',
        'padding' => '16px 32px',
        'border_radius' => '8px',
        'font_weight' => 'normal',
        'margin' => '15px 0',
    ]
]
```

## Security Features

### Button Security
- **URL Validation**: Blocks dangerous protocols (javascript:, data:, vbscript:, file:, about:)
- **CSS Sanitization**: Prevents CSS injection in button styling configuration
- **XSS Protection**: HTML escaping for button text and URLs
- **DoS Protection**: Length limits on CSS values to prevent resource exhaustion
- **Whitelist-based**: Only allows safe URL protocols (https:, http:, mailto:, tel:, relative paths, anchors)

### Safe URL Patterns
```php
// These URLs are allowed:
https://example.com/safe
http://example.com/safe  
mailto:user@example.com
tel:+1234567890
/relative/path
#anchor

// These URLs are blocked and replaced with '#':
javascript:alert('xss')
data:text/html,<script>alert('xss')</script>
vbscript:alert('xss')
file:///etc/passwd
about:blank
```