<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Replace round_status and final_result with a single status column
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('current_round');
        });

        // Migrate existing data
        DB::table('candidates')->where('final_result', 'final_selected')->update(['status' => 'selected']);
        DB::table('candidates')->where('final_result', 'rejected')->update(['status' => 'rejected']);
        DB::table('candidates')->where('round_status', 'pending')->where('final_result', 'in_progress')->update(['status' => 'pending']);
        DB::table('candidates')->where('round_status', 'not_cleared')->where('final_result', '!=', 'rejected')->update(['status' => 'not_cleared']);

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn(['round_status', 'final_result']);
        });

        // Also update interview_rounds result to support maybe/on_hold
        Schema::table('interview_rounds', function (Blueprint $table) {
            $table->string('result', 20)->change();
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->enum('round_status', ['pending', 'cleared', 'not_cleared'])->default('pending');
            $table->enum('final_result', ['in_progress', 'rejected', 'final_selected'])->default('in_progress');
        });

        DB::table('candidates')->where('status', 'selected')->update(['round_status' => 'cleared', 'final_result' => 'final_selected']);
        DB::table('candidates')->where('status', 'rejected')->update(['round_status' => 'not_cleared', 'final_result' => 'rejected']);
        DB::table('candidates')->where('status', 'pending')->update(['round_status' => 'pending', 'final_result' => 'in_progress']);

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
