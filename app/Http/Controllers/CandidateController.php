<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\InterviewRound;
use Illuminate\Http\Request;
use App\Models\User;
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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('interviewer')) {
            if ($request->interviewer === '__unassigned__') {
                $query->whereNull('interviewer');
            } else {
                $query->where('interviewer', $request->interviewer);
            }
        }

        $candidates = $query->paginate(15)->withQueryString();
        $interviewers = User::where('role', 'interviewer')->orderBy('name')->pluck('name')->toArray();

        // Status stats scoped by role
        $statsQuery = Candidate::query();
        if ($user->role === 'interviewer') {
            $statsQuery->where('interviewer', $user->name);
        }

        // Round stats always show all candidates
        $globalQuery = Candidate::query();

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'on_hold' => (clone $statsQuery)->where('status', 'on_hold')->count(),
            'selected' => (clone $statsQuery)->where('status', 'selected')->count(),
            'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
            'round_1' => (clone $globalQuery)->where('current_round', 1)->count(),
            'round_2' => (clone $globalQuery)->where('current_round', 2)->count(),
            'round_3' => (clone $globalQuery)->where('current_round', 3)->count(),
        ];

        if ($request->ajax()) {
            $badgeMap = [
                'pending' => 'bg-yellow-100 text-yellow-800',
                'on_hold' => 'bg-purple-100 text-purple-800',
                'selected' => 'bg-green-100 text-green-800',
                'rejected' => 'bg-red-100 text-red-800',
            ];
            $labelMap = [
                'pending' => 'Pending',
                'on_hold' => 'On Hold',
                'selected' => 'Selected',
                'rejected' => 'Rejected',
            ];

            $rows = $candidates->map(function ($c) use ($badgeMap, $labelMap) {
                return [
                    'id' => $c->id,
                    'candidate_id' => $c->candidate_id,
                    'student_name' => $c->student_name,
                    'aptitude_score' => $c->aptitude_score,
                    'test_score' => $c->test_score,
                    'video_score' => $c->video_score,
                    'current_round' => $c->current_round,
                    'status' => $c->status,
                    'status_label' => $labelMap[$c->status] ?? ucfirst($c->status),
                    'status_badge' => $badgeMap[$c->status] ?? 'bg-gray-100 text-gray-800',
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
        $interviewers = User::where('role', 'interviewer')->orderBy('name')->pluck('name')->toArray();

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

        $validated = $request->validate([
            'result' => ['required', 'in:cleared,not_cleared,on_hold'],
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
            if ($candidate->current_round < 3) {
                $candidate->current_round = $candidate->current_round + 1;
                $candidate->status = 'pending';
                $candidate->interviewer = null;
            } else {
                $candidate->status = 'selected';
            }
        } elseif ($validated['result'] === 'not_cleared') {
            $candidate->status = 'rejected';
        } elseif ($validated['result'] === 'on_hold') {
            $candidate->status = 'on_hold';
        }

        $candidate->save();

        return redirect()->route('candidates.index')
            ->with('success', 'Evaluation submitted successfully.');
    }

    public function revert(Candidate $candidate)
    {
        $candidate->status = 'pending';
        $candidate->save();

        return redirect()->back()->with('success', 'Candidate reverted to pending.');
    }

    public function changeInterviewer(Request $request, Candidate $candidate)
    {
        $interviewers = User::where('role', 'interviewer')->orderBy('name')->pluck('name')->toArray();

        $request->validate([
            'interviewer' => ['required', 'in:' . implode(',', $interviewers)],
        ]);

        $candidate->interviewer = $request->interviewer;
        $candidate->save();

        return redirect()->back()->with('success', 'Interviewer changed successfully.');
    }

    public function assignInterviewer(Request $request, Candidate $candidate)
    {
        $interviewers = User::where('role', 'interviewer')->orderBy('name')->pluck('name')->toArray();

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
