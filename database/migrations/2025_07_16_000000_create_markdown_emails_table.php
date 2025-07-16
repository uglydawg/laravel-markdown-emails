<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('markdown_emails', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('subject');
            $table->text('markdown_content');
            $table->text('html_content');
            $table->json('recipients');
            $table->json('variables')->nullable();
            $table->string('template_used')->default('base-template');
            $table->enum('status', ['draft', 'sent', 'failed'])->default('draft');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('sent_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('markdown_emails');
    }
};