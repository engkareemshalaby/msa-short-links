<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use App\Models\Voting\Member;
use App\Models\Voting\Setting;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $settings = Setting::firstOrCreate(['id' => 1]);
        $members = Member::with('user')->withCount('votesReceived')->get()->sortByDesc('votes_received_count')->values();
        $ballots = DB::table('voting_ballots')->get()->keyBy('voter_id');
        $choices = DB::table('voting_choices')->get()->groupBy('ballot_id');
        $supportersByCandidate = DB::table('voting_choices')
            ->join('voting_ballots', 'voting_choices.ballot_id', '=', 'voting_ballots.id')
            ->get(['voting_choices.candidate_id', 'voting_ballots.voter_id'])
            ->groupBy('candidate_id')
            ->map(fn ($rows) => $rows->pluck('voter_id'));
        return view('voting.admin', compact('settings', 'members', 'ballots', 'choices', 'supportersByCandidate'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'is_open' => ['required', 'boolean'],
            'show_results_to_members' => ['required', 'boolean'],
        ]);
        $settings = Setting::firstOrCreate(['id' => 1]);
        $old = $settings->only(['is_open', 'show_results_to_members']);
        $settings->update($data);
        AuditLogger::log('updated', $settings, 'Voting settings updated', $old, $data);
        return back()->with('success', __('voting.updated'));
    }
}
