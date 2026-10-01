<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->decimal('dupr_rating', 3, 1)->default(2.0);
            $table->decimal('spcn_rating', 3, 1)->default(2.0);
        });

        Schema::table('club_join_requests', function (Blueprint $table) {
            $table->decimal('dupr_rating', 3, 1)->default(2.0);
            $table->decimal('spcn_rating', 3, 1)->default(2.0);
        });

        $ratings = [
            'beginner' => 2.0,
            'intermediate' => 3.5,
            'advanced' => 5.0,
        ];

        foreach ($ratings as $level => $rating) {
            DB::table('members')->where('level', $level)->update([
                'dupr_rating' => $rating,
                'spcn_rating' => $rating,
            ]);
            DB::table('club_join_requests')->where('level', $level)->update([
                'dupr_rating' => $rating,
                'spcn_rating' => $rating,
            ]);
        }

        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('level');
        });

        Schema::table('club_join_requests', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('level')->default('beginner');
        });

        Schema::table('club_join_requests', function (Blueprint $table) {
            $table->string('level')->default('beginner');
        });

        foreach (['members', 'club_join_requests'] as $table) {
            DB::table($table)->where('dupr_rating', '<', 3)->update(['level' => 'beginner']);
            DB::table($table)->where('dupr_rating', '>=', 3)->where('dupr_rating', '<', 4.5)->update(['level' => 'intermediate']);
            DB::table($table)->where('dupr_rating', '>=', 4.5)->update(['level' => 'advanced']);
        }

        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['dupr_rating', 'spcn_rating']);
        });

        Schema::table('club_join_requests', function (Blueprint $table) {
            $table->dropColumn(['dupr_rating', 'spcn_rating']);
        });
    }
};
