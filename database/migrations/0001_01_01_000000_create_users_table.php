<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique()->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->unsignedBigInteger('current_company_id')->nullable();
            $table->boolean('company_config_pending')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            
            // Note: DB level unique constraints on (email) will conflict with soft deletes 
            // if you strictly want to allow re-registering with same email after soft delete.
            // Standard Laravel Approach: Keep unique index, enforce distinct emails for ALL records (even deleted).
            // If user wants to reuse email, they must hard delete or restore.
            // Given "van haber campos unicos y tenndre problemas con eso", user is aware.
            // We'll keep standard unique() for now as it's safest for data integrity.
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
