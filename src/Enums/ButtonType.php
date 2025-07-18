<?php

declare(strict_types=1);

namespace uglydawg\LaravelMarkdownEmails\Enums;

enum ButtonType: string
{
    case PRIMARY = 'primary';
    case SECONDARY = 'secondary';
    case SUCCESS = 'success';
    case DANGER = 'danger';
    case WARNING = 'warning';
    case CUSTOM = 'custom';
}