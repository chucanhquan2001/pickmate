<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->string('invite_token', 64)->nullable()->unique()->after('slug');
        });

        foreach (DB::table('clubs')->whereNull('invite_token')->orderBy('id')->get() as $club) {
            DB::table('clubs')->where('id', $club->id)->update([
                'invite_token' => Str::random(48),
            ]);
        }

        Schema::create('club_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role');
            $table->timestamps();

            $table->unique(['club_id', 'user_id']);
        });

        Schema::create('club_join_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('gender');
            $table->string('nickname')->nullable();
            $table->string('level')->default('beginner');
            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['club_id', 'status']);
            $table->index(['club_id', 'user_id']);
        });

        $now = now();

        foreach (DB::table('users')->whereNotNull('club_id')->orderBy('id')->get() as $user) {
            DB::table('club_memberships')->insert([
                'club_id' => $user->club_id,
                'user_id' => $user->id,
                'role' => $user->role,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('club_join_requests');
        Schema::dropIfExists('club_memberships');

        Schema::table('clubs', function (Blueprint $table) {
            $table->dropUnique(['invite_token']);
            $table->dropColumn('invite_token');
        });
    }
};
