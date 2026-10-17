<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_settings', function (Blueprint $table) {
            $table->string('package', 128)->primary();
            $table->json('settings');
            // Encrypted by the model's cast; the column only ever holds ciphertext.
            $table->longText('credentials')->nullable();
            $table->timestamps();
        });

        Schema::create('source_instances', function (Blueprint $table) {
            $table->id();
            $table->string('package', 128);
            $table->string('source_key', 64);
            $table->string('label');
            $table->json('settings');
            $table->longText('credentials')->nullable();
            $table->boolean('enabled')->default(true);
            // Null = the interval the source's definition declares.
            $table->unsignedInteger('interval_minutes')->nullable();
            $table->text('cursor')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->string('status', 16)->default('pending');
            $table->text('last_error')->nullable();
            $table->timestamp('retry_after')->nullable();
            $table->timestamps();

            $table->index(['package', 'source_key']);
        });
    }
};
