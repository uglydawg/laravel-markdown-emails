<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use uglydawg\LaravelMarkdownEmails\MarkdownEmailRenderer;

beforeEach(function () {
    DB::beginTransaction();
});

afterEach(function () {
    DB::rollBack();
});

it('can render markdown to html', function () {
    // Arrange
    $config = config('markdown-emails');
    $renderer = new MarkdownEmailRenderer($config);
    $markdown = '# Hello World\n\nThis is a **test** email.';

    // Act
    $html = $renderer->render($markdown);

    // Assert
    expect($html)
        ->toContain('<h1>Hello World</h1>')
        ->and($html)->toContain('<strong>test</strong>')
        ->and($html)->toContain('<!DOCTYPE html');
});

it('can render markdown with variables', function () {
    // Arrange
    $config = config('markdown-emails');
    $renderer = new MarkdownEmailRenderer($config);
    $markdown = 'Hello {{ name }}, welcome to {{ company }}!';
    $variables = ['name' => 'John', 'company' => 'Acme Corp'];

    // Act
    $html = $renderer->render($markdown, $variables);

    // Assert
    expect($html)
        ->toContain('Hello John, welcome to Acme Corp!')
        ->and($html)->toContain('<!DOCTYPE html');
});

it('can create and optionally store markdown email', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'database' => ['store_emails' => true]
    ]);
    $renderer = new MarkdownEmailRenderer($config);
    $subject = 'Test Email';
    $markdown = '# Hello World';
    $recipients = ['test@example.com'];
    $variables = ['name' => 'John'];

    // Act
    $email = $renderer->create($subject, $markdown, $recipients, $variables);

    // Assert
    expect($email)
        ->toBeInstanceOf(\uglydawg\LaravelMarkdownEmails\MarkdownEmail::class)
        ->and($email->subject)->toBe('Test Email')
        ->and($email->markdown_content)->toBe('# Hello World')
        ->and($email->recipients)->toBe(['test@example.com'])
        ->and($email->variables)->toBe(['name' => 'John'])
        ->and($email->status)->toBe('draft');
});

it('sanitizes subject to prevent header injection', function () {
    // Arrange
    $config = config('markdown-emails');
    $renderer = new MarkdownEmailRenderer($config);
    $maliciousSubject = "Test\r\nBcc: hacker@evil.com\r\nSubject: Hacked";
    $markdown = '# Test';
    $recipients = ['test@example.com'];

    // Act
    $email = $renderer->create($maliciousSubject, $markdown, $recipients);

    // Assert
    expect($email->subject)->toBe('TestBcc: hacker@evil.comSubject: Hacked')
        ->and($email->subject)->not->toContain("\r")
        ->and($email->subject)->not->toContain("\n");
});

it('sanitizes html content when enabled', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'security' => [
            'sanitize_content' => true,
            'allowed_html_tags' => ['p', 'strong']
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);
    $markdown = '# Hello\n\n<script>alert("xss")</script>\n\n**Bold text**';

    // Act
    $html = $renderer->render($markdown);

    // Assert
    expect($html)
        ->not->toContain('<script>')
        ->and($html)->not->toContain('alert("xss")')
        ->and($html)->toContain('<strong>Bold text</strong>');
});

it('includes business name and logo in template', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png',
            'logo_width' => 200,
            'logo_height' => 80
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);
    $markdown = '# Welcome';

    // Act
    $html = $renderer->render($markdown);

    // Assert
    expect($html)
        ->toContain('Test Company')
        ->and($html)->toContain('https://example.com/logo.png')
        ->and($html)->toContain('width="200"')
        ->and($html)->toContain('height="80"');
});

it('includes footer links in template', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'links' => [
            'unsubscribe_url' => 'https://example.com/unsubscribe',
            'privacy_policy_url' => 'https://example.com/privacy',
            'terms_of_service_url' => 'https://example.com/terms'
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);
    $markdown = '# Welcome';

    // Act
    $html = $renderer->render($markdown);

    // Assert
    expect($html)
        ->toContain('https://example.com/unsubscribe')
        ->and($html)->toContain('https://example.com/privacy')
        ->and($html)->toContain('https://example.com/terms');
});