# Laravel Markdown Emails - Complete Usage Guide

## 1. Quick Test (Outside Laravel)

Run the example script to see the rendered HTML:

```bash
php example-send-email.php
```

This will create a `rendered-email.html` file you can open in your browser.

## 2. Installation & Setup

### Install the Package

```bash
composer require uglydawg/laravel-markdown-emails
```

### Publish Configuration

```bash
php artisan vendor:publish --provider="uglydawg\LaravelMarkdownEmails\MarkdownEmailsServiceProvider"
```

### Configure Mail Settings

In your `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

## 3. Complete Markdown Feature Examples

### Basic Test Command

```php
<?php
// app/Console/Commands/TestMarkdownEmail.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use uglydawg\LaravelMarkdownEmails\Mail\MarkdownEmailMailable;
use uglydawg\LaravelMarkdownEmails\MarkdownEmailRenderer;

class TestMarkdownEmail extends Command
{
    protected $signature = 'email:test {to}';
    protected $description = 'Send a comprehensive markdown email test';

    public function handle(MarkdownEmailRenderer $renderer)
    {
        $to = $this->argument('to');
        
        // Complete markdown example with all supported features
        $markdown = <<<MD
# Welcome to {{ app_name }}, {{ name }}! 🎉

Thank you for joining our **estate planning platform**. We're excited to help you secure your family's future.

## What's Next?

### 1. Complete Your Profile
- Add your personal information
- Set up emergency contacts
- Upload identification documents

### 2. Upload Important Documents
- *Birth certificates*
- *Marriage certificates* 
- *Property deeds*
- ~~Old outdated documents~~ (strikethrough example)

### 3. Create Your Estate Plan
> "The best time to plant a tree was 20 years ago. The second best time is now."
> 
> *- Chinese Proverb*

This is a powerful reminder that estate planning should start **today**.

## Available Features

| Feature | Basic Plan | Premium Plan | Enterprise |
|---------|------------|--------------|------------|
| Document Storage | 10 docs | Unlimited | Unlimited |
| Video Messages | ❌ | ✅ | ✅ |
| Legal Review | ❌ | ✅ | ✅ |
| 24/7 Support | ❌ | ❌ | ✅ |
| Custom Templates | ❌ | Limited | ✅ |

### Code Examples

For developers, here's how to integrate with our API:

```php
// PHP Integration Example
\$client = new UglydawgClient();
\$response = \$client->createWill([
    'user_id' => {{ user_id }},
    'template' => 'basic'
]);
```

```javascript
// JavaScript Integration
const client = new UglydawgAPI();
const will = await client.createWill({
    userId: {{ user_id }},
    template: 'basic'
});
```

### Important Links

**Need Help?**
- Contact our support team at [support@example.com](mailto:support@example.com)
- Visit our [Help Center](https://help.example.com)
- Call us at [1-800-TRUSTED](tel:1-800-878-7833)

**Quick Actions:**
- [Login to Dashboard](https://{{ domain }}/dashboard)
- [Update Profile](https://{{ domain }}/profile)
- [View Documents](https://{{ domain }}/documents)

**Button Examples:**
{{ login_button }}

{{ get_started_button }}

---

## Security & Privacy

We take your privacy seriously. All documents are:

1. **Encrypted** with military-grade encryption
2. **Backed up** daily to secure servers
3. **Monitored** 24/7 for suspicious activity

### Auto-generated Information

```
Session ID: {{ session_id }}
Generated: {{ timestamp }}
User Agent: {{ user_agent }}
```

---

**Legal Disclaimer:** This email contains confidential information. If you received this in error, please delete it immediately.

*This email was sent from Uglydawg Estate Planning Platform*

**Footer Links:**
- [Privacy Policy](https://{{ domain }}/privacy)
- [Terms of Service](https://{{ domain }}/terms)
- [Unsubscribe](https://{{ domain }}/unsubscribe?token={{ unsubscribe_token }})

---

`Secure • Confidential • Protected`

**Thank you for choosing {{ app_name }}!**
MD;

        $variables = [
            'name' => 'John Doe',
            'app_name' => config('app.name', 'Uglydawg'),
            'domain' => 'example.com',
            'user_id' => 12345,
            'session_id' => 'sess_' . str_random(16),
            'timestamp' => Carbon::now()->format('Y-m-d H:i:s T'),
            'user_agent' => 'Mozilla/5.0 Test Browser',
            'unsubscribe_token' => 'token_' . str_random(32),
            'business_name' => 'Uglydawg Estate Planning',
            'logo_url' => 'https://cdn.example.com/logo.png',
            'logo_width' => 200,
            'logo_height' => 80,
            // Button examples using variable substitution
            'login_button' => '<a href="https://example.com/login" style="display: inline-block; padding: 12px 24px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 10px 0;">Login to Dashboard</a>',
            'get_started_button' => '<a href="https://example.com/get-started" style="display: inline-block; padding: 12px 24px; background-color: #27ae60; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 10px 0;">Get Started</a>',
        ];

        // IMPORTANT: Ensure security settings allow HTML for buttons
        $renderer = app(MarkdownEmailRenderer::class);
        
        // Override security settings to allow button HTML
        $config = config('markdown-emails');
        $config['security']['sanitize_content'] = false; // Allow button HTML
        $renderer = new MarkdownEmailRenderer($config);

        try {
            // Method 1: Using the Mailable class (DEPRECATED - use Method 2)
            // $mailable = MarkdownEmailMailable::fromMarkdown(
            //     'Welcome to Uglydawg - Complete Feature Demo',
            //     $markdown,
            //     [$to],
            //     $variables
            // );
            // Mail::to($to)->send($mailable);
            
            // Method 2: Direct HTML rendering (RECOMMENDED)
            $html = $renderer->render($markdown, $variables);
            Mail::html($html, function ($message) use ($to) {
                $message->to($to)
                        ->subject('Welcome to Uglydawg - Complete Feature Demo');
            });
            
            $this->info("✅ Comprehensive markdown email sent to {$to}!");
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to send email: " . $e->getMessage());
        }
    }
}
```

### Simple Welcome Email Example

```php
<?php
// app/Console/Commands/SendWelcomeEmail.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use uglydawg\LaravelMarkdownEmails\MarkdownEmailRenderer;

class SendWelcomeEmail extends Command
{
    protected $signature = 'email:welcome {to} {name}';
    protected $description = 'Send a simple welcome email';

    public function handle(MarkdownEmailRenderer $renderer)
    {
        $to = $this->argument('to');
        $name = $this->argument('name');
        
        $markdown = <<<MD
# Welcome to {{ app_name }}, {{ name }}!

We're thrilled to have you on board. Here's what you can do next:

## Getting Started

1. **Complete your profile** - Add your basic information
2. **Explore features** - Check out what we have to offer
3. **Join our community** - Connect with other users

## Need Help?

- Visit our [Help Center](https://help.example.com)
- Email us at [support@example.com](mailto:support@example.com)
- Follow us on [Twitter](https://twitter.com/example)

Welcome aboard!

**The {{ app_name }} Team**
MD;

        $variables = [
            'name' => $name,
            'app_name' => config('app.name'),
            'business_name' => 'My Company',
            'logo_url' => 'https://example.com/logo.png',
        ];

        $html = $renderer->render($markdown, $variables);
        
        Mail::html($html, function ($message) use ($to, $name) {
            $message->to($to)
                    ->subject("Welcome to " . config('app.name') . ", {$name}!");
        });
        
        $this->info("✅ Welcome email sent to {$to}!");
    }
}
```

## 4. Supported Markdown Features

### Headers
```markdown
# H1 Header
## H2 Header  
### H3 Header
#### H4 Header
##### H5 Header
###### H6 Header
```

### Text Formatting
```markdown
**Bold text**
*Italic text*
~~Strikethrough text~~
`Inline code`
```

### Lists
```markdown
Unordered list:
- Item 1
- Item 2
  - Nested item
  - Another nested item

Ordered list:
1. First item
2. Second item
   1. Nested numbered item
   2. Another nested item
```

### Links
```markdown
[Link text](https://example.com)
[Email link](mailto:test@example.com)
[Phone link](tel:+1-555-123-4567)
```

### Buttons (Limitations & Workarounds)

⚠️ **Note**: Native button markdown syntax is **not supported**. The package only supports standard markdown links that render as `<a>` tags.

**Workarounds for Button-like Elements:**

#### Option 1: Inline HTML with Styling
```markdown
<a href="https://example.com/login" style="display: inline-block; padding: 12px 24px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Click to Login</a>
```

#### Option 2: Variable Substitution
```php
$variables = [
    'login_button' => '<a href="https://example.com/login" style="display: inline-block; padding: 12px 24px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Click to Login</a>',
    'signup_button' => '<a href="https://example.com/signup" style="display: inline-block; padding: 12px 24px; background-color: #e74c3c; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Sign Up Now</a>'
];
```

```markdown
{{ login_button }}

{{ signup_button }}
```

#### Option 3: Custom CSS Classes (Requires Template Modification)
Add to your email template CSS:
```css
.btn {
    display: inline-block;
    padding: 12px 24px;
    background-color: #3498db;
    color: white !important;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    margin: 10px 0;
}

.btn-primary { background-color: #3498db; }
.btn-success { background-color: #27ae60; }
.btn-danger { background-color: #e74c3c; }
.btn-warning { background-color: #f39c12; }
```

Then use:
```markdown
<a href="https://example.com/login" class="btn btn-primary">Login</a>
<a href="https://example.com/signup" class="btn btn-success">Sign Up</a>
```

### Images
```markdown
![Alt text](https://example.com/image.jpg)
![Logo](https://example.com/logo.png "Company Logo")
```

### Blockquotes
```markdown
> This is a blockquote
> It can span multiple lines
> 
> *- Author Name*
```

### Code Blocks
```markdown
```php
echo "Hello World";
```

```javascript
console.log("Hello World");
```
```

### Tables
```markdown
| Header 1 | Header 2 | Header 3 |
|----------|----------|----------|
| Cell 1   | Cell 2   | Cell 3   |
| Cell 4   | Cell 5   | Cell 6   |
```

### Horizontal Rules
```markdown
---
***
___
```

### Variable Substitution
```markdown
Hello {{ name }}!
Your order #{{ order_id }} is ready.
Total: {{ currency }}{{ total }}
```

## 5. Testing with Mailtrap

1. Sign up for a free [Mailtrap](https://mailtrap.io) account
2. Get your credentials from the inbox settings
3. Update your `.env` file with Mailtrap credentials
4. Send test emails - they'll appear in your Mailtrap inbox

## 6. Unit Testing

```php
<?php
// tests/Feature/EmailTest.php

use Illuminate\Support\Facades\Mail;
use uglydawg\LaravelMarkdownEmails\Mail\MarkdownEmailMailable;

it('sends comprehensive markdown email', function () {
    Mail::fake();
    
    $markdown = <<<MD
# Hello {{ name }}!

This is a **test** email with:
- Lists
- [Links](https://example.com)
- Tables

| Feature | Status |
|---------|--------|
| Email   | ✅     |
| SMS     | ❌     |
MD;
    
    $mailable = MarkdownEmailMailable::fromMarkdown(
        'Test Subject',
        $markdown,
        ['test@example.com'],
        [
            'name' => 'John',
            'business_name' => 'Test Company',
            'logo_url' => 'https://example.com/logo.png'
        ]
    );
    
    Mail::to('test@example.com')->send($mailable);
    
    Mail::assertSent(MarkdownEmailMailable::class, function ($mail) {
        return $mail->hasSubject('Test Subject');
    });
});
```

## 7. Preview Mode (Development)

Create a route to preview emails in the browser:

```php
// routes/web.php (only in development!)
if (app()->environment('local')) {
    Route::get('/email-preview', function () {
        $renderer = app(\uglydawg\LaravelMarkdownEmails\MarkdownEmailRenderer::class);
        
        $markdown = <<<MD
# Email Preview
        
This is a **preview** of your markdown email with:
- Variable substitution: {{ name }}
- Rich formatting support
- [Links](https://example.com)

## Features Demo
| Feature | Status |
|---------|--------|
| Headers | ✅ |
| Lists   | ✅ |
| Tables  | ✅ |
| Links   | ✅ |
| Buttons | ✅ |

## Button Examples
{{ login_button }}

{{ signup_button }}
MD;
        
        $variables = [
            'name' => 'Preview User',
            'business_name' => 'Preview Company',
            'logo_url' => 'https://via.placeholder.com/200x80/007bff/ffffff?text=LOGO',
            'login_button' => '<a href="https://example.com/login" style="display: inline-block; padding: 12px 24px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 10px 0;">Login Now</a>',
            'signup_button' => '<a href="https://example.com/signup" style="display: inline-block; padding: 12px 24px; background-color: #27ae60; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 10px 0;">Sign Up Free</a>'
        ];
        
        $html = $renderer->render($markdown, $variables);
        
        return response($html)->header('Content-Type', 'text/html');
    });
}
```

## 8. Common Issues & Solutions

### Email Not Sending
- **Check mail configuration** in `.env`
- **Verify SMTP credentials** are correct
- **Check firewall settings** for SMTP ports
- **Review Laravel logs** for error messages

### Images Not Displaying
- **Use absolute URLs** for all images
- **Check image accessibility** from external sources
- **Verify HTTPS** for secure image hosting
- **Test in multiple email clients**

### Styles Not Rendering
- **Use inline styles** when possible
- **Test across email clients** (Gmail, Outlook, etc.)
- **Keep CSS simple** - complex styles may be stripped

### Variables Not Substituting
- **Check variable syntax**: `{{ variable_name }}`
- **Ensure variables are in array**: `['key' => 'value']`
- **Verify variable names** match exactly
- **Check for typos** in variable names

### Database Errors
- **Run migrations**: `php artisan migrate`
- **Check database connection** in `.env`
- **Verify table exists**: `markdown_emails`
- **Check permissions** on database

## 9. Advanced Configuration

### Custom Templates
```php
// In config/markdown-emails.php
'template' => [
    'logo_width' => 250,
    'logo_height' => 100,
    'base_view' => 'emails.custom-template',
],
```

### Security Settings
```php
'security' => [
    'sanitize_content' => true,
    'allowed_html_tags' => [
        'p', 'br', 'strong', 'em', 'u', 'a', 'ul', 'ol', 'li', 
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'code', 'pre'
    ],
],
```

### Markdown Extensions
```php
'markdown' => [
    'extensions' => [
        'table',           // Table support
        'strikethrough',   // ~~strikethrough~~ support
        'autolink',        // Auto-convert URLs to links
    ],
],
```

## 10. Performance Tips

1. **Cache rendered emails** for repeated sends
2. **Use queue workers** for bulk email sending
3. **Optimize images** before including in emails
4. **Keep markdown simple** for faster processing
5. **Use database transactions** for batch operations

## 11. Run Commands

```bash
# Send comprehensive test email
php artisan email:test your@email.com

# Send simple welcome email
php artisan email:welcome your@email.com "Your Name"

# Preview email in browser (development)
# Visit: http://your-app.test/email-preview
```