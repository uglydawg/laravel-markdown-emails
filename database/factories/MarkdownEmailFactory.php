<?php

declare(strict_types=1);

namespace uglydawg\LaravelMarkdownEmails\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use uglydawg\LaravelMarkdownEmails\MarkdownEmail;

class MarkdownEmailFactory extends Factory
{
    protected $model = MarkdownEmail::class;

    public function definition(): array
    {
        return [
            'subject' => $this->faker->sentence(),
            'markdown_content' => $this->faker->paragraph(),
            'html_content' => '<p>' . $this->faker->paragraph() . '</p>',
            'recipients' => [$this->faker->email()],
            'variables' => ['name' => $this->faker->name()],
            'template_used' => 'base-template',
            'status' => 'draft',
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => $this->faker->sentence(),
        ]);
    }
}