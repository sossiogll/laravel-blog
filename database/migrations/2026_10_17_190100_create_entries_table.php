<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->string('type', 63);
            $table->string('slug', 190);
            $table->string('title', 500);
            $table->text('summary')->nullable();
            $table->longText('body')->nullable();
            $table->string('body_format', 16)->default('markdown');
            $table->string('language', 12)->default('en');
            $table->string('translation_key')->nullable()->index();
            // What the source owns (overwritten by every sync) and what the person owns (never touched by one).
            $table->json('fields');
            $table->json('curated');
            // Null for an entry written by hand. Nulled, not cascaded: deleting a source keeps the content.
            $table->foreignId('source_instance_id')->nullable()->constrained('source_instances')->nullOnDelete();
            $table->string('external_id')->nullable();
            $table->string('fingerprint', 64)->nullable();
            $table->text('canonical_url')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('source_updated_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // The arbiters of concurrent writers: a sync upserts on the first, a slug is claimed on the second.
            $table->unique(['source_instance_id', 'external_id']);
            $table->unique(['type', 'language', 'slug']);
        });

        Schema::create('entry_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_id')->constrained('entries')->cascadeOnDelete();
            $table->string('field', 64);
            $table->unsignedInteger('position');
            // Null until the entry it names exists; resolved after every sync run.
            $table->foreignId('target_entry_id')->nullable()->constrained('entries')->nullOnDelete();
            $table->string('external_id')->nullable();
            $table->string('type', 63)->nullable();
            $table->string('slug', 190)->nullable();

            $table->unique(['entry_id', 'field', 'position']);
        });
    }
};
