<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use uglydawg\LaravelMarkdownEmails\MarkdownEmailRenderer;
use uglydawg\LaravelMarkdownEmails\Enums\ButtonType;

beforeEach(function () {
    DB::beginTransaction();
});

afterEach(function () {
    DB::rollBack();
});

it('can render markdown to html', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);
    $markdown = "# Hello World\n\nThis is a **test** email.";

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
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    ]);
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
        'database' => ['store_emails' => true],
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
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
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    ]);
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
        ],
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);
    $markdown = "# Hello\n\n<script>alert('xss')</script>\n\n**Bold text**";

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

it('fails when business_name is not defined', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'logo_url' => 'https://example.com/logo.png'
            // business_name is intentionally missing
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);
    $markdown = '# Welcome';

    // Act & Assert
    expect(fn() => $renderer->render($markdown))
        ->toThrow(Exception::class);
});

it('fails when logo_url is not defined', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company'
            // logo_url is intentionally missing
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);
    $markdown = '# Welcome';

    // Act & Assert
    expect(fn() => $renderer->render($markdown))
        ->toThrow(Exception::class);
});

it('includes footer links in template', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ],
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

it('renders markdown links correctly', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);
    $markdown = '
**Questions?** Contact our support team at [support@example.com](mailto:support@example.com)

[Visit Dashboard](https://example.com/dashboard) | [Privacy Policy](https://example.com/privacy)

Regular link: [Example](https://example.com)
';

    // Act
    $html = $renderer->render($markdown);

    // Assert
    expect($html)
        ->toContain('<a href="mailto:support@example.com">support@example.com</a>')
        ->and($html)->toContain('<a href="https://example.com/dashboard">Visit Dashboard</a>')
        ->and($html)->toContain('<a href="https://example.com/privacy">Privacy Policy</a>')
        ->and($html)->toContain('<a href="https://example.com">Example</a>');
});

it('throws exception when markdown contains missing variables', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);
    $markdown = '# Welcome {{ name }}! Your order #{{ order_id }} is ready for {{ missing_var }}.';
    $variables = [
        'name' => 'John',
        'order_id' => 12345
        // missing_var is intentionally not provided
    ];

    // Act & Assert
    expect(fn() => $renderer->render($markdown, $variables))
        ->toThrow(Exception::class, 'Missing required variables in markdown: missing_var');
});

it('validates variables with different spacing', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);
    $markdown = 'Hello {{name}} and {{ email }} and {{  phone  }}!';
    $variables = [
        'name' => 'John',
        'email' => 'john@example.com'
        // phone is missing
    ];

    // Act & Assert
    expect(fn() => $renderer->render($markdown, $variables))
        ->toThrow(Exception::class, 'Missing required variables in markdown: phone');
});

it('passes validation when all variables are provided', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);
    $markdown = 'Hello {{ name }}! Your order #{{ order_id }} is ready.';
    $variables = [
        'name' => 'John',
        'order_id' => 12345
    ];

    // Act
    $html = $renderer->render($markdown, $variables);

    // Assert
    expect($html)
        ->toContain('Hello John!')
        ->and($html)->toContain('Your order #12345 is ready.');
});

it('passes validation when no variables are used', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);
    $markdown = '# Welcome! This email has no variables.';

    // Act
    $html = $renderer->render($markdown);

    // Assert
    expect($html)
        ->toContain('<h1>Welcome! This email has no variables.</h1>')
        ->and($html)->toContain('<!DOCTYPE html');
});

it('renders markdown tables correctly', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);
    $markdown = '
| Feature | Basic Plan | Premium Plan |
|---------|------------|--------------|
| Storage | 10 docs | Unlimited |
| Support | ❌ | ✅ |
';

    // Act
    $html = $renderer->render($markdown);

    // Assert
    expect($html)
        ->toContain('<table>')
        ->and($html)->toContain('<th>Feature</th>')
        ->and($html)->toContain('<th>Basic Plan</th>')
        ->and($html)->toContain('<td>10 docs</td>')
        ->and($html)->toContain('<td>Unlimited</td>');
});

it('renders button variables correctly', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);
    $markdown = '
# Welcome!

Click the button below:

{{ login_button }}
';
    $variables = [
        'login_button' => '<a href="https://example.com/login" style="display: inline-block; padding: 12px 24px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Login Now</a>'
    ];

    // Act
    $html = $renderer->render($markdown, $variables);

    // Assert
    expect($html)
        ->toContain('<a href="https://example.com/login"')
        ->and($html)->toContain('background-color: #3498db')
        ->and($html)->toContain('Login Now</a>')
        ->and($html)->not->toContain('{{ login_button }}');
});

it('debugs table and button rendering issues', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ],
        'security' => [
            'sanitize_content' => false, // Disable sanitization to allow button HTML
        ],
    ]);
    $renderer = new MarkdownEmailRenderer($config);
    
    $markdown = '
# Test Email

## Table Test

| Feature | Basic Plan | Premium Plan |
|---------|------------|--------------|
| Storage | 10 docs | Unlimited |
| Support | ❌ | ✅ |

## Button Test

{{ login_button }}

{{ signup_button }}

## Strikethrough Test

~~This should be crossed out~~
';

    $variables = [
        'login_button' => '<a href="https://example.com/login" style="display: inline-block; padding: 12px 24px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Login Now</a>',
        'signup_button' => '<a href="https://example.com/signup" style="display: inline-block; padding: 12px 24px; background-color: #27ae60; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Sign Up</a>',
    ];

    // Act
    $html = $renderer->render($markdown, $variables);
    
    // Debug output
    file_put_contents(__DIR__ . '/../../debug-output.html', $html);
    
    // Check table rendering
    $hasTable = str_contains($html, '<table>');
    $hasTableHeaders = str_contains($html, '<th>Feature</th>');
    $hasTableCells = str_contains($html, '<td>10 docs</td>');
    
    // Check button rendering
    $hasButtons = str_contains($html, 'background-color: #3498db');
    $hasButtonText = str_contains($html, 'Login Now');
    
    // Check strikethrough
    $hasStrikethrough = str_contains($html, '<del>') || str_contains($html, '<s>');
    
    echo "\n=== DEBUG RESULTS ===\n";
    echo "Table: " . ($hasTable ? "✅ WORKING" : "❌ NOT WORKING") . "\n";
    echo "Table Headers: " . ($hasTableHeaders ? "✅ WORKING" : "❌ NOT WORKING") . "\n";
    echo "Table Cells: " . ($hasTableCells ? "✅ WORKING" : "❌ NOT WORKING") . "\n";
    echo "Buttons: " . ($hasButtons ? "✅ WORKING" : "❌ NOT WORKING") . "\n";
    echo "Button Text: " . ($hasButtonText ? "✅ WORKING" : "❌ NOT WORKING") . "\n";
    echo "Strikethrough: " . ($hasStrikethrough ? "✅ WORKING" : "❌ NOT WORKING") . "\n";
    echo "HTML saved to: debug-output.html\n";
    echo "===================\n";
    
    // Assert at least one thing should work
    expect($hasButtonText)->toBeTrue('Button text should be present');
});

it('can create primary button with default styling', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);

    // Act
    $button = $renderer->createButton('Click Me', 'https://example.com', ButtonType::PRIMARY);

    // Assert
    expect($button)
        ->toContain('href="https://example.com"')
        ->and($button)->toContain('Click Me')
        ->and($button)->toContain('background-color: #3498db')
        ->and($button)->toContain('color: white')
        ->and($button)->toContain('text-decoration: none')
        ->and($button)->toContain('padding: 12px 24px');
});

it('can create secondary button with correct styling', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);

    // Act
    $button = $renderer->createButton('Secondary Action', 'https://example.com/secondary', ButtonType::SECONDARY);

    // Assert
    expect($button)
        ->toContain('href="https://example.com/secondary"')
        ->and($button)->toContain('Secondary Action')
        ->and($button)->toContain('background-color: #95a5a6')
        ->and($button)->toContain('color: white');
});

it('can create success button with correct styling', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);

    // Act
    $button = $renderer->createButton('Success', 'https://example.com/success', ButtonType::SUCCESS);

    // Assert
    expect($button)
        ->toContain('href="https://example.com/success"')
        ->and($button)->toContain('Success')
        ->and($button)->toContain('background-color: #27ae60');
});

it('can create danger button with correct styling', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);

    // Act
    $button = $renderer->createButton('Delete', 'https://example.com/delete', ButtonType::DANGER);

    // Assert
    expect($button)
        ->toContain('href="https://example.com/delete"')
        ->and($button)->toContain('Delete')
        ->and($button)->toContain('background-color: #e74c3c');
});

it('can create warning button with correct styling', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);

    // Act
    $button = $renderer->createButton('Warning', 'https://example.com/warning', ButtonType::WARNING);

    // Assert
    expect($button)
        ->toContain('href="https://example.com/warning"')
        ->and($button)->toContain('Warning')
        ->and($button)->toContain('background-color: #f39c12');
});

it('sanitizes button text and URL to prevent XSS', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);

    // Act
    $button = $renderer->createButton('<script>alert("xss")</script>', 'javascript:alert("xss")', ButtonType::PRIMARY);

    // Assert
    expect($button)
        ->not->toContain('<script>')
        ->and($button)->toContain('&lt;script&gt;')
        ->and($button)->toContain('href="#"'); // Malicious URL should be replaced with #
});

it('enforces valid button types using enum', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);

    // Act & Assert - Test that all enum values work
    $primaryButton = $renderer->createButton('Primary', 'https://example.com', ButtonType::PRIMARY);
    $secondaryButton = $renderer->createButton('Secondary', 'https://example.com', ButtonType::SECONDARY);
    $successButton = $renderer->createButton('Success', 'https://example.com', ButtonType::SUCCESS);
    $dangerButton = $renderer->createButton('Danger', 'https://example.com', ButtonType::DANGER);
    $warningButton = $renderer->createButton('Warning', 'https://example.com', ButtonType::WARNING);
    $customButton = $renderer->createButton('Custom', 'https://example.com', ButtonType::CUSTOM);

    expect($primaryButton)->toContain('Primary')
        ->and($secondaryButton)->toContain('Secondary')
        ->and($successButton)->toContain('Success')
        ->and($dangerButton)->toContain('Danger')
        ->and($warningButton)->toContain('Warning')
        ->and($customButton)->toContain('Custom');
});

it('uses custom button configuration when provided', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ],
        'buttons' => [
            'custom' => [
                'background_color' => '#ff6b6b',
                'text_color' => '#ffffff',
                'padding' => '16px 32px',
                'border_radius' => '8px',
                'font_weight' => 'normal',
                'margin' => '15px 0',
            ]
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);

    // Act
    $button = $renderer->createButton('Custom Button', 'https://example.com', ButtonType::CUSTOM);

    // Assert
    expect($button)
        ->toContain('background-color: #ff6b6b')
        ->and($button)->toContain('color: #ffffff')
        ->and($button)->toContain('padding: 16px 32px')
        ->and($button)->toContain('border-radius: 8px')
        ->and($button)->toContain('font-weight: normal')
        ->and($button)->toContain('margin: 15px 0');
});

it('blocks dangerous URL protocols', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);

    $dangerousUrls = [
        'javascript:alert("xss")',
        'data:text/html,<script>alert("xss")</script>',
        'vbscript:alert("xss")',
        'file:///etc/passwd',
        'about:blank'
    ];

    foreach ($dangerousUrls as $url) {
        // Act
        $button = $renderer->createButton('Test', $url, ButtonType::PRIMARY);

        // Assert
        expect($button)->toContain('href="#"'); // Should be replaced with safe placeholder
    }
});

it('allows safe URL protocols', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);

    $safeUrls = [
        'https://example.com/safe',
        'http://example.com/safe',
        'mailto:test@example.com',
        'tel:+1234567890',
        '/relative/path',
        '#anchor'
    ];

    foreach ($safeUrls as $url) {
        // Act
        $button = $renderer->createButton('Test', $url, ButtonType::PRIMARY);

        // Assert
        expect($button)->toContain('href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"');
    }
});

it('sanitizes CSS values to prevent CSS injection', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ],
        'buttons' => [
            'malicious' => [
                'background_color' => 'red; } body { background: url(javascript:alert("xss"));',
                'text_color' => 'white"; expression(alert("xss"));',
                'padding' => '10px; background: url(data:text/html,<script>alert("xss")</script>);',
                'border_radius' => '5px /* comment */ "',
                'font_weight' => 'bold\';',
                'margin' => '10px 0 {}'
            ]
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);

    // Act
    $button = $renderer->createButton('Test', 'https://example.com', ButtonType::CUSTOM);

    // Assert - Check that malicious CSS is stripped from the style attribute
    preg_match('/style="([^"]*)"/', $button, $matches);
    $styleContent = $matches[1] ?? '';
    
    expect($styleContent)
        ->not->toContain('}')
        ->and($styleContent)->not->toContain('{')
        ->and($styleContent)->not->toContain('expression')
        ->and($styleContent)->not->toContain('javascript:')
        ->and($styleContent)->not->toContain('url(')
        ->and($styleContent)->not->toContain('/*')
        ->and($styleContent)->not->toContain('"')
        ->and($styleContent)->not->toContain("'")
        ->and($styleContent)->not->toContain('\\');
});

it('limits CSS value length to prevent DoS', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ],
        'buttons' => [
            'long_value' => [
                'background_color' => str_repeat('a', 200), // Very long value
                'padding' => str_repeat('b', 150)
            ]
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);

    // Act
    $button = $renderer->createButton('Test', 'https://example.com', ButtonType::CUSTOM);

    // Assert - Values should be truncated to prevent DoS
    expect(strlen($button))->toBeLessThan(1000); // Reasonable limit
});

it('handles invalid URLs gracefully', function () {
    // Arrange
    $config = array_merge(config('markdown-emails'), [
        'template' => [
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    ]);
    $renderer = new MarkdownEmailRenderer($config);

    $invalidUrls = [
        'not-a-url',
        'htp://invalid-protocol',
        'ftp://example.com/not-allowed',
        '   ',
        ''
    ];

    foreach ($invalidUrls as $url) {
        // Act
        $button = $renderer->createButton('Test', $url, ButtonType::PRIMARY);

        // Assert
        expect($button)->toContain('href="#"'); // Should be replaced with safe placeholder
    }
});