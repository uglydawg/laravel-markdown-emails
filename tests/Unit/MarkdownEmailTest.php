<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use uglydawg\LaravelMarkdownEmails\MarkdownEmail;

beforeEach(function () {
    DB::beginTransaction();
});

afterEach(function () {
    DB::rollBack();
});

it('can create a markdown email', function () {
    // Arrange
    $data = [
        'subject' => 'Test Email',
        'markdown_content' => '# Hello World',
        'html_content' => '<h1>Hello World</h1>',
        'recipients' => ['test@example.com'],
        'variables' => ['name' => 'John'],
        'template_used' => 'base-template',
        'status' => 'draft',
    ];

    // Act
    $email = MarkdownEmail::create($data);

    // Assert
    expect($email)
        ->toBeInstanceOf(MarkdownEmail::class)
        ->and($email->subject)->toBe('Test Email')
        ->and($email->markdown_content)->toBe('# Hello World')
        ->and($email->html_content)->toBe('<h1>Hello World</h1>')
        ->and($email->recipients)->toBe(['test@example.com'])
        ->and($email->variables)->toBe(['name' => 'John'])
        ->and($email->template_used)->toBe('base-template')
        ->and($email->status)->toBe('draft');
});

it('can mark email as sent', function () {
    // Arrange
    $email = MarkdownEmail::factory()->create(['status' => 'draft']);

    // Act
    $email->markAsSent();

    // Assert
    expect($email->status)->toBe('sent')
        ->and($email->sent_at)->not->toBeNull();
});

it('can mark email as failed', function () {
    // Arrange
    $email = MarkdownEmail::factory()->create(['status' => 'draft']);
    $errorMessage = 'Failed to send email';

    // Act
    $email->markAsFailed($errorMessage);

    // Assert
    expect($email->status)->toBe('failed')
        ->and($email->error_message)->toBe($errorMessage);
});

it('can scope sent emails', function () {
    // Arrange
    MarkdownEmail::factory()->create(['status' => 'draft']);
    MarkdownEmail::factory()->create(['status' => 'sent']);
    MarkdownEmail::factory()->create(['status' => 'failed']);

    // Act
    $sentEmails = MarkdownEmail::sent()->get();

    // Assert
    expect($sentEmails)->toHaveCount(1)
        ->and($sentEmails->first()->status)->toBe('sent');
});

it('can scope failed emails', function () {
    // Arrange
    MarkdownEmail::factory()->create(['status' => 'draft']);
    MarkdownEmail::factory()->create(['status' => 'sent']);
    MarkdownEmail::factory()->create(['status' => 'failed']);

    // Act
    $failedEmails = MarkdownEmail::failed()->get();

    // Assert
    expect($failedEmails)->toHaveCount(1)
        ->and($failedEmails->first()->status)->toBe('failed');
});