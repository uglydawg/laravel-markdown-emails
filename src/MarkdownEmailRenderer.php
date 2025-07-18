<?php

declare(strict_types=1);

namespace uglydawg\LaravelMarkdownEmails;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\MarkdownConverter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Exception;

class MarkdownEmailRenderer
{
    protected array $config;
    protected MarkdownConverter $converter;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->setupMarkdownConverter();
    }

    protected function setupMarkdownConverter(): void
    {
        $config = [
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
        ];
        
        // Get enabled extensions from config
        $enabledExtensions = $this->config['markdown']['extensions'] ?? [];
        
        // Create environment and add extensions
        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());
        
        // Add table extension
        if (in_array('table', $enabledExtensions)) {
            $environment->addExtension(new TableExtension());
        }
        
        // Add strikethrough extension
        if (in_array('strikethrough', $enabledExtensions)) {
            $environment->addExtension(new StrikethroughExtension());
        }
        
        // Add autolink extension
        if (in_array('autolink', $enabledExtensions)) {
            $environment->addExtension(new AutolinkExtension());
        }
        
        // Use MarkdownConverter with environment
        $this->converter = new MarkdownConverter($environment);
    }

    /**
     * Render markdown content to HTML email
     */
    public function render(string $markdown, array $variables = [], string $template = null): string
    {
        try {
            // Validate that all variables in markdown are provided
            $this->validateVariables($markdown, $variables);
            
            // Replace variables in markdown
            $processedMarkdown = $this->replaceVariables($markdown, $variables);
            
            // Convert markdown to HTML
            $htmlContent = $this->converter->convert($processedMarkdown)->getContent();
            
            // Sanitize HTML if enabled
            if ($this->config['security']['sanitize_content'] ?? true) {
                $htmlContent = $this->sanitizeHtml($htmlContent);
            }
            
            // Use specified template or default
            $templateView = $template ?? ($this->config['template']['base_view'] ?? 'markdown-emails::base-template');
            
            // Render the final email template
            return View::make($templateView, [
                'content' => $htmlContent,
                'businessName' => $variables['business_name'] ?? $this->config['template']['business_name'],
                'logoUrl' => $variables['logo_url'] ?? $this->config['template']['logo_url'],
                'logoWidth' => $variables['logo_width'] ?? $this->config['template']['logo_width'] ?? 200,
                'logoHeight' => $variables['logo_height'] ?? $this->config['template']['logo_height'] ?? 80,
                'links' => $this->config['links'] ?? [],
                'variables' => $variables,
            ])->render();
            
        } catch (Exception $e) {
            throw new Exception("Failed to render markdown email: " . $e->getMessage());
        }
    }

    /**
     * Replace variables in markdown content
     */
    protected function replaceVariables(string $markdown, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $stringValue = is_string($value) ? $value : (string) $value;
            $markdown = str_replace("{{ $key }}", $stringValue, $markdown);
            $markdown = str_replace("{{$key}}", $stringValue, $markdown);
        }
        
        return $markdown;
    }

    /**
     * Validate that all variables in markdown are provided
     */
    protected function validateVariables(string $markdown, array $variables): void
    {
        // Find all variables in the markdown using regex
        preg_match_all('/\{\{\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*\}\}/', $markdown, $matches);
        
        if (empty($matches[1])) {
            return; // No variables found
        }
        
        $foundVariables = array_unique($matches[1]);
        $missingVariables = [];
        
        foreach ($foundVariables as $variable) {
            if (!array_key_exists($variable, $variables)) {
                $missingVariables[] = $variable;
            }
        }
        
        if (!empty($missingVariables)) {
            throw new Exception(
                "Missing required variables in markdown: " . implode(', ', $missingVariables) . 
                ". Found variables: " . implode(', ', $foundVariables) . 
                ". Provided variables: " . implode(', ', array_keys($variables))
            );
        }
    }

    /**
     * Sanitize HTML content
     */
    protected function sanitizeHtml(string $html): string
    {
        $allowedTags = implode('><', $this->config['security']['allowed_html_tags'] ?? ['p', 'br', 'strong', 'em', 'u', 'a', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6']);
        return strip_tags($html, "<$allowedTags>");
    }

    /**
     * Create and optionally store a markdown email
     */
    public function create(string $subject, string $markdown, array $recipients, array $variables = [], string $template = null): MarkdownEmail
    {
        $htmlContent = $this->render($markdown, $variables, $template);
        
        $email = new MarkdownEmail([
            'subject' => $this->sanitizeSubject($subject),
            'markdown_content' => $markdown,
            'html_content' => $htmlContent,
            'recipients' => $recipients,
            'variables' => $variables,
            'template_used' => $template ?? 'base-template',
            'status' => 'draft',
        ]);

        if ($this->config['database']['store_emails'] ?? true) {
            $email->save();
        }

        return $email;
    }

    /**
     * Generate a styled button for email templates
     */
    public function createButton(string $text, string $url, string $type = 'primary'): string
    {
        // Validate and sanitize URL
        $sanitizedUrl = $this->sanitizeUrl($url);
        $buttonStyles = $this->getButtonStyles($type);
        
        return sprintf(
            '<a href="%s" style="%s">%s</a>',
            htmlspecialchars($sanitizedUrl, ENT_QUOTES, 'UTF-8'),
            $buttonStyles,
            htmlspecialchars($text, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Get button styles based on type
     */
    protected function getButtonStyles(string $type): string
    {
        $buttonConfig = $this->config['buttons'][$type] ?? $this->config['buttons']['primary'] ?? [];
        
        $styles = [
            'display: inline-block',
            'text-decoration: none',
            'text-align: center',
            'padding: ' . $this->sanitizeCssValue($buttonConfig['padding'] ?? '12px 24px'),
            'color: ' . $this->sanitizeCssValue($buttonConfig['text_color'] ?? 'white'),
            'background-color: ' . $this->sanitizeCssValue($buttonConfig['background_color'] ?? '#3498db'),
            'border-radius: ' . $this->sanitizeCssValue($buttonConfig['border_radius'] ?? '5px'),
            'font-weight: ' . $this->sanitizeCssValue($buttonConfig['font_weight'] ?? 'bold'),
            'margin: ' . $this->sanitizeCssValue($buttonConfig['margin'] ?? '10px 0'),
        ];
        
        return implode('; ', $styles) . ';';
    }

    /**
     * Sanitize URL to prevent XSS and malicious schemes
     */
    protected function sanitizeUrl(string $url): string
    {
        // Remove any whitespace
        $url = trim($url);
        
        // Return safe placeholder for empty URLs
        if (empty($url)) {
            return '#';
        }
        
        // Check for dangerous protocols
        $dangerousProtocols = ['javascript:', 'data:', 'vbscript:', 'file:', 'about:'];
        
        foreach ($dangerousProtocols as $protocol) {
            if (stripos($url, $protocol) === 0) {
                return '#'; // Replace with safe placeholder
            }
        }
        
        // Allow specific safe protocols and patterns
        $safePatterns = [
            '/^https?:\/\/.+/i',     // HTTP/HTTPS
            '/^mailto:.+@.+/i',      // Email
            '/^tel:\+?[\d\s\-\(\)]+/i', // Phone
            '/^\/[^\/].*/',          // Relative paths starting with /
            '/^#[\w\-]+$/',          // Anchors
        ];
        
        foreach ($safePatterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return $url; // URL is safe
            }
        }
        
        // If no safe pattern matches, return safe placeholder
        return '#';
    }

    /**
     * Sanitize CSS values to prevent CSS injection
     */
    protected function sanitizeCssValue(string $value): string
    {
        // Remove any characters that could break out of CSS context
        $value = str_replace(['"', "'", '\\', ';', '{', '}', '(', ')', '<', '>', 'expression', 'javascript:', 'data:', 'url('], '', $value);
        
        // Remove any potential CSS injection attempts
        $value = preg_replace('/\s*(\/\*.*?\*\/|\/\/.*?$)/m', '', $value);
        
        // Limit length to prevent DoS
        return Str::limit(trim($value), 100);
    }

    /**
     * Sanitize email subject to prevent header injection
     */
    protected function sanitizeSubject(string $subject): string
    {
        // Remove potential header injection characters
        $subject = str_replace(["\r", "\n", "\t"], '', $subject);
        
        // Limit length
        return Str::limit($subject, 255);
    }
}