<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('timezone')->default('Asia/Ho_Chi_Minh');
            $table->string('language', 10)->default('vi');
            $table->string('status')->default('active');
            $table->unsignedSmallInteger('default_score')->default(11);
            $table->unsignedTinyInteger('default_best_of')->default(1);
            $table->unsignedSmallInteger('default_participation_points')->default(1);
            $table->unsignedSmallInteger('default_win_points')->default(3);
            $table->unsignedSmallInteger('default_loss_points')->default(0);
            $table->unsignedSmallInteger('default_clean_win_bonus')->default(1);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('avatar')->nullable();
            $table->string('role');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('provider_user_id');
            $table->string('email')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_user_id']);
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

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('social_accounts');
        Schema::dropIfExists('users');
        Schema::dropIfExists('clubs');
    }
};
