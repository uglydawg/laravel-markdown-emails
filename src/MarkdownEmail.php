<?php

declare(strict_types=1);

namespace uglydawg\LaravelMarkdownEmails;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use uglydawg\LaravelMarkdownEmails\Events\MarkdownEmailSent;
use uglydawg\LaravelMarkdownEmails\Events\MarkdownEmailFailed;

class MarkdownEmail extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'subject',
        'markdown_content',
        'html_content',
        'recipients',
        'variables',
        'template_used',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'recipients' => 'array',
        'variables' => 'array',
        'sent_at' => 'datetime',
    ];

    protected static function newFactory()
    {
        return \uglydawg\LaravelMarkdownEmails\Database\Factories\MarkdownEmailFactory::new();
    }

    public function getConnectionName()
    {
        return config('markdown-emails.database.connection', parent::getConnectionName());
    }

    public function getTable()
    {
        return config('markdown-emails.database.table_name', 'markdown_emails');
    }

    /**
     * Mark email as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        event(new MarkdownEmailSent($this));
    }

    /**
     * Mark email as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);

        event(new MarkdownEmailFailed($this));
        
        if (config('markdown-emails.logging.enabled')) {
            Log::channel(config('markdown-emails.logging.channel'))
                ->error('Markdown email failed', [
                    'email_id' => $this->id,
                    'error' => $errorMessage,
                    'subject' => $this->subject,
                    'recipients' => $this->recipients,
                ]);
        }
    }

    /**
     * Scope for sent emails
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope for failed emails
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}