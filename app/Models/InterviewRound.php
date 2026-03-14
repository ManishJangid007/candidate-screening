<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewRound extends Model
{
    protected $fillable = [
        'candidate_id',
        'round_number',
        'interviewer',
        'result',
        'remarks',
        'evaluated_by',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function getFormattedResultAttribute(): string
    {
        return match ($this->result) {
            'cleared' => 'Cleared',
            'not_cleared' => 'Not Cleared',
            default => ucfirst($this->result),
        };
    }

    public function getResultBadgeClassAttribute(): string
    {
        return match ($this->result) {
            'cleared' => 'bg-green-100 text-green-800',
            'not_cleared' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
