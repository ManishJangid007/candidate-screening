@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Candidate Detail</h1>
        <a href="{{ route('candidates.index') }}" class="btn-outline btn-sm">Back to List</a>
    </div>

    {{-- Candidate Info Card --}}
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="card-title">Candidate Information</h2>
        </div>
        <div class="card-content">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <span class="form-label">Candidate ID</span>
                    <p class="mt-1">{{ $candidate->candidate_id }}</p>
                </div>
                <div>
                    <span class="form-label">Student Name</span>
                    <p class="mt-1">{{ $candidate->student_name }}</p>
                </div>
                <div>
                    <span class="form-label">Aptitude Score</span>
                    <p class="mt-1">{{ $candidate->aptitude_score ?? '-' }}</p>
                </div>
                <div>
                    <span class="form-label">Test Score</span>
                    <p class="mt-1">{{ $candidate->test_score ?? '-' }}</p>
                </div>
                <div>
                    <span class="form-label">Video Score</span>
                    <p class="mt-1">{{ $candidate->video_score ?? '-' }}</p>
                </div>
                <div>
                    <span class="form-label">Current Round</span>
                    <p class="mt-1">Round {{ $candidate->current_round }}</p>
                </div>
                <div>
                    <span class="form-label">Status</span>
                    <p class="mt-1">
                        <span class="badge {{ $candidate->status_badge_class }}">{{ $candidate->formatted_status }}</span>
                    </p>
                </div>
                <div>
                    <span class="form-label">Final Result</span>
                    <p class="mt-1">
                        <span class="badge {{ $candidate->final_result_badge_class }}">{{ $candidate->formatted_final_result }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Revert Action (Admin only) --}}
    @if(Auth::user()->isAdmin() && ($candidate->final_result === 'rejected' || $candidate->final_result === 'final_selected'))
    <div class="card mb-6">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <p class="text-sm text-muted-foreground">
                    This candidate is marked as <span class="font-semibold">{{ $candidate->formatted_final_result }}</span>.
                    You can revert them back to pending status for the current round.
                </p>
                <form action="{{ route('candidates.revert', $candidate) }}" method="POST" class="ml-4 shrink-0">
                    @csrf
                    <button type="submit" class="btn-outline" onclick="return confirm('Are you sure you want to revert this candidate to pending?')">
                        Revert to Pending
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Evaluation Form Card --}}
    @if($candidate->final_result !== 'rejected' && $candidate->final_result !== 'final_selected')
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="card-title">Evaluation - Round {{ $candidate->current_round }}</h2>
        </div>
        <div class="card-content">
            <form action="{{ route('candidates.evaluate', $candidate) }}" method="POST">
                @csrf

                <div class="mb-4">
                    <span class="form-label">Round Result</span>
                    <div class="flex items-center gap-4 mt-2">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="result" value="cleared" required>
                            <span>Cleared</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="result" value="not_cleared" required>
                            <span>Not Cleared</span>
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="remarks" class="form-label">Remarks</label>
                    <textarea name="remarks" id="remarks" rows="3" class="form-textarea"></textarea>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="btn-primary">Save</button>
                    <a href="{{ route('candidates.index') }}" class="btn-outline">Back to List</a>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Interview History Card --}}
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="card-title">Interview History</h2>
        </div>
        <div class="card-content">
            @if($candidate->interviewRounds->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Round</th>
                            <th>Interviewer</th>
                            <th>Result</th>
                            <th>Remarks</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($candidate->interviewRounds as $round)
                            <tr>
                                <td>Round {{ $round->round_number }}</td>
                                <td>{{ $round->interviewer }}</td>
                                <td>
                                    <span class="badge {{ $round->result_badge_class }}">{{ $round->formatted_result }}</span>
                                </td>
                                <td>{{ $round->remarks }}</td>
                                <td>{{ $round->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No interview history yet.</p>
            @endif
        </div>
    </div>

</div>
@endsection
