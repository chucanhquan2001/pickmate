<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('nickname')->nullable();
            $table->string('avatar')->nullable();
            $table->string('gender');
            $table->date('birthday')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('level')->default('beginner');
            $table->date('joined_at')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['club_id', 'email']);
            $table->index(['club_id', 'status']);
        });

        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->string('status')->default('active');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['club_id', 'code']);
        });

        Schema::create('minigames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('format');
            $table->string('status')->default('draft');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedSmallInteger('default_score');
            $table->unsignedTinyInteger('best_of');
            $table->unsignedSmallInteger('participation_points');
            $table->unsignedSmallInteger('win_points');
            $table->unsignedSmallInteger('loss_points');
            $table->unsignedSmallInteger('clean_win_bonus');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['club_id', 'status']);
        });

        Schema::create('minigame_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('minigame_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->unique(['minigame_id', 'member_id']);
        });

        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('minigame_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('session_id')->nullable()->index();
            $table->foreignId('court_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('scheduled');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['minigame_id', 'status']);
            $table->index('scheduled_at');
        });

        Schema::create('match_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('team');
            $table->unsignedTinyInteger('position');
            $table->timestamp('created_at')->nullable();

            $table->unique(['match_id', 'member_id']);
        });

        Schema::create('match_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->unsignedTinyInteger('set_number');
            $table->unsignedTinyInteger('team_1_score');
            $table->unsignedTinyInteger('team_2_score');
            $table->timestamps();

            $table->unique(['match_id', 'set_number']);
        });

        Schema::create('rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('minigame_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('matches')->default(0);
            $table->unsignedInteger('wins')->default(0);
            $table->unsignedInteger('losses')->default(0);
            $table->integer('points')->default(0);
            $table->decimal('rating', 8, 2)->nullable();
            $table->unsignedInteger('rank')->default(0);
            $table->timestamp('updated_at')->nullable();

            $table->unique(['minigame_id', 'member_id']);
        });

        Schema::create('ranking_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('minigame_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('match_id')->nullable()->constrained('matches')->nullOnDelete();
            $table->integer('old_point');
            $table->integer('change_point');
            $table->integer('new_point');
            $table->unsignedInteger('old_rank')->nullable();
            $table->unsignedInteger('new_rank')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranking_logs');
        Schema::dropIfExists('rankings');
        Schema::dropIfExists('match_sets');
        Schema::dropIfExists('match_players');
        Schema::dropIfExists('matches');
        Schema::dropIfExists('minigame_members');
        Schema::dropIfExists('minigames');
        Schema::dropIfExists('courts');
        Schema::dropIfExists('members');
    }
};
