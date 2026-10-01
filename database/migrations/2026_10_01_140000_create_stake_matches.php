<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stake_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('court_id')->constrained()->restrictOnDelete();
            $table->timestamp('scheduled_at');
            $table->string('format');
            $table->string('item');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('expected_amount');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['club_id', 'scheduled_at']);
        });

        Schema::create('stake_match_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stake_match_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('team');
            $table->unsignedTinyInteger('position');
            $table->timestamp('created_at')->nullable();

            $table->unique(['stake_match_id', 'member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stake_match_players');
        Schema::dropIfExists('stake_matches');
    }
};
