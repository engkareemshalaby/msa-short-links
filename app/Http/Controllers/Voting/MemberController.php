<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use App\Models\Voting\Member;
use App\Models\Voting\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $member = Member::where('user_id', $request->user()->id)->firstOrFail();
        $settings = Setting::firstOrCreate(['id' => 1]);
        $ballot = DB::table('voting_ballots')->where('voter_id', $member->id)->first();
        $choices = $ballot ? DB::table('voting_choices')->where('ballot_id', $ballot->id)->pluck('candidate_id')->all() : [];
        $candidates = Member::with('user')->whereKeyNot($member->id)->get()->shuffle();
        $results = $settings->show_results_to_members
            ? Member::with('user')->withCount('votesReceived')->orderByDesc('votes_received_count')->get()
            : null;

        return view('voting.index', compact('member', 'settings', 'ballot', 'choices', 'candidates', 'results'));
    }

    public function store(Request $request): RedirectResponse
    {
        $member = Member::where('user_id', $request->user()->id)->firstOrFail();
        $data = $request->validate([
            'choices' => ['required', 'array', 'size:8'],
            'choices.*' => ['required', 'integer', 'distinct', 'exists:voting_members,id'],
        ]);
        if (in_array($member->id, array_map('intval', $data['choices']), true)) {
            throw ValidationException::withMessages(['choices' => 'You cannot vote for yourself.']);
        }

        DB::transaction(function () use ($member, $data) {
            $settings = Setting::whereKey(1)->lockForUpdate()->first();
            if (! $settings?->is_open) {
                throw ValidationException::withMessages(['choices' => 'Voting is currently closed.']);
            }
            if (DB::table('voting_ballots')->where('voter_id', $member->id)->exists()) {
                throw ValidationException::withMessages(['choices' => 'Your vote has already been recorded.']);
            }
            $ballotId = DB::table('voting_ballots')->insertGetId(['voter_id' => $member->id, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('voting_choices')->insert(array_map(fn ($id) => ['ballot_id' => $ballotId, 'candidate_id' => $id], $data['choices']));
        });

        return redirect()->route('voting.index')->with('success', 'Your vote has been recorded successfully.');
    }
}
