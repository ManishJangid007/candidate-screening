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
                    <span class="form-label">GitHub Profile</span>
                    <p class="mt-1">
                        @if($candidate->github_profile)
                            @php
                                $githubUrl = $candidate->github_profile;
                                if (!str_starts_with($githubUrl, 'http://') && !str_starts_with($githubUrl, 'https://')) {
                                    $githubUrl = 'https://' . $githubUrl;
                                }
                            @endphp
                            <a href="{{ $githubUrl }}" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline inline-flex items-center gap-1">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
                                {{ $candidate->github_profile }}
                            </a>
                        @else
                            -
                        @endif
                    </p>
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
                    <span class="form-label">Interviewer</span>
                    <p class="mt-1">{{ $candidate->interviewer ?? 'Not Assigned' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Change Interviewer (Admin only) --}}
    @if(Auth::user()->isAdmin())
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="card-title">Change Interviewer</h2>
        </div>
        <div class="card-content">
            <form action="{{ route('candidates.change-interviewer', $candidate) }}" method="POST" class="flex items-end gap-3">
                @csrf
                <div class="flex-1">
                    <label for="change-interviewer" class="form-label">Interviewer</label>
                    <select name="interviewer" id="change-interviewer" class="form-select mt-1" required>
                        <option value="">Select Interviewer</option>
                        @foreach($interviewers as $interviewer)
                            <option value="{{ $interviewer }}" {{ $candidate->interviewer == $interviewer ? 'selected' : '' }}>{{ $interviewer }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-primary">Update</button>
            </form>
        </div>
    </div>
    @endif

    {{-- Revert Action (Admin only) --}}
    @if(Auth::user()->isAdmin() && in_array($candidate->status, ['rejected', 'selected', 'on_hold']))
    <div class="card mb-6">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <p class="text-sm text-muted-foreground">
                    This candidate is marked as <span class="font-semibold">{{ $candidate->formatted_status }}</span>.
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
    @if(!in_array($candidate->status, ['rejected', 'selected']))
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="card-title">Evaluation - Round {{ $candidate->current_round }}</h2>
        </div>
        <div class="card-content">
            <form action="{{ route('candidates.evaluate', $candidate) }}" method="POST">
                @csrf

                <div class="mb-4">
                    <span class="form-label">Round Result</span>
                    <div class="flex flex-wrap items-center gap-4 mt-2">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="result" value="cleared" required>
                            <span>Cleared</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="result" value="not_cleared" required>
                            <span>Not Cleared</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="result" value="on_hold" required>
                            <span>On Hold</span>
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="remarks" class="form-label">Remarks</label>
                    <textarea name="remarks" id="remarks" rows="4" class="form-textarea" placeholder="Add your evaluation remarks here..."></textarea>
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
                <div class="table-wrapper">
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
                                    <td class="max-w-xs">
                                        @if($round->remarks && strlen($round->remarks) > 100)
                                            <div class="remarks-cell">
                                                <p class="remarks-short">{{ Str::limit($round->remarks, 100) }}
                                                    <button type="button" class="text-primary text-xs font-medium hover:underline toggle-remarks">Show more</button>
                                                </p>
                                                <p class="remarks-full hidden">{{ $round->remarks }}
                                                    <button type="button" class="text-primary text-xs font-medium hover:underline toggle-remarks">Show less</button>
                                                </p>
                                            </div>
                                        @else
                                            {{ $round->remarks ?? '-' }}
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap">{{ $round->created_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted-foreground">No interview history yet.</p>
            @endif
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-remarks').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var cell = this.closest('.remarks-cell');
            cell.querySelector('.remarks-short').classList.toggle('hidden');
            cell.querySelector('.remarks-full').classList.toggle('hidden');
        });
    });
});
</script>
@endsection
