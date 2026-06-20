<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint ) {
            ->id();
            ->string('name');
            ->string('email')->unique();
            ->enum('role', ['admin', 'user'])->default('user');
            ->timestamp('last_login_at')->nullable();
            ->timestamp('email_verified_at')->nullable();
            ->string('password');
            ->rememberToken();
            ->timestamps();
            ->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint ) {
            ->string('email')->primary();
            ->string('token');
            ->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint ) {
            ->string('id')->primary();
            ->foreignId('user_id')->nullable()->index();
            ->string('ip_address', 45)->nullable();
            ->text('user_agent')->nullable();
            ->longText('payload');
            ->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
