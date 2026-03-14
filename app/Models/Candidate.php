<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    protected $fillable = [
        'candidate_id',
        'student_name',
        'aptitude_score',
        'test_score',
        'video_score',
        'current_round',
        'round_status',
        'final_result',
        'interviewer',
    ];

    public function interviewRounds(): HasMany
    {
        return $this->hasMany(InterviewRound::class)->orderBy('round_number');
    }

    public function getRoundLabelAttribute(): string
    {
        return 'Round ' . $this->current_round;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->round_status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'cleared' => 'bg-green-100 text-green-800',
            'not_cleared' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getFinalResultBadgeClassAttribute(): string
    {
        return match ($this->final_result) {
            'in_progress' => 'bg-blue-100 text-blue-800',
            'rejected' => 'bg-red-100 text-red-800',
            'final_selected' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getFormattedStatusAttribute(): string
    {
        return match ($this->round_status) {
            'pending' => 'Pending',
            'cleared' => 'Cleared',
            'not_cleared' => 'Not Cleared',
            default => ucfirst($this->round_status),
        };
    }

    public function getFormattedFinalResultAttribute(): string
    {
        return match ($this->final_result) {
            'in_progress' => 'In Progress',
            'rejected' => 'Rejected',
            'final_selected' => 'Final Selected',
            default => ucfirst($this->final_result),
        };
    }
}
