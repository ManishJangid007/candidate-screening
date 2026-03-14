<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\InterviewRound;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidateController extends Controller
{
    public function index(Request $request)
    {
        $query = Candidate::query();

        $user = Auth::user();
        if ($user->role === 'interviewer') {
            $query->where('interviewer', $user->name);
        }

        if ($request->filled('candidate_id')) {
            $query->where('candidate_id', 'LIKE', '%' . $request->candidate_id . '%');
        }

        if ($request->filled('student_name')) {
            $query->where('student_name', 'LIKE', '%' . $request->student_name . '%');
        }

        if ($request->filled('current_round')) {
            $query->where('current_round', $request->current_round);
        }

        if ($request->filled('round_status')) {
            $query->where('round_status', $request->round_status);
        }

        if ($request->filled('interviewer')) {
            if ($request->interviewer === '__unassigned__') {
                $query->whereNull('interviewer');
            } else {
                $query->where('interviewer', $request->interviewer);
            }
        }

        if ($request->filled('final_result')) {
            $query->where('final_result', $request->final_result);
        }

        $candidates = $query->paginate(15)->withQueryString();
        $interviewers = config('interviewers');

        // Stats query scoped by role
        $statsQuery = Candidate::query();
        if ($user->role === 'interviewer') {
            $statsQuery->where('interviewer', $user->name);
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('round_status', 'pending')->count(),
            'cleared' => (clone $statsQuery)->where('round_status', 'cleared')->count(),
            'not_cleared' => (clone $statsQuery)->where('round_status', 'not_cleared')->count(),
            'in_progress' => (clone $statsQuery)->where('final_result', 'in_progress')->count(),
            'rejected' => (clone $statsQuery)->where('final_result', 'rejected')->count(),
            'final_selected' => (clone $statsQuery)->where('final_result', 'final_selected')->count(),
        ];

        if ($request->ajax()) {
            $statusBadgeMap = [
                'pending' => 'bg-yellow-100 text-yellow-800',
                'cleared' => 'bg-green-100 text-green-800',
                'not_cleared' => 'bg-red-100 text-red-800',
            ];
            $statusLabelMap = [
                'pending' => 'Pending',
                'cleared' => 'Cleared',
                'not_cleared' => 'Not Cleared',
            ];
            $finalBadgeMap = [
                'in_progress' => 'bg-blue-100 text-blue-800',
                'rejected' => 'bg-red-100 text-red-800',
                'final_selected' => 'bg-green-100 text-green-800',
            ];
            $finalLabelMap = [
                'in_progress' => 'In Progress',
                'rejected' => 'Rejected',
                'final_selected' => 'Final Selected',
            ];

            $rows = $candidates->map(function ($c) use ($statusBadgeMap, $statusLabelMap, $finalBadgeMap, $finalLabelMap) {
                return [
                    'id' => $c->id,
                    'candidate_id' => $c->candidate_id,
                    'student_name' => $c->student_name,
                    'aptitude_score' => $c->aptitude_score,
                    'test_score' => $c->test_score,
                    'video_score' => $c->video_score,
                    'current_round' => $c->current_round,
                    'round_status' => $c->round_status,
                    'round_status_label' => $statusLabelMap[$c->round_status] ?? ucfirst($c->round_status),
                    'round_status_badge' => $statusBadgeMap[$c->round_status] ?? 'bg-gray-100 text-gray-800',
                    'final_result' => $c->final_result,
                    'final_result_label' => $finalLabelMap[$c->final_result] ?? ucfirst($c->final_result),
                    'final_result_badge' => $finalBadgeMap[$c->final_result] ?? 'bg-gray-100 text-gray-800',
                    'interviewer' => $c->interviewer,
                    'show_url' => route('candidates.show', $c),
                    'assign_url' => route('candidates.assign', $c),
                ];
            });

            return response()->json([
                'data' => $rows,
                'stats' => $stats,
                'pagination' => [
                    'current_page' => $candidates->currentPage(),
                    'last_page' => $candidates->lastPage(),
                    'per_page' => $candidates->perPage(),
                    'total' => $candidates->total(),
                    'from' => $candidates->firstItem(),
                    'to' => $candidates->lastItem(),
                ],
            ]);
        }

        return view('candidates.index', compact('candidates', 'interviewers', 'stats'));
    }

    public function show(Candidate $candidate)
    {
        $user = Auth::user();
        if ($user->role === 'interviewer') {
            $assignedToUser = $candidate->interviewer === $user->name;
            $hasEvaluated = $candidate->interviewRounds()->where('interviewer', $user->name)->exists();
            if (!$assignedToUser && !$hasEvaluated) {
                abort(403);
            }
        }

        $candidate->load('interviewRounds');
        $interviewers = config('interviewers');

        return view('candidates.show', compact('candidate', 'interviewers'));
    }

    public function evaluate(Request $request, Candidate $candidate)
    {
        $user = Auth::user();
        if ($user->role === 'interviewer') {
            $assignedToUser = $candidate->interviewer === $user->name;
            $hasEvaluated = $candidate->interviewRounds()->where('interviewer', $user->name)->exists();
            if (!$assignedToUser && !$hasEvaluated) {
                abort(403);
            }
        }

        $interviewers = config('interviewers');

        $validated = $request->validate([
            'result' => ['required', 'in:cleared,not_cleared'],
            'remarks' => ['nullable', 'string'],
        ]);

        $interviewerName = $candidate->interviewer ?? Auth::user()->name;

        $interviewRound = new InterviewRound();
        $interviewRound->candidate_id = $candidate->id;
        $interviewRound->round_number = $candidate->current_round;
        $interviewRound->interviewer = $interviewerName;
        $interviewRound->result = $validated['result'];
        $interviewRound->remarks = $validated['remarks'] ?? null;
        $interviewRound->evaluated_by = Auth::id();
        $interviewRound->save();

        if ($validated['result'] === 'cleared') {
            if ($candidate->current_round < 4) {
                $candidate->current_round = $candidate->current_round + 1;
                $candidate->round_status = 'pending';
                $candidate->interviewer = null;
            } else {
                $candidate->round_status = 'cleared';
                $candidate->final_result = 'final_selected';
            }
        } else {
            $candidate->round_status = 'not_cleared';
            $candidate->final_result = 'rejected';
        }

        $candidate->save();

        return redirect()->route('candidates.show', $candidate)
            ->with('success', 'Evaluation submitted successfully.');
    }

    public function revert(Candidate $candidate)
    {
        $candidate->round_status = 'pending';
        $candidate->final_result = 'in_progress';
        $candidate->save();

        return redirect()->back()->with('success', 'Candidate reverted to pending.');
    }

    public function assignInterviewer(Request $request, Candidate $candidate)
    {
        $interviewers = config('interviewers');

        $request->validate([
            'interviewer' => ['required', 'in:' . implode(',', $interviewers)],
        ]);

        $candidate->interviewer = $request->interviewer;
        $candidate->save();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Interviewer assigned successfully.']);
        }

        return redirect()->back()->with('success', 'Interviewer assigned successfully.');
    }
}
