<?php

declare(strict_types=1);

namespace uglydawg\LaravelMarkdownEmails\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use uglydawg\LaravelMarkdownEmails\MarkdownEmail;

class MarkdownEmailFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public MarkdownEmail $email
    ) {}
}