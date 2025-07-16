<?php

declare(strict_types=1);

namespace uglydawg\LaravelMarkdownEmails;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Exception;

class MarkdownEmailRenderer
{
    protected array $config;
    protected CommonMarkConverter $converter;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->setupMarkdownConverter();
    }

    protected function setupMarkdownConverter(): void
    {
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $environment->addExtension(new CommonMarkCoreExtension());
        
        // Add extensions based on config
        if (in_array('table', $this->config['markdown']['extensions'])) {
            $environment->addExtension(new TableExtension());
        }
        
        if (in_array('strikethrough', $this->config['markdown']['extensions'])) {
            $environment->addExtension(new StrikethroughExtension());
        }
        
        if (in_array('autolink', $this->config['markdown']['extensions'])) {
            $environment->addExtension(new AutolinkExtension());
        }

        $this->converter = new CommonMarkConverter([], $environment);
    }

    /**
     * Render markdown content to HTML email
     */
    public function render(string $markdown, array $variables = [], string $template = null): string
    {
        try {
            // Replace variables in markdown
            $processedMarkdown = $this->replaceVariables($markdown, $variables);
            
            // Convert markdown to HTML
            $htmlContent = $this->converter->convert($processedMarkdown)->getContent();
            
            // Sanitize HTML if enabled
            if ($this->config['security']['sanitize_content']) {
                $htmlContent = $this->sanitizeHtml($htmlContent);
            }
            
            // Use specified template or default
            $templateView = $template ?? $this->config['template']['base_view'];
            
            // Render the final email template
            return View::make($templateView, [
                'content' => $htmlContent,
                'businessName' => $this->config['template']['business_name'],
                'logoUrl' => $this->config['template']['logo_url'],
                'logoWidth' => $this->config['template']['logo_width'],
                'logoHeight' => $this->config['template']['logo_height'],
                'links' => $this->config['links'],
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
            $markdown = str_replace("{{ $key }}", $value, $markdown);
            $markdown = str_replace("{{$key}}", $value, $markdown);
        }
        
        return $markdown;
    }

    /**
     * Sanitize HTML content
     */
    protected function sanitizeHtml(string $html): string
    {
        $allowedTags = implode('><', $this->config['security']['allowed_html_tags']);
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

        if ($this->config['database']['store_emails']) {
            $email->save();
        }

        return $email;
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