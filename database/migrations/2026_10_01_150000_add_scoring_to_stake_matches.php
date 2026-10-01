<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stake_matches', function (Blueprint $table) {
            $table->string('scoring_type')->default('side_out')->after('format');
            $table->unsignedTinyInteger('team_1_score')->nullable()->after('expected_amount');
            $table->unsignedTinyInteger('team_2_score')->nullable()->after('team_1_score');
        });
    }

    public function down(): void
    {
        Schema::table('stake_matches', function (Blueprint $table) {
            $table->dropColumn(['scoring_type', 'team_1_score', 'team_2_score']);
        });
    }
};
