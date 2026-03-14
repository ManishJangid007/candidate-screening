<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('candidate_id', 50)->unique();
            $table->string('student_name');
            $table->integer('aptitude_score')->nullable();
            $table->integer('test_score')->nullable();
            $table->integer('video_score')->nullable();
            $table->tinyInteger('current_round')->default(1);
            $table->enum('round_status', ['pending', 'cleared', 'not_cleared'])->default('pending');
            $table->enum('final_result', ['in_progress', 'rejected', 'final_selected'])->default('in_progress');
            $table->string('interviewer')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
