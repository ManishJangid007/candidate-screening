<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    protected $fillable = [
        'candidate_id',
        'student_name',
        'github_profile',
        'aptitude_score',
        'test_score',
        'video_score',
        'current_round',
        'status',
        'interviewer',
    ];

    public function interviewRounds(): HasMany
    {
        return $this->hasMany(InterviewRound::class)->orderBy('round_number');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'on_hold' => 'bg-purple-100 text-purple-800',
            'selected' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getFormattedStatusAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'on_hold' => 'On Hold',
            'selected' => 'Selected',
            'rejected' => 'Rejected',
            default => ucfirst($this->status),
        };
    }
}
